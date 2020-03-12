<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Song</h2>
            <router-link to="/manager/songs" class="text-secondary">&lt;&lt; Back to Songs</router-link>
        </div>
        <div class="container">
            <form v-on:submit.prevent="submitSong">
                <form-buttons @cancel="goToSongsPage" @delete="confirmDeleteItem(song)" @submit="submitSong" />          
                <form-alerts v-bind:errors="errors" v-bind:showSavingAlert="showSavingAlert" savingMessage="Saving song..." />
                <div class="form-group">
                    <label for="manageSongTitle">Title</label>
                    <input v-model="song.title" class="form-control" type="text" id="manageSongTitle" placeholder="Enter title">
                </div>
                <div class="form-group">
                    <label for="manageSongDescription">Description</label>
                    <textarea v-model="song.description" class="form-control" id="manageSongDescription" placeholder="Enter description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <div class="container">
                        <div class="row">
                            <div style="col col-md-8">
                                <label for="manageSongUpload">Upload an Mp3 file</label>
                                <input type="file" class="form-control-file" id="manageSongUpload" accept="audio/mp3" @change='processFile'>
                                <small class="form-text text-muted more-vpadding">Filename: {{song.filename}}</small>
                            </div>
                            <div style="col col-md-4">
                                <audio controls ref="player" :key="audioSrc">
                                    <source v-bind:src="audioSrc" type="audio/mpeg">
                                </audio>
                            </div>
                         </div>
                    </div>
                </div>
                <br/>
                <form-buttons @cancel="goToSongsPage" @delete="confirmDeleteItem(song)" @submit="submitSong" />
            </form>
        </div>
        <modal v-if="showDeleteModal" handler="song" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FormAlerts from './FormAlerts.vue';
    import FormButtons from './FormButtons.vue';
    import DeleteButtonMixin from './DeleteButtonMixin';
    import { StatusEnum } from '../../store/StatusEnum';
    export default {
        name: "ManageSong",
        mixins: [DeleteButtonMixin],
        components: {
            FormAlerts,
            FormButtons,
        },
        data: function() {
            return {
                errors: [],
                showSavingAlert: false,
                audioSrc: "",
            }
        },
        methods: {
            processFile(e) {
                const files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    return;
                }
                const file = files[0];
                const input = e.target;
                const reader = new FileReader();
                reader.onload = function() {
                    this.$data.audioSrc = this.song.fileInput = reader.result;
                }.bind(this);
                this.song.filename = file.name.replace(/ |'/g, "_");;
                reader.readAsDataURL(file);
            },
            submitSong: async function() {
                this.showSavingAlert = true;
                try {
                    if (this.song.id) {
                        const response = await this.updateSong(this.song);
                    } else {
                        const response = await this.createSong(this.song);
                    }
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                    }, 1000);
                } catch(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }
            },
            goToSongsPage() {
                this.$router.push('/manager/songs');
            },
            ...mapActions([
                'updateSong',
                'createSong'
            ]),
        },
        computed: {
            song: function() {
                if (this.$route.params.id === "create") {
                    return { id: null, title: null, filename: null, description: null, fileInput: null };
                } else {
                    return Vue.util.extend({}, this.getSongById(this.$route.params.id));
                }
            },
            ...mapGetters([
                'catalogState',
                'getSongById'
                ]),      
        },
        mounted() {
            if (this.catalogState === StatusEnum.LOADED) {
                this.$data.audioSrc = "/audio/" + encodeURI(this.song.filename);
            } else {
                this.$watch('catalogState', (newState, oldState) => {
                    if (newState === StatusEnum.LOADED) {
                        this.$data.audioSrc = "/audio/" + encodeURI(this.song.filename);
                    }
                }); 
            }
            this.$watch('audioSrc', () => {
                this.$refs.player.load();
            });
        }
    }
</script>

<style scoped>
</style>
