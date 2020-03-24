<template>
    <div class="music">
         <navbar/>
        <div class="page-content">
            <router-view @playSongToggle="playSongToggle"></router-view>
        </div>
        <footer class="footer" v-bind:class="{'playerActive': showPlayer}">
            <audio id="player" ref="player" :key="audioSrc" :src="audioSrc" preload="auto" controls></audio>
            <div ref="slideContainer"  class="slideContainer">
                <div ref="progressBar" id="progressBar">
                    <div ref="playSlider" class="playSlider"></div>
                </div>
            </div>
            <div>
                <button ref="pausePlayer" @click="playSongToggle(activeSong)">Play/Pause</button>
                <span v-text="currentTimeString"></span> | <span v-text="durationString"></span> | 
                <span v-text="activeSong.title"></span> | <span v-text="songAlbumTitle"></span>
            </div>
        </footer>        
    </div>
</template>

<script>
    import "./music.css";
    import Navbar from './Navbar.vue';
    import { mapGetters } from 'vuex';
    import Hammer from 'hammerjs';
    let ticker = null;
    export default {
        name: "Music",
        data: function () {
            return {
                audioSrc: 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA',
                activeSong: { id: null },
                durationString: '00:00',
                currentTimeString: '00:00',

            }
        },
        components: {
            Navbar
        },
        methods: {
            playSongToggle: function(song) {
                const player = this.$refs.player;
                if (this.$data.activeSong.id !== song.id) {
                    const progressBar = this.$refs.progressBar;
                    cancelAnimationFrame(ticker);
                    this.$data.activeSong = song;
                    player.setAttribute('title', `The Discoverables - ${song.title}`);
                    requestAnimationFrame(function() {
                        progressBar.style.width = '0px';
                    });
                    const src = `/audio/${encodeURI(song.filename)}`;
                    if (isChromeDesktop() && !player.paused) {
                        player.pause();
                        // Chrome on Mac has a bit of a stutter when changing tracks.
                        // a slight timeout makes it better
                        setTimeout(function() {
                            player.src = src;
                            player.load();
                            player.play();
                        }, 1000);
                    } else {
                        player.pause();
                        player.src = src;
                        player.load();
                        player.play();
                    }
                } else if (!player.paused) {
                    player.pause();
                } else {
                    player.play();
                }
                
                function isChromeDesktop() {
                    const ua = navigator.userAgent;
                    const isChrome = /Chrome/i.test(ua);
                    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua);
                    return ((!isMobile && isChrome));
                }                
            },
            durationToString: function(duration) {
                if (isNaN(duration)) {
                    return '00:00';
                }
                const minutes = Math.floor(duration / 60);
                const seconds = (duration - minutes * 60).toString().substr(0, 2);
                return `${minutes}:${seconds}`;
            },
            currentTimeToString: function(currentTime) {
                if (isNaN(currentTime)) {
                    return '00:00';
                }
                const minute = parseInt(currentTime / 60) % 60;
                const seconds = (currentTime % 60).toFixed();
                return (minute < 10 ? "0" + minute : minute) + ":" + (seconds < 10 ? "0" + seconds : seconds);
            },
        },

        computed: {
            queue: function() {
                return this.songSet.map(song => {
                    const clone = Vue.util.extend({}, song);
                    clone.album = this.getSongAlbum(song);                    
                    return clone;
                }).filter(song => song.album);
            },
            showPlayer() {
                return this.$data.activeSong.id !== null;
            },
            songAlbumTitle() {
                if (this.$data.activeSong.album) {
                    return this.$data.activeSong.album.title;
                }
            },
            ...mapGetters([
                'songSet',
                'getSongAlbum'
            ])
        },
        mounted() {
            const player = this.$refs.player;
            const slider = this.$refs.playSlider;
            const slideContainer = this.$refs.slideContainer;
            const progressBar = this.$refs.progressBar;

            let sliderIsBeingMoved = false;
            
            player.addEventListener('playing', function(ev) {
                ticker = requestAnimationFrame(tick);
            });

            function tick() {
                // if the duration is set, 
                // and the player isn't paused, 
                // and the slider isn't being moved,
                if (!isNaN(player.duration) && !player.paused && !sliderIsBeingMoved) {
                    // set the slider's position dynamically
                    const progress = (player.currentTime / player.duration) * 100;
                    progressBar.style.width = `${progress}%`;

                }
                ticker = requestAnimationFrame(tick);
            }

            player.addEventListener('timeupdate', (ev) => {
                this.$data.currentTimeString = this.currentTimeToString(player.currentTime);
            });

            player.addEventListener('durationchange', (ev) => {
                this.$data.durationString = this.durationToString(player.duration);
            });
            
            player.addEventListener('pause', handlePause);

            // I have to find a way to test these
            player.addEventListener('waiting', handlePause);
            player.addEventListener('stalled', handlePause);

            function handlePause(e) {
                progressBar.style.width = `${(player.currentTime / player.duration) * 100}%`;
                // if the player is still paused on the next animation frame, cancel the ticker
                requestAnimationFrame(function() {
                    if (player.paused) {
                        cancelAnimationFrame(ticker);
                    }
                });
            }

            player.addEventListener('ended', () => {
                cancelAnimationFrame(ticker);                
                const index = this.queue.findIndex(song => song.id === this.$data.activeSong.id);
                const nextIndex = (this.queue.length > (index + 1)) ? index + 1 : 0;
                this.playSongToggle(this.queue[nextIndex]);            
            });

            (new Hammer(slideContainer)).on("pan press tap pressup", function(ev) {
                cancelAnimationFrame(ticker);
                sliderIsBeingMoved = true;
                slider.classList.add('activePlaySlider');
                const xPos = ev.center.x - slideContainer.offsetLeft;
                progressBar.style.width = `${xPos}px`;
                if (ev.isFinal) {
                    const timeRemaining = (xPos / slideContainer.offsetWidth) * player.duration;
                    player.currentTime = timeRemaining;
                    ticker = requestAnimationFrame(tick);
                    slider.classList.remove('activePlaySlider');
                    sliderIsBeingMoved = false;
                }                
            });

        } 
    }
</script>
<style scoped>

    #player {
        display: none;
    } 
    
    .footer {
        display: none;
        position: fixed;       
        left: 0;
        bottom: 0;
        width: 100%;
        padding: 0 44px 0 44px;
        height: 60px;
        background-color: #909090;
    }

    .slideContainer {
        position: relative;
        height: 10px;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 8px;
        border-radius: 15px;
        background: #d3d3d3;
        cursor: pointer;
        overflow: visible;
    }

    .playSlider {
        position: relative;
        margin-right: -9px;
        margin-top: -4px;
        width: 18px;
        height: 18px;
        float: right;
        border-radius: 50%; 
        background: #4CAF50;
        cursor: pointer;
        z-index: 100;
        transition: all 0.1s;
    }

    .playSlider.activePlaySlider {
        box-shadow: 0 3px 0 rgb(75, 84, 79);
        width: 36px;
        height: 36px;
        margin-left: -18px;
        margin-top: -12px;
        transition: all 0.1s;
      
    }

    #progressBar {
        position: absolute;
        width: 0px;
        height: 10px;
        border-radius: 15px; 
        background: blueviolet;
        cursor: pointer;
        top: 0px;
        left: 0px;

    }

    .playerActive {
        display: block;
    }

</style>