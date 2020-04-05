<template>
    <div class="page-content dashboard">
        <h2>The Discoverables</h2>
        <div class='banner-outline'></div>
        <div class="dashboard-section">
            <h4>Songs</h4>
            <song-list 
                :playing="playing"
                :loadingState="loadingState"
                :activeSong="activeSong"
                :songs="topSongs" 
                @toggleSong="toggleSong"
                @openAlbum="openAlbum"
                bullet="artwork"
            />
            <!-- <table class="song-table">
                <tbody>
                    <tr 
                        v-for="song in topSongs"
                        :key="song.id"
                        class="song-list-row"
                        v-bind:class="{
                            'active-song': isActiveSong(song),
                            'loading': isActiveSong(song) && loading,
                            'playing': isActiveSong(song) && playing,
                            'paused': isActiveSong(song) && !playing 
                        }">
                        <td class='song-list-album-cell' @click="toggleSong(song)">
                            <svg 
                                class="play-button-arrow" xmlns="http://www.w3.org/2000/svg" 
                                viewBox="-5 -5 34 34" 
                                preserveAspectRatio="xMidYMid meet">
                                <path d="M8 5v14l11-7z" fill="white"></path>
                            </svg>

     						<svg class='pause' xmlns="http://www.w3.org/2000/svg" 
                                viewBox="-5 -5 34 34" 
                                preserveAspectRatio="xMidYMid meet">
                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"  fill="white"/></svg>								
                            <svg class="spinner" v-bind:class="{'active': isActiveSong(song) && loading}"
                                viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                                <g fill="none" fill-rule="evenodd" stroke-width="2">
                                    <circle cx="22" cy="22" r="1">
                                        <animate attributeName="r"
                                            begin="0s" dur="1.8s"
                                            values="1; 20"
                                            calcMode="spline"
                                            keyTimes="0; 1"
                                            keySplines="0.165, 0.84, 0.44, 1"
                                            repeatCount="indefinite" />
                                        <animate attributeName="stroke-opacity"
                                            begin="0s" dur="1.8s"
                                            values="1; 0"
                                            calcMode="spline"
                                            keyTimes="0; 1"
                                            keySplines="0.3, 0.61, 0.355, 1"
                                            repeatCount="indefinite" />
                                    </circle>
                                    <circle cx="22" cy="22" r="1">
                                        <animate attributeName="r"
                                            begin="-0.9s" dur="1.8s"
                                            values="1; 20"
                                            calcMode="spline"
                                            keyTimes="0; 1"
                                            keySplines="0.165, 0.84, 0.44, 1"
                                            repeatCount="indefinite" />
                                        <animate attributeName="stroke-opacity"
                                            begin="-0.9s" dur="1.8s"
                                            values="1; 0"
                                            calcMode="spline"
                                            keyTimes="0; 1"
                                            keySplines="0.3, 0.61, 0.355, 1"
                                            repeatCount="indefinite" />
                                    </circle>
                                </g>
                            </svg>                            
    
                            <div v-if="song.album" class="song-list-album-artwork-div">
                                <img class='song-list-album-artwork' :src="'../artwork/thumbnail~' + song.album.artworkFilename" :alt="song.album.title">
                            </div>
                        </td>
                        <td class="song-title-cell" @click.prevent="toggleSong(song)">
                            <div class="song-title">{{ song.title }}</div>
                            <a v-if="song.album" class="album-title" @click="openAlbum(song.album)">{{ song.album.title }}</a>
                        </td>

                        <td>
                            ⋮
                        </td>
                    </tr>
                  
                </tbody>
            </table> -->
            <div class="block-link"><a href="#" class="dashboard-page-link">SHOW ALL</a></div>
        </div>
        <div class="dashboard-section">
            <h4>Albums</h4>
            <div class="scrolling-wrapper">
                <div class="album card" v-for="album in albumSet" :key="album.id"  @click="openAlbum(album)">
                    <div>
                        <img class='album-list-album-artwork' :src="'../artwork/medium~' + album.artworkFilename" :alt="album.title">
                    </div>
                    <div class="album-title">{{ album.title }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import SongHelperMixin from './SongHelperMixin';
    import { StatusEnum } from '../store/StatusEnum';
    import SongList from './layout/SongList.vue';
    const settings = {
        songLimit: 3,
        showSongsWithoutAlbums: false,
    };

    export default {
        name: "Dashboard",
        mixins: [SongHelperMixin],
        props: [
            "activeSong",
            "loadingState",
            "playing"
        ],
        components: {
            SongList
        },
        methods: {
            openAlbum({id}) {
                this.$router.push(`/album/${id}`);
            },
           
        },
        computed: {

            topSongs: function() {
                let set;
                if (this.homepagePlaylist && this.homepagePlaylist.songs.length > 0) {                    
                    set = this.homepagePlaylist.songs.map(id => {
                        const homepageSongWithAlbum = this.songsWithAlbums.find(song => song.id === id);
                        if (homepageSongWithAlbum) {
                            return homepageSongWithAlbum;
                        }
                        return this.$store.state.catalog.songs[id];
                    });
                } else {
                    set = this.songsWithAlbums;
                }
                // only include songs that are in albums
                if (!settings.showSongsWithoutAlbums) {
                    set = set.filter(song => song.album);
                }
                // the dashboard should have no more than 4 songs on the song list
                return (set.length <= settings.songLimit) ? set : set.slice(0, settings.songLimit);
            },
            ...mapGetters([
                'albumSet',
                'songSet',
                'homepagePlaylist',
                'songsWithAlbums',
                'catalogState',
                'getSongAlbum',
            ])
        },
        created() {
            this.$watch('catalogState', (newState, oldState) => {
                if (newState === StatusEnum.LOADED) {
                    this.$emit("setQueue", this.topSongs);
                }
            }); 
        }
    }
</script>

<style scoped>
.music .page-content {      
    background: url('../assets/dashboard_background.jpg') no-repeat center center fixed; 
    -webkit-background-size: contain;
    -moz-background-size: contain;
    -o-background-size: contain;
    background-size:contain;
    background-position: top;
}

.music .banner-outline {
    padding: 12%;
}

</style>
