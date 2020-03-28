<template>
    <div class="page-content">
        <h2>The Discoverables</h2>
        <div class='banner-outline'></div>
        <div class="dashboard-section">
            <h4>Songs</h4>
            <table class="song-table">
                <tbody>
                    <tr v-for="song in topSongs" :key="song.id"  @click="playSongToggle(song)">
                        <td class='song-list-album-cell'>
                            <div v-if="song.album">
                                <img class='song-list-album-artwork' :src="'../artwork/thumbnail~' + song.album.artworkFilename" :alt="song.album.title">
                            </div>
                        </td>
                        <td class="song-title-cell">
                            <div class="song-title">{{ song.title }}</div>
                            <div v-if="song.album" class="album-title" @click="openAlbum(song.album)">{{ song.album.title }}</div>
                        </td>
                        <td>{{durationToString(song.duration)}}</td>
                        <td>
                            ⋮
                        </td>
                    </tr>
                </tbody>
            </table>
            <div><a href="#" class="dashboard-page-link">SHOW ALL</a></div>
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
    export default {
        name: "Dashboard",
        mixins: [SongHelperMixin],
        components: {
        },
        data: function() {
            return {
            }
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
                        const homepageSongWithAlbum = this.songsWithAlbums.find(song => {
                            return song.id === id;
                        });
                        if (homepageSongWithAlbum) {
                            return homepageSongWithAlbum;
                        }
                        return this.$store.state.catalog.songs[id];
                    });
                } else {
                    set = this.songsWithAlbums;
                }
                // only include songs that are in albums
                set = set.filter(song => song.album);
                // the dashboard should have no more than 4 songs on the song list
                return (set.length <= 4) ? set : set.slice(0, 4);
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
