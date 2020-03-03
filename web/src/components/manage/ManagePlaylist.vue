<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Playlist</h2>
            <router-link to="/manager/playlists" class="text-secondary">&lt;&lt; Back to Playlists</router-link>
        </div>
        <div class="container">
            <form v-on:submit.prevent="savePlaylist">
                <table class="table table-borderless table-sm">
                    <tbody>
                        <tr>
                            <td>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="goToPlaylistsPage">Cancel</button>
                            </td>
                            <td>
                                <div class="float-right">
                                <button class="btn-xs btn-outline-secondary" 
                                    @click.self.prevent="confirmDeleteItem(playlist)">Delete</button>
                            </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <form-alerts v-bind:errors="errors" 
                    v-bind:showSavingAlert="showSavingAlert" 
                    savingMessage="Saving playlist..." />
                <div class="form-group">
                    <label for="managePlaylistTitle">Title</label>
                    <input v-model="playlist.title" 
                        type="text" 
                        id="managePlaylistTitle" 
                        class="form-control" 
                        placeholder="Enter title">
                </div>
                <div class="form-group">
                    <label for="managePlaylistDescription">Description</label>
                    <textarea v-model="playlist.description" class="form-control" id="managePlaylistDescription" placeholder="Enter description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <h6>Songs in Playlist</h6>
                            <draggable class="list-group droppable-song-list"
                                v-bind="dragOptions" 
                                @update="updateOrder"
                                @add="addSong"
                                @remove="removeSong"
                                :list="playlistSongs" 
                                group="songLists" @change="log">
                                <div class="list-group-item clickable-text"
                                    v-for="song in playlistSongs"
                                    :key="song.orderIndex">
                                    {{ song.title }}
                                </div>
                            </draggable>
                        </div>
                        <div class="col-6">
                            <h6>Songs Not in Playlist</h6>
                            <draggable class="list-group droppable-song-list"
                                v-bind="dragOptions" 

                                :list="songsNotInPlaylist"
                                group="songLists" @change="log">
                                <div class="list-group-item clickable-text"
                                    v-for="song in songsNotInPlaylist"
                                    :key="song.orderIndex">
                                    {{ song.title }}
                                </div>
                            </draggable>
                        </div>
                    </div>
                </div>
        
            </form>
        </div>  
        <modal v-if="showModal" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import draggable from "vuedraggable";
    import { mapActions, mapGetters } from 'vuex';
    import FormAlerts from './FormAlerts.vue';
    import Modal from './Modal.vue';
    import { StatusEnum } from '../../store/StatusEnum';
    export default {
        name: "ManagePlaylist",
        display: "Manage Playlist",
        components: {
            FormAlerts,
            Modal,
            draggable
        },
        data: function() {
            return {
                errors: [],
                showSavingAlert: false,
                showModal: false,
                itemToDelete: null,
                playlistSongs: [],
                songsNotInPlaylist: []
            }
        },
        methods: {
            confirmDeleteItem(playlist) {
                this.$data.itemToDelete = playlist;
                this.$data.showModal = true;    
            },
            closeDeleteItemModal() {
                this.$data.showModal = false;
                this.$data.itemToDelete = null;
            },
            submitDelete() {
                const options = {
                    id: this.$data.itemToDelete.id,
                    handler: 'playlist'
                };
                this.deleteItem(options).then(() => {
                    this.closeDeleteItemModal();
                    this.$router.push('/manager/playlists')
                }).catch((data) => {
                    console.log(data.errorMessages);
                });
            },
            savePlaylist(e) {
                this.showSavingAlert = true;
                const songsIndex = [];
                this.playlist.songs = this.playlistSongs.map(function(song, index) {
                    songsIndex.push({
                        playlistId: this.playlist.id,
                        songId: song.id,
                        orderIndex: index + 1
                    });
                    s.orderIndex = index + 1;
                    return song.id;
                }.bind(this));
                
                const saveAction = (this.playlist.id) ? this.updatePlaylist : this.createPlaylist;
                const actionOptions = {
                    playlist: this.playlist,
                    songsIndex: songsIndex
                }
                saveAction(actionOptions).then((response) => {
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                    }, 1000);
                }).catch(function(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }.bind(this));
            },
            goToPlaylistsPage() {
                this.$router.push('/manager/playlists');
            },
            ...mapGetters([
                'getPlaylistById',
            ]),
            ...mapActions([
                'deleteItem',
                'updatePlaylist',
                'createPlaylist'
            ]),
            log: function(e) {
                console.log(e);
            },
            // onMove({ relatedContext, draggedContext }) {
            //     const relatedElement = relatedContext.element;
            //     const draggedElement = draggedContext.element;
            //     return ((!relatedElement || !relatedElement.fixed) && !draggedElement.fixed);
            // },
            addSong(e) {

            },
            removeSong(e) {

            },
            updateOrder(e) {

            }
        },
        created() {
            let frameCount = 0;
            const tick = function () {
                frameCount++;
                if (this.catalogState !== StatusEnum.LOADED) {
                    if (frameCount > 600 || this.catalogState === StatusEnum.ERROR) {
                        console.error('it took too long to get the catalog :(')
                        return;
                    }
                    requestAnimationFrame(tick);
                    return;
                }
                
                this.playlistSongs = this.playlist.songs.map(function(songId, orderIndex) {
                        const song = Vue.util.extend({}, this.songs[songId]);
                        const playlistSongEntry = this.playlistSongIndex.find((pls) => {
                            return pls.songId === songId && pls.playlistId === this.playlist.id;
                        });
                        song.orderIndex = parseInt(playlistSongEntry.orderIndex);
                        console.log(song.orderIndex, song.title);
                        return song;
                    }.bind(this)).sort((one, two) => {
                        return one.orderIndex - two.orderIndex;
                    });

                this.songsNotInPlaylist = this.songSet.filter(function(song){
                        if (!this.playlist.songs.includes(song.id)) {
                            return song;
                        }
                    }.bind(this)).map((song, orderIndex) => {
                        const s = Vue.util.extend({}, song)
                        s.orderIndex = orderIndex + 1 + 10000;
                        return s;
                    });   
            }.bind(this);
            requestAnimationFrame(tick);
        },
        computed: {
            playlist: function() {
                if (this.$route.params.id === "create") {
                    return { id: null, title: null, description: null, songs: [] };
                } else {
                    const storeData = this.getPlaylistById()(this.$route.params.id);
                    return Vue.util.extend({}, storeData);
                }
            },
            ...mapGetters([
                'songSet',
                'catalogState',
                'playlistSongIndex',
            ]),
            songs: function() {
                return this.$store.state.catalog.songs;
            },
            dragOptions() {
                return {
                    ghostClass: "ghost"
                };
                
            }
        }
    }
</script>

<style scoped>
.droppable-song-list {
    min-height: 200px;
    background-color:#f8f9fa;
    border-radius: 5px;
}
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}
</style>
