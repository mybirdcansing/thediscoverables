<template>
    <div class="music">
         <navbar/>
        <div class="page-content">
            <router-view @playSongToggle="playSongToggle"></router-view>
        </div>
        <footer class="footer" v-bind:class="{'player-active': showPlayer}">
            <audio id="audio-player" controls ref="player" :key="audioSrc" preload="auto" v-bind:src="audioSrc"></audio>
            <div ref="slideContainer"  class="slideContainer">
                <div ref="progressBar" id="progressBar">
                    <div ref="playSlider" class="playSlider"></div>
                </div>
            </div>
            <div>
                <button ref="pausePlayer" @click="playSongToggle(activeSong)">Play/Pause</button>
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
                activeSong: { id: null }
            }
        },
        components: {
            Navbar
        },
        methods: {
            playSongToggle: function(song) {
                const player = this.$refs.player;
                const slider = this.$refs.playSlider;
                const slideContainer = this.$refs.slideContainer;
                const progressBar = this.$refs.progressBar;
                const src = `/audio/${encodeURI(song.filename)}`;
                player.setAttribute('title', `The Discoverables - ${song.title}`);
                if (this.$data.activeSong.id !== song.id) {
                    this.$data.activeSong = song;
                    cancelAnimationFrame(ticker);
                    progressBar.style.width = '0px';
                    
                    function isChromeDesktop() {
                        const ua = navigator.userAgent;
                        const isChrome = /Chrome/i.test(ua);
                        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua);
                        return ((!isMobile && isChrome));
                    }
                    // Chrome on Mac has a bit of a stutter when changing tracks.
                    // a slight timeout makes it better
                    if (isChromeDesktop() && !player.paused) {
                        player.pause();
                        setTimeout(function() {
                            player.src = src;
                            player.load();
                            player.play();
                        }, 1000);
                    } else {
                        player.pause();
                        player.load();
                        player.src = src;
                        player.play();
                    }
                } else if (!player.paused) {
                    player.pause();
                } else {
                    player.play();
                }             
            }
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

            const tick = function() {
                if (!isNaN(player.duration) && !player.paused && !sliderIsBeingMoved) {
                    const x = `${(player.currentTime / player.duration) * 100 }%`;
                    progressBar.style.width = x;
                }
                ticker = requestAnimationFrame(tick);
            }.bind(this);
            
            const moveSlider = function (ev) {
                cancelAnimationFrame(ticker);
                sliderIsBeingMoved = true;
                slider.classList.add("activePlaySlider");
                const xPos = ev.center.x - slideContainer.offsetLeft;
                progressBar.style.width = `${xPos}px`;
                if (ev.isFinal) {
                    const timeRemaining = (xPos / slideContainer.offsetWidth) * player.duration;
                    player.currentTime = timeRemaining;
                    ticker = requestAnimationFrame(tick);
                    slider.classList.remove("activePlaySlider");
                    sliderIsBeingMoved = false;
                }                
            }.bind(this);

            const tappableSlider = new Hammer(slideContainer);
            tappableSlider.on("pan press tap pressup", moveSlider);

            player.addEventListener('playing', function(e) {
                ticker = requestAnimationFrame(tick);
            }.bind(this));

            const handlePause = function(e) {
                progressBar.style.width = `${(player.currentTime / player.duration) * 100}%`;
                // if the player is still paused on the next animation frame, cancel the ticker
                requestAnimationFrame(function() {
                    if (player.paused) {
                        cancelAnimationFrame(ticker);
                    }
                })
            };
            player.addEventListener('pause', handlePause);
            player.addEventListener('waiting', handlePause);
            player.addEventListener('stalled', handlePause);
            player.addEventListener('ended', function() {
                cancelAnimationFrame(ticker);                
                const index = this.queue.findIndex(song => song.id === this.$data.activeSong.id);
                const nextIndex = (this.queue.length > (index + 1)) ? index + 1 : 0;
                this.playSongToggle(this.queue[nextIndex]);            
            }.bind(this));            
        } 
    }
</script>
<style scoped>
    #audio-player {
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

    .player-active {
        display: block;
    }

</style>