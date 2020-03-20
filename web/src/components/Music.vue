<template>
    <div class="music">
         <navbar/>
        <div class="page-content">
            <router-view @playSongToggle="playSongToggle"></router-view>
        </div>
        <footer class="footer-player">
            <div ref="slideContainer"  class="slidecontainer">
              <!-- <input type="range" min="1" max="100" value="50" class="slider" id="playSlider"  ref="playSlider"> -->
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
            ...mapGetters([
                'songSet',
                'getSongAlbum'
            ])
        },
        mounted() {
            const player = this.$refs.player;
            const slider = this.$refs.playSlider;
            const slideContainer = this.$refs.slideContainer;
            const mc = new Hammer(slider);
            mc.on("pan", handleDrag);
            let lastPosX = 0;
            let isDragging = false;
            function handleDrag(ev) {
                let elem = ev.target;
                slider.style.transition = '';
                if (!isDragging) {
                    isDragging = true;
                    lastPosX = elem.offsetLeft;
                }
                let posX = ev.deltaX + lastPosX;
                elem.style.left = posX + "px";
 
                if (ev.isFinal) {
                    isDragging = false;
                    player.pause();
                    const timeRemaining = (posX / slideContainer.offsetWidth) * player.duration;
                    player.currentTime = timeRemaining;
                    player.play();                 
                }
            }
            
            
            player.addEventListener('play', function(e) {
                const timesToTry = 100;
                let tryTime = 0;
                const move = function() {
   
                    if (tryTime === timesToTry) {
                        return; // this is taking too long
                    }
                    if (isNaN(player.duration)) {
                        tryTime++;
                        requestAnimationFrame(move);
                        return;
                    }                   
                    const timeRemaining = player.duration - player.currentTime;
                    slider.style.transition = `all ${timeRemaining}s 0s cubic-bezier(0, 0, 1, 1)`;
                    requestAnimationFrame(function() {
                        slider.style.left = `${slideContainer.offsetWidth}px`;
                    });
                };
                requestAnimationFrame(move);
            }.bind(this));


            const handlePause = function(e) {
                const player = this.$refs.player;
                if (isNaN(player.duration)) {
                    return;
                }
                const slider = this.$refs.playSlider;
                const slideContainer = this.$refs.slideContainer;
                slider.style.transition = '';
                slider.style.left = `${((player.currentTime / player.duration) * slideContainer.offsetWidth)}px`;
                // slider.style.left = `${((player.currentTime / player.duration) * 100)}%`;
            }.bind(this);
            player.addEventListener('pause', handlePause);
            player.addEventListener('waiting', handlePause);
            player.addEventListener('stalled', handlePause);
            player.addEventListener('ended', function() {
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