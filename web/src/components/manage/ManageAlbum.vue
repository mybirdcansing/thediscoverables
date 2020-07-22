<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Album</h2>
            <router-link to="/manager/albums" class="text-secondary">&lt;&lt; Back to Albums</router-link>
        </div>
        <div class="container">
            <form @submit.prevent="submitAlbum">
                <form-buttons @cancel="goToAlbumsPage" @delete="confirmDeleteItem(album)" @submit="submitAlbum" />
                <form-alerts :errors="errors" :showSavingAlert="showSavingAlert" savingMessage="Saving album..." />
                <div class="form-group">
                    <div class="col-sm-6 nopadding">
                        <label for="manageAlbumTitle">Title</label>
                        <input v-model="album.title" class="form-control" type="text" id="manageAlbumTitle" placeholder="Enter title">
                    </div>
                </div>
                <div class="form-group">
                    <label for="manageAlbumDescription">Description</label>
                    <textarea v-model="album.description" class="form-control" id="manageAlbumDescription" placeholder="Enter description" rows="3"></textarea>
                </div>
                <div class="form-group">                      
                    <div class="row">
                        <div class="col-sm-5 some-vpadding">
                            <h6>Playlist:</h6>
                            <div class="form-check playlist-list">
                                <div v-for="item in playlistSet" :key="item.id">
                                    <input 
                                        v-model="album.playlist"
                                        :id="item.id"
                                        :value="item.id"
                                        :checked="album.playlist === item.id" 
                                        @change="$data.songs = getPlaylistSongs(item)" 
                                        class="form-check-input" type="radio" name="playlistRadios">
                                    <label class="form-check-label"  v-bind:for="item.id">
                                        {{item.title}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 some-vpadding">
                            <h6>Songs:</h6>
                            <div class="song-list">
                                <div v-for="song in songs" :key="song.id" v-text="song.title"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="albumReleaseDate">Release Date</label>
                    <input type="date" id="albumReleaseDate" v-model="album.publishDate" name="birthday">
                </div>
                <div class="form-group">
                    <label for="manageArtUpload">Upload Cover Art</label>
                    <input id="manageArtUpload" @change='processArtworkFile' type="file" class="form-control-file" accept="image/*">
                    <div class="container more-vpadding">
                        <div class="row">
                            <div class="col-sm-8">
                                <small v-if="album.artworkFilename" class="form-text text-muted">File:</small>
                                <img class="albumArtwork" ref="artworkImg" id="albumArtworkImage" />
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <form-buttons @cancel="goToAlbumsPage" @delete="confirmDeleteItem(album)" @submit="submitAlbum" />
            </form>
        </div>
        <modal v-if="showDeleteModal" handler="album" @close="closeDeleteItemModal" @submit="submitDelete">
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

    export default {
        name: "ManageAlbum",
        mixins: [
            DeleteButtonMixin
        ],
        components: {
            FormAlerts,
            FormButtons
        },        
        data: function() {
            return {
                errors: [],
                showSavingAlert: false,
                songs: []
            }
        },
        methods: {
            setArtworkSrc(path) {
                this.$refs.artworkImg.src = path;
            },
            processArtworkFile(e) {
                const files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    return;
                }
                const file = files[0];
                const input = e.target;
                const reader = new FileReader();
                reader.onload = () => {
                    const fileAsText = reader.result;
                    this.setArtworkSrc(fileAsText);
                    this.album.artworkFilename = file.name;
                    this.album.fileInput = fileAsText;
                };
                reader.readAsDataURL(file);
            },
            submitAlbum: async function() {
                this.$data.showSavingAlert = true;
                try{
                    const payload = { data: this.album, handler: 'album' };
                    if (this.album.id) {
                        await this.updateItem(payload);
                    } else {
                        await this.createItem(payload);
                    }
                    this.errors = [];
                    setTimeout(() => {
                        this.$data.showSavingAlert = false;
                        if (!this.album.id) {
                            this.goToAlbumsPage();
                        }
                    }, 900);
                } catch (data) {
                    this.$data.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }
            },            
            goToAlbumsPage() {
                this.$router.push('/manager/albums');
            },
            ...mapActions([
                'updateItem',
                'createItem',
                'deleteItem'
            ])
        },
        computed: {
            album: function() {
                if (this.$route.params.id === "create") {
                    return { 
                        id: null,
                        title: null,
                        description: null,
                        playlist: null,
                        artworkFilename: null,
                        publishDate: null,
                        fileInput: null                     
                    };
                } else { 
                    return Vue.util.extend({}, this.getAlbumById(this.$route.params.id)); 
                }
            },
            ...mapGetters([
                'playlistSet',
                'catalogState',
                'getPlaylistSongs',
                'getAlbumById',
                'getPlaylistById',
                'catalogState',
            ]),
        },
        mounted() {
            const postLoad = () => {
                this.setArtworkSrc('/artwork/large~' + this.album.artworkFilename);
                if (this.album.playlist) {
                    this.$data.songs = this.getPlaylistSongs(this.getPlaylistById(this.album.playlist));
                }
            };

            if (this.$route.params.id === 'create') {
                this.setArtworkSrc('data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
            } else if (this.catalogState === StatusEnum.LOADED) {
                postLoad();
            } else {
                this.$watch('catalogState', (newState, oldState) => {
                    if (newState === StatusEnum.LOADED) {
                        postLoad();
                    }
                }); 
            }
            
        },
    }
</script>

<style scoped>
    .albumArtwork {
        width:  100%;
        border: 1px solid;
        border-color: #aaa;
        padding: 10px;
        -moz-box-shadow: 2px 2px 4px 0px #006773;
        -webkit-box-shadow:  2px 2px 4px 0px #006773;
        box-shadow: 2px 2px 4px 0px #006773;        
    }
    .nopadding {
        padding-left: 0 !important;
        margin-left: 0 !important;
    }
    .top-buffer { 
        margin-top:20px !important;
    }
    .song-list {
        background-color:#ebf2f9;
        border-radius: 5px;
        margin-top: 4px;
        padding-top: 2px;
        padding-left: 12px;        
        padding-bottom: 2px;
    }
    .playlist-list {
        background-color:#ebf2f9;
        border-radius: 5px;
        margin-top: 4px;
        padding-top: 2px;
        padding-left: 28px;        
        padding-bottom: 2px;
    }    
</style>
