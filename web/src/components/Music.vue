<template>
    <div class="music">
        <navbar/>
        <router-view 
            @toggleSong="toggleSong"
            @setQueue="setQueue"
            @setQueueAndPlay="setQueueAndPlay"
            :activeSong="activeSong"
            :loadingState="loadingState"
            :playing="playing"
        ></router-view>
        <PageBottom/>
        <div class="footer-spacer" ref="footerSpacer"></div>
        <footer class="footer" ref="footer" v-bind:class="{'playerActive': showPlayer}">
            <audio id="player" ref="player" :key="audioSrc" :src="audioSrc" preload="auto"></audio>
            
            <div id="slideContainerContainer">
                <div ref="slideContainer"  id="slideContainer">
                    <div ref="progressBar" id="progressBar">
                        <div ref="playSlider" class="playSlider"></div>
                    </div>
                </div>
            </div>
            <div id="timeDisplay">
                <table> 
                    <tr>
                        <!-- <td ></td> -->
                        <td class="title-cell">
                            <span class="song-time">{{currentTimeString}} / {{durationString}}</span>
                            <span class="song-title">{{activeSong.title}}</span> 
                            <span v-if="activeSong.album">
                                • 
                                <router-link class="album-title" 
                                    :to="'/album/' + activeSong.album.id">
                                {{activeSong.album.title}}</router-link>
                              
                            </span>
                        </td>
                    </tr>
                </table>     
            </div>
            <div id="playerControls">
                <table>
                    <tr>
                        <td @click="playPrevious"
                            class="previous" 
                            v-bind:class="{
                                'first-song-active': songIndex === 0,
                            }">
                            <img src="../assets/previous-icon.svg" alt="Next" />
                        </td>                        
                        <td id="playerToggle"
                                @click="toggleSong(activeSong)"
                                v-bind:class="{
                                'loading': loading,
                                'playing': playing,
                                'paused': !playing 
                            }">
                                <img src="../assets/play-icon.svg" alt="Play" class="play">
                                <img src="../assets/pause-icon.svg" alt="Pause" class="pause">
                                <img src="../assets/spinner-icon.svg" alt="Loading" class="spinner">
                        </td>
                        <td 
                            @click="playNext(false)"
                            class="next" 
                            v-bind:class="{
                                'last-song-active': queue.length === songIndex + 1,
                            }">
                            <img src="../assets/next-icon.svg" alt="Next" />
                        </td>
                        <td class="volume-cell">
                            <span v-if="!isIOS">
                                <img @click="lowerVolume" src="../assets/volume_down.svg" alt="volume down" />
                                <input @input="setVolume" ref="playerVolumeSlider" type="range" min="0" max="100">
                                <img @click="raiseVolume" src="../assets/volume_up.svg" alt="volume up" />
                            </span>
                            <span v-else ref="airPlay" id="airPlay">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M6 22h12l-6-6zM21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v-2H3V5h18v12h-4v2h4c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z" fill="white"/>
                                </svg>
                            </span>
                                &nbsp; &nbsp;
                        </td>
                    </tr>
                </table>
            </div>
        </footer>        
    </div>
</template>

<script>
    import "./music.css";
    import Navbar from './Navbar.vue';
    import PageBottom from './layout/PageBottom.vue'
    import { mapGetters, mapActions } from 'vuex';
    import Hammer from 'hammerjs';
    import SongHelperMixin from './SongHelperMixin';
    import StatusEnum from '../store/StatusEnum';

    let ticker;
    let playPromise;
    export default {
        name: "Music",
        mixins: [SongHelperMixin],
        data: function () {
            return {
                audioSrc: '',
                activeSong: { },
                durationString: '00:00',
                currentTimeString: '00:00',
                queue: [],
                loadingState: StatusEnum.INIT,
                playing: false
            }
        },
        components: {
            Navbar,
            PageBottom
        },
        methods: {
            setQueue: function(songs) {
                this.queue = songs;
            },
            setVolume(ev) {
                this.$refs.player.volume = ev.target.value / 100;
            },           
            raiseVolume() {
                let updatedValue = this.$refs.player.volume + 0.1;
                if (updatedValue > 1) updatedValue = 1
                this.$refs.player.volume = updatedValue;
                this.$refs.playerVolumeSlider.value = updatedValue * 100;                
            },
            lowerVolume() {
                let updatedValue = this.$refs.player.volume - 0.1;
                if (updatedValue < 0) updatedValue = 0
                this.$refs.player.volume = updatedValue;
                this.$refs.playerVolumeSlider.value = updatedValue * 100;
            },
            setQueueAndPlay: function(songs) {
                this.queue = songs;
                this.activeSong = { };
                this.toggleSong(songs[0]);
            },
            playPrevious: function() {
                if (this.queue.length === 0 || this.songIndex < 1) {
                    return;
                } 
                this.toggleSong(this.queue[this.songIndex - 1]);
            },
            playNext: function(loop) {
                const queueLength = this.queue.length;
                if (queueLength === 0) {
                    return;
                } 
                // prevent looping?
                if (queueLength === (this.songIndex + 1) && !loop) {
                    return;
                }
                const nextIndex = (queueLength > (this.songIndex + 1)) ? this.songIndex + 1 : 0;
                this.toggleSong(this.queue[nextIndex]);
            },
            toggleSong: function(song) {
                const player = this.$refs.player;
                if (this.activeSong.id !== song.id) {
                    this.activeSong = song;
                    this.loadingState = StatusEnum.LOADING;
                    cancelAnimationFrame(ticker);

                    if (this.playing) {
                        player.pause();
                    }

                    const slideContainer = this.$refs.slideContainer;
                    const spans = slideContainer.getElementsByTagName('span');
                    for (let i = 0; i < spans.length; i++) {
                        slideContainer.removeChild(spans[i]);
                    }

                    player.setAttribute('title', `The Discoverables - ${song.title}`);

                    requestAnimationFrame(function() {
                        this.$refs.progressBar.style.width = '0px';
                    }.bind(this));

                    const src = `/audio/${encodeURI(song.filename)}`;
                    if (this.isChromeDesktop && !player.paused) {
                        // Chrome on Mac has a bit of a stutter when changing tracks.
                        // a timeout makes it better
                        setTimeout(function() {
                            player.src = src;
                            player.load();
                            playPromise = player.play();
                        }, 1000);
                    } else {
                        player.src = src;
                        player.load();
                        playPromise = player.play();
                    }
                } else if (this.playing) {
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            player.pause();
                        })
                        .catch(() => {
                            this.playing = false;
                        });
                    }
                } else {
                    playPromise = player.play();
                }
            },
        },
        computed: {
            songIndex: function() {
                if (!this.queue) {
                    return 0;
                }
                return this.queue.findIndex(song => song.id === this.activeSong.id);
            },            
            showPlayer() {
                return typeof this.activeSong.id === 'string';
            },
            loading() {
                return this.loadingState === StatusEnum.LOADING;
            },
            isIOS() {
                const ua = navigator.userAgent;
                const iOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
                if (iOS) {
                    return true;
                }
                if (ua.indexOf('Macintosh') > -1) {
                    try {
                        document.createEvent("TouchEvent");
                        return true;
                    } catch (e) {}
                }

                return false;
            },
            isChromeDesktop() {
                const ua = navigator.userAgent;
                const isChrome = /Chrome/i.test(ua);
                const isMobile = /Android|webOS|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua) || this.isIOS;
                return ((!isMobile && isChrome));
            },
            duration() {
                return this.activeSong.duration;
                // return this.$refs.player.duration;
            }
        },
        mounted() {    
            const player = this.$refs.player;
            const slider = this.$refs.playSlider;
            const slideContainer = this.$refs.slideContainer;
            const progressBar = this.$refs.progressBar;

            if (this.$refs.playerVolumeSlider) {
                this.$refs.playerVolumeSlider.value = player.volume * 100;
            }

            let sliderBeingSlided = false;

            const tick = () => {
                // if the duration is set, and the player isn't paused, 
                // and the slider isn't being moved,
                // set the slider's position dynamically
                if (!isNaN(this.duration) && !player.paused && !sliderBeingSlided) {
                    const progress = (player.currentTime / this.duration) * 100;
                    progressBar.style.width = `${progress}%`;
                }
                ticker = requestAnimationFrame(tick);
            }

            player.addEventListener('playing', () => {
                this.loadingState = StatusEnum.LOADED;
                this.playing = true;
                ticker = requestAnimationFrame(tick);
            });


            player.addEventListener('timeupdate', () => {
                this.currentTimeString = this.ticksToTimeString(player.currentTime);
            });

            player.addEventListener('durationchange', () => {
                this.durationString = this.ticksToTimeString(this.duration);
            });

            const handleProgress = () => {
                let ranges = [];
                for(let i = 0; i < player.buffered.length; i ++) {
                    ranges.push([
                        player.buffered.start(i),
                        player.buffered.end(i)
                    ]);
                }
                
                //get the current collection of spans inside the slide container
                const spans = slideContainer.getElementsByTagName('span');
                
                //then add or remove spans so we have the same number as time ranges
                while(spans.length < player.buffered.length) {
                    const span = document.createElement('span');
                    slideContainer.appendChild(span);
                }

                while(spans.length > player.buffered.length) {
                    slideContainer.removeChild(slideContainer.lastChild);
                }
                
                for(let i = 0; i < player.buffered.length; i ++) {
                    const durationPercent = (100 / this.duration);
                    spans[i].style.left = Math.round(durationPercent * ranges[i][0]) + '%';
                    spans[i].style.width = Math.round(durationPercent * (ranges[i][1] - ranges[i][0])) + '%';
                }
            }
            
            player.addEventListener('progress', handleProgress, false);
            player.addEventListener('loadedmetadata', handleProgress, false);           

            player.addEventListener('pause', () => {
                progressBar.style.width = `${(player.currentTime / this.duration) * 100}%`;
                // if the player is still paused on the next animation frame, cancel the ticker
                requestAnimationFrame(() => {
                    if (player.paused) {
                        this.playing = false;                      
                        cancelAnimationFrame(ticker);
                    }
                });
            });

            // I have to find a way to test this
            player.addEventListener('stalled', () => {
                this.loadingState = StatusEnum.LOADING;
            });
     
            player.addEventListener('waiting', () => {
                this.loadingState = StatusEnum.LOADING;
            });

            player.addEventListener('ended', () => {
                this.playNext(true);
            });

            (new Hammer(slideContainer)).on("pan press tap pressup", (ev) => {
                cancelAnimationFrame(ticker);
                sliderBeingSlided = true;
                slider.classList.add('activePlaySlider');
                const xPos = ev.center.x - slideContainer.offsetLeft;
                progressBar.style.width = `${xPos}px`;
                if (ev.isFinal) {
                    const timeRemaining = (xPos / slideContainer.offsetWidth) * this.duration;
                    player.currentTime = timeRemaining;
                    ticker = requestAnimationFrame(tick);
                    slider.classList.remove('activePlaySlider');
                    sliderBeingSlided = false;
                }                
            });

            // Detect if AirPlay is available
            // Mac OS Safari 9+ only
            const airPlay = this.$refs.airPlay;
            if (airPlay) {
                if (window.WebKitPlaybackTargetAvailabilityEvent) {
                    player.addEventListener('webkitplaybacktargetavailabilitychanged', function(ev) {
                        switch (ev.availability) {
                            case "available":
                                airPlay.style.display = 'inline-block';
                            break;
    
                            default:
                            airPlay.style.display = 'none';
                        }
                        airPlay.addEventListener('click', function() {
                            player.webkitShowPlaybackTargetPicker();
                        });
                    });
                } else {
                    airPlay.style.display = 'none';
                }
            }

            document.addEventListener("keydown", (ev) => {
                ev = ev || window.event;
                if (ev.keyCode == 32 && this.activeSong.id) {
                    this.toggleSong(this.activeSong)
                }
            });
        },
        watch: {
            showPlayer: function (showing) {
                this.$refs.footerSpacer.style.height =  (showing) ? '110px' : '20px';
            }
        },         
    }
</script>