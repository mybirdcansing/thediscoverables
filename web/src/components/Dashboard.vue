<template>
    <div class="page-content">        
        <div class='immersive-header'><div class='header-image'></div></div>
        <div class="dashboard-section top-songs">
        <h2 class='band-name'>The Discoverables</h2>
            <h4>Songs</h4>
            <song-list 
                :playing="playing"
                :loadingState="loadingState"
                :activeSong="activeSong"
                :songs="topSongs" 
                @toggleSong="toggleSong"
                @openAlbum="goToAlbum"
                showAlbumLink="true"
                bullet="artwork"
            />

            <div class="block-link"><router-link class="all-songs-link" to="/songs">SHOW ALL</router-link></div>
        </div>
        <div class="dashboard-section albums">
            <h4>Albums</h4>
            <div class="scrolling-wrapper">
                <div class="album card" v-for="album in albumSet" :key="album.id"  @click="goToAlbum(album)">
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
    import StatusEnum from '../store/StatusEnum';
    import SongList from './layout/SongList.vue';


    export default {
        name: "Dashboard",
        mixins: [SongHelperMixin],
        props: [
            "activeSong",
            "loadingState",
            "playing"
        ],
        data: function () {
            return {
                settings: {
                    songLimit: 3,
                    showSongsWithoutAlbums: false,
                }
            }
        },

        components: {
            SongList
        },
        methods: {
            goToAlbum(album) {
                this.$router.push(`/album/${album.id}`);
            },
        },
        computed: {
            topSongs: function() {
                let setlist;
                if (this.homepagePlaylist && this.homepagePlaylist.songs.length > 0) {   
                    const songsWithAlbums = this.getSongsWithAlbums;                 
                    setlist = this.homepagePlaylist.songs.map(function(id) {
                        const homepageSongWithAlbum = songsWithAlbums.find(song => song.id === id);
                        if (homepageSongWithAlbum) {
                            return homepageSongWithAlbum;
                        }
                        return this.getSongById(id);
                    }.bind(this));
                } else {
                    setlist = this.getSongsWithAlbums;
                }
                // only include songs that are in albums
                if (!this.$data.settings.showSongsWithoutAlbums) {
                    setlist = setlist.filter(song => song.album);
                }
                // the dashboard should have no more than 4 songs on the song list
                return (setlist.length <= this.$data.settings.songLimit) ? setlist : setlist.slice(0, this.$data.settings.songLimit);
            },
            ...mapGetters([
                'albumSet',
                'songSet',
                'homepagePlaylist',
                'getSongById',
                'getSongsWithAlbums',
                'catalogState',
                'getSongAlbum',
            ]),
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

.immersive-header {
    width: 100%;
    background: linear-gradient(360deg, rgba(38, 38, 38, 1) 8.98%, rgba(38, 38, 38, 0) 100%) ; 

}

.band-name {
    position: absolute;
    top:45px;
    color:rgba(0, 0, 0, 0.55);
}

.header-image {
    /* background-image: url('../assets/xx-large-adam_bay3.jpg'); */
        background-image: url('../assets/xx-large-adam_bay5.jpg');
    background-repeat: no-repeat;
    background-size: cover;
    background-position-x: center;
    position: relative;
    width: 100%;
    height: 350px;

    z-index: -1;
}
.top-songs {
    margin-top: -34px;
}  
@media (max-width: 542px) { 
    .header-image {
        height: 180px;
    }
    .top-songs {
        margin-top: -30px;
    }    
}

@media (min-width: 892px) { 
    .header-image {
        height: 650px;
    }
   .top-songs {
        margin-top: -60px;
    } 
}



</style>
