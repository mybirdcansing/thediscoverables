<template>
    <div class="page-content songs">
        <div class="block-link"><play-button @play="setQueueAndPlay(songs)" /></div>
        <h4>Songs</h4>
            <song-list 
                :playing="playing"
                :loadingState="loadingState"
                :activeSong="activeSong"
                :songs="songs" 
                @toggleSong="toggleSong"
                @openAlbum="openAlbum"              
                bullet="artwork"
            />
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import SongHelperMixin from './SongHelperMixin';
    import StatusEnum from '../store/StatusEnum';
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
            openAlbum(album) {
                this.$router.push(`/album/${album.id}`);
            },
          ...mapGetters([
                'songsWithAlbums'
            ]),            
        },        
        computed: {
            ...mapGetters([
                'catalogState',
            ]),
            songs: function() {
                return this.songsWithAlbums();
            },
            songCount() {
                if (this.songs) {
                    return this.songs.length;
                } 
            },
            totalMinutes: function() {
                let runtime = 0;
                this.songs.forEach(song => runtime += parseFloat(song.duration));
                return Math.floor(runtime / 60);
            },

        },
        created() {
            if (this.catalogState === StatusEnum.LOADED) {
                this.$emit("setQueue", this.songs);
            } else {
                this.$watch('catalogState', (newState, oldState) => {
                    if (newState === StatusEnum.LOADED) {
                        this.$emit("setQueue", this.songs);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>
