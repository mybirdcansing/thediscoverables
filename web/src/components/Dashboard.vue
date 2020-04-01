<template>
    <div class="page-content">
        <h2>The Discoverables</h2>
        <div class='banner-outline'></div>
        <div class="dashboard-section">
            <h4>Songs</h4>
            <table class="song-table">
                <tbody>
                    <tr 
                        v-for="song in topSongs"
                        :key="song.id"
                        class="song-list-row"
                        v-bind:class="{'active-song': activeSong.id === song.id }">
                        <td class='song-list-album-cell' @click="toggleSong(song)">
                            <svg 
                                class="play-button-arrow"
                                viewBox="-5 -5 34 34"
                                preserveAspectRatio="xMidYMid meet" 
                                focusable="false"
                            >
                                <path d="M8 5v14l11-7z" fill="white"></path>
                            </svg>                         
                            <div v-if="song.album" class="song-list-album-artwork-div">
                                <img class='song-list-album-artwork' :src="'../artwork/thumbnail~' + song.album.artworkFilename" :alt="song.album.title">
                            </div>
                        </td>
                        <td class="song-title-cell" @click.prevent="toggleSong(song)">
                            <div><span class="song-title">{{ song.title }}</span></div>
                            <a v-if="song.album" class="album-title" @click="openAlbum(song.album)">{{ song.album.title }}</a>
                        </td>

                        <td>
                            ⋮
                        </td>
                    </tr>
                  
                </tbody>
            </table>
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

    const settings = {
        songLimit: 3,
        showSongsWithoutAlbums: false,
    };

    export default {
        name: "Dashboard",
        mixins: [SongHelperMixin],
        props: [
            "activeSong"
        ],
        components: {
        },
        methods: {
          openAlbum({id}) {
              this.$router.push(`/album/${id}`);
          }
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
            this.$watch('activeSong', (newState, oldState) => {
                console.log(newState);
                
            })
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
