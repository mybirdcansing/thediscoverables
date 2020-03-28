<template>
    <div class="page-content album" v-if="album">
        <table>
            <tr>
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

        <h4>Songs</h4>
        <table class="song-table">
            <tbody>
                <tr v-for="(song, index) in songs" :key="song.id"  @click="playSongToggle(song)">
                    <td class='song-list-album-cell'>
                        {{index+1}}
                    </td>
                    <td class="song-title-cell">
                        <div class="song-title">{{ song.title }}</div>
                    </td>
                    <td>{{durationToString(song.duration)}}</td>
                    <td>
                        ⋮
                    </td>
                </tr>
            </tbody>
        </table>   
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    import SongHelperMixin from './SongHelperMixin';
    import { StatusEnum } from '../store/StatusEnum';
    export default {
        name: "Album",
        mixins: [SongHelperMixin],        
        components: {
            
        },
        methods: {

        },
        data: function() {
            return {

            }
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
        }
    }
</script>

<style scoped>
    .page-content.album {
        margin-top:16px;
    }
</style>
