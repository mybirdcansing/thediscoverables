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

            <div class="block-link"><router-link to="/songs">SHOW ALL</router-link></div>
        </div>
        <div class="dashboard-section">
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
            goToAlbum(album) {
                this.$router.push(`/album/${album.id}`);
            },
        },
        computed: {
            topSongs: function() {
                let setlist;
                if (this.homepagePlaylist && this.homepagePlaylist.songs.length > 0) {                    
                    setlist = this.homepagePlaylist.songs.map(id => {
                        const homepageSongWithAlbum = this.songsWithAlbums.find(song => song.id === id);
                        if (homepageSongWithAlbum) {
                            return homepageSongWithAlbum;
                        }
                        return this.$store.state.catalog.songs[id];
                    });
                } else {
                    setlist = this.songsWithAlbums;
                }
                // only include songs that are in albums
                if (!settings.showSongsWithoutAlbums) {
                    setlist = setlist.filter(song => song.album);
                }
                // the dashboard should have no more than 4 songs on the song list
                return (setlist.length <= settings.songLimit) ? setlist : setlist.slice(0, settings.songLimit);
            },
            ...mapGetters([
                'albumSet',
                'songSet',
                'homepagePlaylist',
                'songsWithAlbums',
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
    background-image: url('../assets/xx-large-adam_bay3.jpg');
    background-repeat: no-repeat;
    background-size: cover;
    background-position-x: center;
    position: relative;
    width: 100%;
    height: 400px;

    z-index: -1;
}
@media (max-width: 592px) { 
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
