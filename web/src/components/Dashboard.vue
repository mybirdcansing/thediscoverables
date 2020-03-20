<template>
    <div>
        <h2>Music</h2>
        <div class='banner-outline'></div>
        <div class="dashboard-section">
            <h4>Songs</h4>
            <table class="song-table">
                <tbody>
                    <tr v-for="song in topSongs" :key="song.id"  @click="playSongToggle(song)">
                        <td class='song-list-album-cell'>
                            <div v-if="song.album">
                                <img class='song-list-album-artwork' :src="'../artwork/' + song.album.artworkFilename" :alt="song.album.title">
                            </div>
                        </td>
                        <td class="song-title-cell">
                            <div class="song-title">{{ song.title }}</div>
                            <div v-if="song.album" class="album-title">{{ song.album.title }}</div>
                        </td>
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
                        <img class='album-list-album-artwork' :src="'../artwork/' + album.artworkFilename" :alt="album.title">
                    </div>
                    <div class="album-title">{{ album.title }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    export default {
        name: "Dashboard",
        components: {
        },
        data: function() {
            return {
            }
        }, 
        methods: {
          openAlbum({id}) {
              console.log(`Album: ${id}`);
          },
          playSongToggle(song) {
            console.log(`Song: ${song.id}`);
            this.$emit("playSongToggle", song);
          }
        },
        computed: {
            topSongs: function() {
                const set = this.songSet.map(song => {
                    const clone = Vue.util.extend({}, song);
                    clone.album = this.getSongAlbum(song);                    
                    return clone;
                }).filter(song => song.album);
                return (set.length < 4) ? set : set.slice(0, 3);
            },
            ...mapGetters([
                'albumSet',
                'songSet',
                'getSongAlbum'
            ])
        }
    }
</script>

<style scoped>
</style>
