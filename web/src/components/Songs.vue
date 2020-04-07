<template>
    <div class="page-content songs">
        <h2>The Discoverables</h2>
        <h4>All Songs</h4>
        <div class="block-link"><play-button @play="setQueueAndPlay(songsWithAlbums)" /></div>
            <song-list 
                :playing="playing"
                :loadingState="loadingState"
                :activeSong="activeSong"
                :songs="songsWithAlbums" 
                @toggleSong="toggleSong"
                @openAlbum="goToAlbum"  
                showAlbumLink="true"            
                bullet="artwork"
            />
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import SongHelperMixin from './SongHelperMixin';
    import PlayButton from './layout/PlayButton.vue';
    import SongList from './layout/SongList.vue';
    export default {
        name: "Album",
        mixins: [SongHelperMixin],
        props: [
            "activeSong",
            "loadingState",
            "playing"
        ],        
        components: {
            PlayButton,
            SongList
        },
        methods: {
            goToAlbum(album) {
                this.$router.push(`/album/${album.id}`);
            },          
        },        
        computed: {
            ...mapGetters([
                'songsWithAlbums'
            ]),
        },
    }
</script>

<style scoped>

</style>
