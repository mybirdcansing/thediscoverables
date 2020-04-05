<template>
    <div class="page-content album" v-if="album">
        <table class="album-header">
            <tr class="album-header">
                <td class="album-artwork">
                    <img class="album-page-artwork-img" :src="'../artwork/medium~' + album.artworkFilename" :alt="album.title">
                </td>
                <td class="album-description">
                    <div>
                        {{album.title}}
                    </div>
                    <div class="album-details">
                        <!-- 
                        <span v-if="songCount === 1">Single</span>
                        <span v-if="songCount <= 6">EP</span>
                        <span v-if="songCount > 6">Album</span> 
                        • 
                        -->
                        <router-link to="/">The Discoverables</router-link> • {{publishYear}}
                    </div>
                    <div class="album-details">
                        {{songCount}} songs • {{totalMinutes}} minutes
                    </div>
                </td>
            </tr>
        </table>
        <div class="block-link"><play-button @play="setQueueAndPlay(songs)" /></div>
        <h4>Songs</h4>
            <song-list 
                :playing="playing"
                :loadingState="loadingState"
                :activeSong="activeSong"
                :songs="songs" 
                @toggleSong="toggleSong"                
                bullet="index"
            />
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import SongHelperMixin from './SongHelperMixin';
    import { StatusEnum } from '../store/StatusEnum';
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

        },
        computed: {
            ...mapGetters([
                'catalogState',
                'getAlbumById',
                'getAlbumSongs',
            ]),
            album: function() {
                return this.getAlbumById(this.$route.params.id);
            },
            songs: function() {
                if (this.album) {
                    return this.getAlbumSongs(this.album).map(song => {
                        const clone = Vue.util.extend({}, song);
                        clone.album = this.album;
                        return clone;
                    });
                }
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
            publishYear: function() {
                if (this.album && this.album.publishDate) {
                    return new Date(this.album.publishDate).getFullYear();
                }
            }
        },
        mounted() {
            const setTitle = () => {
                this.$el.ownerDocument.title = `${this.$router.currentRoute.meta.title}: ${this.album.title}`;
            };

            if (this.album) {
                setTitle();
            } else {
                this.$watch('album', (newState, oldState) => {
                    if (newState) {
                        setTitle();
                    }
                });             
            }
        },
        created() {
            this.$watch('catalogState', (newState, oldState) => {
                if (newState === StatusEnum.LOADED) {
                    this.$emit("setQueue", this.songs);
                }
            }); 
        }
    }
</script>

<style scoped>
    .page-content.album {
        margin-top:16px;
    }
    .album-header { 
        margin-left: 20px;
    }
    .album-header .album-artwork img{
        margin-left: -4px;
    }
</style>
