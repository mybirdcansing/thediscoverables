<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Playlist</h2>
            <router-link to="/manager/playlists" class="text-secondary">&lt;&lt; Back to Playlists</router-link>
        </div>
        <div class="container">
            <form @submit.prevent="savePlaylist">
                <form-buttons 
                    @submit="savePlaylist"
                    @cancel="goToPlaylistsPage"
                    @delete="confirmDeleteItem(playlist)"
                />
                <form-alerts 
                    :errors="errors" 
                    :showSavingAlert="showSavingAlert" 
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
                    <textarea 
                        v-model="playlist.description" 
                        class="form-control" 
                        id="managePlaylistDescription"
                        placeholder="Enter description" rows="3"></textarea>
                </div>
                <div>
                    <div class="row">
                        <div class="col-6">
                            <h6>Songs in Playlist</h6>
                            <draggable class="list-group droppable-song-list"
                                v-bind="dragOptions"
                                :list="playlistSongs" 
                                group="songLists">
                                <div class="list-group-item clickable-text"
                                    v-for="song in playlistSongs"
                                    :key="song.id">
                                    {{ song.title }}
                                </div>
                            </draggable>
                        </div>
                        <div class="col-6">
                            <h6>Songs Not in Playlist</h6>
                            <draggable class="list-group droppable-song-list"
                                v-bind="dragOptions" 
                                :list="songsNotInPlaylist"
                                group="songLists">
                                <div class="list-group-item clickable-text"
                                    v-for="song in songsNotInPlaylist"
                                    :key="song.id">
                                    {{ song.title }}
                                </div>
                            </draggable>
                        </div>
                    </div>
                </div>
                <br/>
                <form-buttons @cancel="goToPlaylistsPage" @delete="confirmDeleteItem(playlist)" @submit="savePlaylist" />
            </form>
        </div>  
        <modal v-if="showDeleteModal" @close="closeDeleteItemModal" @submit="submitDelete" handler="playlist">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FormButtons from './layout/FormButtons.vue';
    import FormAlerts from './layout/FormAlerts.vue';
    import DeleteButtonMixin from './layout/DeleteButtonMixin';
    import StatusEnum from '../../store/StatusEnum';
    import draggable from "vuedraggable";
    export default {
        name: "ManagePlaylist",
        mixins: [DeleteButtonMixin],
        display: "Manage Playlist",
        components: {
            FormAlerts,
            FormButtons,
            draggable,
        },
        data: function() {
            return {
                errors: [],
                showSavingAlert: false,
                playlistSongs: [],
                songsNotInPlaylist: []
            }
        },
        methods: {
            savePlaylist: async function(e) {
                this.showSavingAlert = true;
                this.playlist.songs = this.playlistSongs.map((song, index) => {
                    return song.id;
                });
                try {
                    const payload = { data: this.playlist, handler: 'playlist' };
                    if (this.playlist.id) {
                        await this.updateItem(payload);
                    } else {
                        await this.createItem(payload);
                    }
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                        if (!this.playlist.id) {
                            this.goToPlaylistsPage();
                        }
                    }, 900);
                } catch(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }
            },
            goToPlaylistsPage() {
                this.$router.push('/manager/playlists');
            },
            ...mapActions([
                'updateItem',
                'createItem',
                'deleteItem'
            ])
        },
        created() {
            const setSongLists = () => {
                this.playlistSongs = this.playlist.songs.map((songId) => {
                    return Vue.util.extend({}, this.songs[songId]);
                });

                this.songsNotInPlaylist = this.songSet.filter((song) => {
                    if (!this.playlist.songs.includes(song.id)) {
                        return Vue.util.extend({}, song);                
                    }
                }); 
            };
            if (this.catalogState === StatusEnum.LOADED) {
                setSongLists();
            } else {
                this.$watch('catalogState', (newState, oldState) => {
                    if (newState === StatusEnum.LOADED) {
                        setSongLists();
                    }
                }); 
            }
        },
        computed: {
            playlist: function() {
                if (this.$route.params.id === "create") {
                    return { id: null, title: null, description: null, songs: [] };
                } else {
                    return Vue.util.extend({}, this.getPlaylistById(this.$route.params.id));
                }
            },
            ...mapGetters([
                'songSet',
                'catalogState',
                'getPlaylistById',
            ]),
            songs: function() {
                return this.$store.state.catalog.songs;
            },
            dragOptions() {
                return { ghostClass: "ghost" };   
            }
        }
    }
</script>

<style scoped>
.droppable-song-list {
    min-height: 100px;
    background-color:#ebf2f9;
    border-radius: 5px;
    padding-bottom: 2px;
}
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.list-group-item:hover {
    background: #f9fafd;
}
</style>
