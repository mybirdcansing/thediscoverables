<template>
    <div class="music">
         <navbar/>
        <div class="page-content">
            <router-view @playSongToggle="playSongToggle"></router-view>
        </div>
        <footer class="footer-player">
            <div ref="slideContainer"  class="slidecontainer">
              <!-- <input type="range" min="1" max="100" value="50" class="slider" id="playSlider"  ref="playSlider">
               @click="adjustSlider" -->
                <div ref="playSlider" id="playSlider"></div>
            </div>
            <audio id="audio-player" controls ref="player" :key="audioSrc" preload="auto" v-bind:src="audioSrc"></audio>
        </footer>        
    </div>
</template>

<script>
    import "./music.css";
    import Navbar from './Navbar.vue';
    import { mapGetters } from 'vuex';
    import Hammer from 'hammerjs';
    const isChromeDesktop = function() {
        const ua = navigator.userAgent;
        const isChrome = /Chrome/i.test(ua);
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua);
        return ((!isMobile && isChrome));
    }

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
                const src = `/audio/${encodeURI(song.filename)}`;
                player.setAttribute('title', `The Discoverables - ${song.title}`);

                slider.style.transition = '';
                if (this.$data.activeSong.id !== song.id) {
                    this.$data.activeSong = song;
                    cancelAnimationFrame(this.$data.ticker);
                    requestAnimationFrame(function() {
                        slider.style.left = '-5px';
                    });
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
            ...mapGetters([
                'songSet',
                'getSongAlbum'
            ])
        },
        mounted() {
            const player = this.$refs.player;
            const slider = this.$refs.playSlider;
            const slideContainer = this.$refs.slideContainer;
            const tick = function() {
                if (!isNaN(player.duration) && !player.paused) {
                    slider.style.left = `${(player.currentTime / player.duration) * 100 }%`;
                }
                this.$data.ticker = requestAnimationFrame(tick);
            }.bind(this);
            
            const moveSlider = function (ev) {
                cancelAnimationFrame(this.$data.ticker);
                slider.style.left = `${ev.center.x - 9}px`;
                if (ev.isFinal) {
                    const timeRemaining = ((ev.center.x - 9) / slideContainer.offsetWidth) * player.duration;
                    player.currentTime = timeRemaining;           
                }                
            }.bind(this);

            const tappableSlider = new Hammer(slideContainer);
            tappableSlider.on("pan press tap pressup", moveSlider);

            const dragableSlider = new Hammer(slider);
            dragableSlider.on("pan", moveSlider);

            player.addEventListener('playing', function(e) {
                console.log("playing event bubbled");
                this.$data.ticker = requestAnimationFrame(tick);
            }.bind(this));

            const handlePause = function(e) {
                slider.style.left = `${(player.currentTime / player.duration) * 100}%`;
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
                const nextIndex = (this.songSet.length > index) ? index + 1 : 0;
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
        left: 0;
        bottom: 0;
        width: 100%;
        padding: 0px 0;
        height: 50px;
        background-color: #909090;
    }

    .slidecontainer {
        position: absolute;
        width: 100%;
        height: 5px;
        background: #d3d3d3;
        cursor: pointer;
    }

    #playSlider {
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%; 
        background: #4CAF50;
        cursor: pointer;
        top: -6px;
        transition-timing-function: linear;
        left: -5px;
    }

    .activePlaySlider {
        box-shadow: 0 3px 0 #00823F;
        width: 36px;
        height: 36px;
        top: -12px;
    }


</style>