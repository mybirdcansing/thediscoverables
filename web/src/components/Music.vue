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
        <div class="footer-spacer"></div>
        <footer class="footer" ref="footer" v-bind:class="{'playerActive': showPlayer}">
            <audio id="player" ref="player" :key="audioSrc" :src="audioSrc" preload="auto" controls></audio>
            <div ref="slideContainer"  id="slideContainer">
                <div ref="progressBar" id="progressBar">
                    <div ref="playSlider" class="playSlider"></div>
                </div>
            </div>
            <div>
                <button ref="pausePlayer" @click="toggleSong(activeSong)">Play/Pause</button>
                <span v-text="currentTimeString"></span> | <span v-text="durationString"></span> | 
                <span v-text="activeSong.title"></span> | <span v-text="songAlbumTitle"></span> |
                <span ref="airPlay" id="airPlay">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M6 22h12l-6-6zM21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v-2H3V5h18v12h-4v2h4c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z" fill="white"/>
                    </svg>
                </span>
            </div>
        </footer>        
    </div>
</template>

<script>
    import "./music.css";
    import Navbar from './Navbar.vue';
    import { mapGetters, mapActions } from 'vuex';
    import Hammer from 'hammerjs';
    import SongHelperMixin from './SongHelperMixin';
    import StatusEnum from '../store/StatusEnum';

    let ticker = null;
    let playPromise;
    export default {
        name: "Music",
        mixins: [SongHelperMixin],
        data: function () {
            return {
                audioSrc: '', //data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA
                activeSong: { id: null },
                durationString: '00:00',
                currentTimeString: '00:00',
                queue: [],
                loadingState: StatusEnum.INIT,
                playing: false
            }
        },
        components: {
            Navbar
        },
        methods: {
            setQueue: function(songs) {
                this.queue = songs;
            },
            setQueueAndPlay: function(songs) {
                this.queue = songs;
                this.activeSong = { id: null };
                this.toggleSong(songs[0]);
            },
            toggleSong: function(song) {
                const player = this.$refs.player;
                if (this.activeSong.id !== song.id) {
                    this.activeSong = song;
                    this.loadingState = StatusEnum.LOADING;

                    const progressBar = this.$refs.progressBar;
                    const slideContainer = this.$refs.slideContainer;
                    cancelAnimationFrame(ticker);
                    
                    if (this.playing) {
                        player.pause();
                    }

                    const spans = slideContainer.getElementsByTagName('span');
                    for (let i = 0; i < spans.length; i++) {
                        slideContainer.removeChild(spans[i]);
                    }

                    player.setAttribute('title', `The Discoverables - ${song.title}`);
                    requestAnimationFrame(function() {
                        progressBar.style.width = '0px';
                    });
                    const src = `/audio/${encodeURI(song.filename)}`;
                    if (isChromeDesktop() && !player.paused) {
                        // Chrome on Mac has a bit of a stutter when changing tracks.
                        // a slight timeout makes it better
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
                
                function isChromeDesktop() {
                    const ua = navigator.userAgent;
                    const isChrome = /Chrome/i.test(ua);
                    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua);
                    return ((!isMobile && isChrome));
                }                
            },
            
        },

        computed: {
            showPlayer() {
                return this.activeSong.id !== null;
            },
            songAlbumTitle() {
                if (this.activeSong.album) {
                    return this.activeSong.album.title;
                }
            },
            ...mapGetters([
                'songsWithAlbums',
            ])
        },
        mounted() {          
            const player = this.$refs.player;
            const slider = this.$refs.playSlider;
            const slideContainer = this.$refs.slideContainer;
            const progressBar = this.$refs.progressBar;
            const airPlay = this.$refs.airPlay;
            const footer = this.$refs.airPlay.footer;

            let sliderBeingSlided = false;

            player.addEventListener('playing', (ev) => {
                this.loadingState = StatusEnum.LOADED;
                this.playing = true;
                ticker = requestAnimationFrame(tick);
            });

            function tick() {
                // if the duration is set, 
                // and the player isn't paused, 
                // and the slider isn't being moved,
                if (!isNaN(player.duration) && !player.paused && !sliderBeingSlided) {
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

            player.addEventListener('progress', handleProgress, false);
            player.addEventListener('loadedmetadata', handleProgress, false);           

            function handleProgress() {
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
                    const durationPercent = (100 / player.duration);
                    spans[i].style.left = Math.round(durationPercent * ranges[i][0]) + '%';
                    spans[i].style.width = Math.round(durationPercent * (ranges[i][1] - ranges[i][0])) + '%';
                }
            }

            const handlePause =  (ev) => {
                progressBar.style.width = `${(player.currentTime / player.duration) * 100}%`;
                // if the player is still paused on the next animation frame, cancel the ticker
                requestAnimationFrame(() => {
                    if (player.paused) {
                        this.playing = false;                      
                        cancelAnimationFrame(ticker);
                    }
                });
            }

            player.addEventListener('pause', handlePause);

            // I have to find a way to test this
            // player.addEventListener('stalled', handlePause);

            player.addEventListener('ended', function () {
                cancelAnimationFrame(ticker);
                if (this.queue.length === 0) {
                    return;
                } 
                const index = this.queue.findIndex(song => song.id === this.activeSong.id);
                // prevent looping of the queue...?
                // if (this.queue.length > (index + 1)) {
                //     return;
                // }
                const nextIndex = (this.queue.length > (index + 1)) ? index + 1 : 0;
                this.toggleSong(this.queue[nextIndex]);            
            }.bind(this));

            (new Hammer(slideContainer)).on("pan press tap pressup", function(ev) {
                cancelAnimationFrame(ticker);
                sliderBeingSlided = true;
                slider.classList.add('activePlaySlider');
                const xPos = ev.center.x - slideContainer.offsetLeft;
                progressBar.style.width = `${xPos}px`;
                if (ev.isFinal) {
                    const timeRemaining = (xPos / slideContainer.offsetWidth) * player.duration;
                    player.currentTime = timeRemaining;
                    ticker = requestAnimationFrame(tick);
                    slider.classList.remove('activePlaySlider');
                    sliderBeingSlided = false;
                }                
            });

            // Detect if AirPlay is available
            // Mac OS Safari 9+ only
            if (window.WebKitPlaybackTargetAvailabilityEvent) {
                player.addEventListener('webkitplaybacktargetavailabilitychanged', function(event) {
                    switch (event.availability) {
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

            document.addEventListener("keydown", function(e) {
                e = e || window.event;
                if (e.keyCode == 32 && this.activeSong.id) {
                    this.toggleSong(this.activeSong)
                }
            }.bind(this));
        } 
    }
</script>
<style>

    audio {
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
    .footer-spacer {
        height: 20px;
    }

    #slideContainer {
        position: relative;
        height: 10px;
        width: 100%;
        margin: 6px auto 12px auto;
        background: #d3d3d3;
        box-shadow:outset 0 0 0 1px rgb(157, 154, 154), outset 0 0 0 2px rgb(202, 203, 201);
        cursor: pointer;
        overflow: visible;
    }
  
    #slideContainer span {
        position:absolute;
        left:0;
        top:0;
        display:inline-block;
        background: #b6b6b6;
        z-index: 80;
    }  
    #slideContainer:hover .playSlider {
        position: relative;
        opacity: 0;
        margin-right: -9px;
        margin-top: -4px;
        width: 18px;
        height: 18px;
        float: right;
        border-radius: 50%; 
        background: #4CAF50;
        cursor: pointer;
        z-index: 100;
    }
    
    #slideContainer:hover .playSlider {
        opacity: 1;
    }

    .playSlider.activePlaySlider {
        box-shadow: 0 3px 0 rgb(75, 84, 79);
        opacity: 1;
        width: 36px;
        height: 36px;
        margin-left: -18px;
        margin-top: -12px;
        transition: all 0;
    }

    #progressBar {
        position: absolute;
        display:block;
        width: 0px;
        height: 10px;
        background: rgb(114, 105, 123);
        cursor: pointer;
        top: 0px;
        left: 0px;
        z-index: 90;
    }

    .playerActive {
        display: block;
    }

</style>