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
                @openAlbum="goToAlbum"
                bullet="artwork"
            />

            <div class="block-link"><router-link to="/songs">ALL SONGS</router-link></div>
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
