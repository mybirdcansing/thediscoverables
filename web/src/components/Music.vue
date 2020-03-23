<template>
    <div class="music">
         <navbar/>
        <div class="page-content">
            <router-view @playSongToggle="playSongToggle"></router-view>
        </div>
        <footer class="footer-player" v-bind:class="{'player-active': showPlayer}">
            <div ref="slideContainer"  class="slidecontainer">
                <div ref="playSlider" id="playSlider"></div>
                <div ref="progressBar" id="progressBar"></div>
            </div>

            <audio id="audio-player" controls ref="player" :key="audioSrc" preload="auto" v-bind:src="audioSrc"></audio>
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

    export default {
        name: "Music",
        data: function () {
            return {
                ticker: null,
                audioSrc: 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA',
                activeSong: {id:null}
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

                slider.style.transition = '';
                if (this.$data.activeSong.id !== song.id) {
                    this.$data.activeSong = song;
                    cancelAnimationFrame(this.$data.ticker);
                    requestAnimationFrame(function() {
                        slider.style.left = '0px';
                        progressBar.style.width = '0px';
                    });
                    
                    const isChromeDesktop = function() {
                        const ua = navigator.userAgent;
                        const isChrome = /Chrome/i.test(ua);
                        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua);
                        return ((!isMobile && isChrome));
                    };

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
                return !this.$data.activeSong.id;
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
            let isMovingSlider = false;
            document.addEventListener("touchstart", event => {
                if(event.touches.length > 1) {
                    event.preventDefault();
                    event.stopPropagation(); // maybe useless
                }
            }, {passive: false});

            const tick = function() {
                if (!isNaN(player.duration) && !player.paused && !isMovingSlider) {
                    const x = `${(player.currentTime / player.duration) * 100 }%`;
                    slider.style.left = x;
                    progressBar.style.width = x;
                }
                this.$data.ticker = requestAnimationFrame(tick);
            }.bind(this);
            
            const moveSlider = function (ev) {
                cancelAnimationFrame(this.$data.ticker);
                isMovingSlider = true;
                const x = ev.center.x - slideContainer.offsetLeft;
                slider.classList.add("activePlaySlider");
                slider.style.left = `${x}px`;
                progressBar.style.width = `${x}px`;
                if (ev.isFinal) {
                    const timeRemaining = (x / slideContainer.offsetWidth) * player.duration;
                    player.currentTime = timeRemaining;
                    this.$data.ticker = requestAnimationFrame(tick);
                    slider.classList.remove("activePlaySlider");
                    isMovingSlider = false;
                }                
            }.bind(this);

            const tappableSlider = new Hammer(slideContainer);
            tappableSlider.on("pan press tap pressup", moveSlider);

            player.addEventListener('playing', function(e) {
                this.$data.ticker = requestAnimationFrame(tick);
            }.bind(this));

            const handlePause = function(e) {
                const x = `${(player.currentTime / player.duration) * 100 }%`;
                slider.style.left = x;
                progressBar.style.width = x;
                // if the player is still paused on the next animation frame, cancel the ticker
                requestAnimationFrame(function() {
                    if (player.paused) {
                        cancelAnimationFrame(this.$data.ticker);
                    }
                }.bind(this))
            }.bind(this);
            player.addEventListener('pause', handlePause);
            player.addEventListener('waiting', handlePause);
            player.addEventListener('stalled', handlePause);
            player.addEventListener('ended', function() {
                cancelAnimationFrame(this.$data.ticker);                
                const index = this.queue.findIndex(song => song.id === this.$data.activeSong.id);
                const nextIndex = (this.queue.length > (index + 1)) ? index + 1 : 0;
                this.playSongToggle(this.queue[nextIndex]);            
            }.bind(this));            
        } 
    }
</script>
<style scoped>

    audio::-webkit-media-controls-panel  {
        background-color: #909090; 
    }
    #audio-player {
        width: 0;
        height: 0;
        margin-left: -3000px;
    } 
    
    .footer-player {
        position: fixed;
        text-align: center;
        left: 0;
        bottom: 0;
        width: 100%;
        padding: 0 40px 0 40px;
        height: 60px;
        background-color: #909090;
    }

    .slidecontainer {
        position: relative;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        height: 10px;
        background: #d3d3d3;
        cursor: pointer;
        border-radius: 15px; 

    }

    #playSlider {
        position: absolute;
        margin-left: -9px;
        margin-top: -4px;
        
        width: 18px;
        height: 18px;
        left: 0px;
        border-radius: 50%; 
        background: #4CAF50;
        cursor: pointer;
        transition-timing-function: linear;
        z-index: 100;
    }

    #playSlider.activePlaySlider {
        box-shadow: 0 3px 0 rgb(75, 84, 79);
        width: 36px;
        height: 36px;
        margin-left: -18px;
        margin-top: -12px;        
      
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
        display: none;
    }

</style>