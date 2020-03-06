<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Album</h2>
            <router-link to="/manager/albums" class="text-secondary">&lt;&lt; Back to Albums</router-link>
        </div>
        <div class="container">
            <form v-on:submit.prevent="submitAlbum">
                <form-buttons @cancel="goToAlbumsPage" @delete="confirmDeleteItem(album)" @submit="submitAlbum" />
                <form-alerts v-bind:errors="errors" v-bind:showSavingAlert="showSavingAlert" savingMessage="Saving album..." />
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
                        <div class="col-sm-5">Playlist:</div>
                        <div class="col-sm-6 nopadding some-vpadding">Songs:</div>
                    </div>                        
                    <div class="row ">
                        <div class="col-sm-5">
                            <div class="form-check">
                                <div v-for="item in allPlaylists" :key="item.id">
                                    <input 
                                        v-bind:id="item.id"
                                        v-bind:value="item.id"
                                        v-on:change="setPlaylistSongs(item)" 
                                        v-bind:checked="album.playlist === item.id" 
                                        v-model="album.playlist"
                                        class="form-check-input" type="radio" name="playlistRadios">
                                    <!-- checked -->
                                    <label class="form-check-label"  v-bind:for="item.id">
                                        {{item.title}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class='row' v-for="song in songs" :key="song.id" v-text="song.title"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="albumReleaseDate">Release Date</label>
                    <input type="date" id="albumReleaseDate" v-model="album.publishDate" name="birthday">
                </div>
                <div class="form-group">
                    <label for="manageArtUpload">Upload Cover Art</label>
                    <input type="file" class="form-control-file" id="manageArtUpload" accept="image/*" @change='processFile'>
                    <div class="container more-vpadding">
                        <div class="row">
                            <div class="col-sm-8">
                                <small v-if="album.artworkFilename" class="form-text text-muted">File:</small>
                                <img class="albumArtwork" id="newAlbumArtworkImage" v-bind:src="'../../../artwork/' + album.artworkFilename" />
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <form-buttons @cancel="goToAlbumsPage" @delete="confirmDeleteItem(album)" @submit="submitAlbum" />
            </form>
        </div>
        <modal v-if="showModal" handler="album" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>        
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FormButtons from './FormButtons.vue';
    import FormAlerts from './FormAlerts.vue';
    import DeleteButtonMixin from './DeleteButtonMixin';
    import { StatusEnum } from '../../store/StatusEnum';
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
            ...mapGetters({
                getById: 'getAlbumById',
                getPlaylistById: 'getPlaylistById'
            }),
            processFile(e) {
                const files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    return;
                }
                const file = files[0];
                const input = e.target;
                const reader = new FileReader();
                reader.onload = function() {
                    const fileAsText = reader.result;
                    document.getElementById('newAlbumArtworkImage').src = fileAsText;
                    this.album.artworkFilename = file.name;
                    this.album.fileInput = fileAsText;
                }.bind(this);
                reader.readAsDataURL(file);
            },
            submitAlbum() {
                this.showSavingAlert = true;
                const saveAction = (this.album.id) ? this.updateItem : this.createItem;
                saveAction({
                    data: this.album,
                    handler: 'album'
                }).then((response) => {
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                    }, 1000);
                }).catch(function(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }.bind(this));
            },
            goToAlbumsPage() {
                this.$router.push('/manager/albums');
            },
            ...mapActions([
                'updateItem',
                'createItem'
            ]),
            setPlaylistSongs(playlist) {
                this.$data.songs = this.getPlaylistSongs(playlist);
            }
        },
        computed: {
            album: function() {
                if (this.$route.params.id === "create") {
                    return { id: null, title: null, description: null, playlist: null, artworkFilename: null, publishDate: null, fileInput: null };
                } else {
                    let data = Vue.util.extend({}, this.getById()(this.$route.params.id)); //this.$store.state.catalog.albums[this.$route.params.id];
                    if (data.publishDate) {
                        data.publishDate = data.publishDate.split(' ')[0];
                    }
                    return data; 
                }
            },
            ...mapGetters([
                'catalogState',
            ]),
            ...mapGetters({
                allPlaylists: 'playlistSet',
                allSongs: 'songSet',
                catalogState: 'catalogState',
                getPlaylistSongs: 'getPlaylistSongs',
            }),
        },
        mounted() {
            if (this.$route.params.id === "create") {
                document.getElementById('newAlbumArtworkImage').src = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
            }
            
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
                if (this.album.playlist) {
                    this.$data.songs = this.getPlaylistSongs(this.getPlaylistById()(this.album.playlist));
                }
            }.bind(this);
            requestAnimationFrame(tick);
        }
    }
</script>

<style scoped>
    .albumArtwork {
        width:  100%;
        border: 1px solid;
        border-color: #aaa;
        padding: 10px;
        box-shadow: 5px 10px #bbb;
    }
    .nopadding {
        padding-left: 0 !important;
        margin-left: 0 !important;
    }
    .top-buffer { margin-top:20px !important; }
</style>
