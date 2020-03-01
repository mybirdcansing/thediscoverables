<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Song</h2>
            <router-link to="/manager/songs" class="text-secondary">&lt;&lt; Back to Songs</router-link>
        </div>
        <div class="container">
            <form v-on:submit.prevent="submitSong">
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
                    <label for="manageSongUpload">Upload an Mp3 file</label>
                    <input type="file" class="form-control-file" id="manageSongUpload" accept="audio/mp3" @change='processFile'>
                    <small class="form-text text-muted">Current file: {{song.filename}}</small>
                </div>                
                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                <button type="button" class="btn btn-sm btn-secondary" @click="goToSongsPage">Cancel</button>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import FormAlerts from './FormAlerts.vue';

    export default {
        name: "ManageSong",
        components: {
            FormAlerts
        },
        data: function() {
            return {
                errors: [],
                showSavingAlert: false
            }
        },
        methods: {
            ...mapGetters({
                getById: 'getSongById'
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
                    this.song.fileInput = reader.result;
                }.bind(this);
                this.song.filename = file.name;
                console.log(file.name);
                reader.readAsDataURL(file);
            },
            submitSong(e) {
                this.showSavingAlert = true;
                const saveAction = (this.song.id) ? this.updateSong : this.createSong;
                saveAction(this.song).then((response) => {
                    this.errors = [];
                    setTimeout(() => {
                        this.showSavingAlert = false;
                        this.$router.push('/manager/song/' + response.songId)
                    }, 1000);
                }).catch(function(data) {
                    this.showSavingAlert = false;
                    this.errors = Object.values(data.errorMessages).reverse();
                }.bind(this));
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
                    // const songFromStore = this.$store.state.catalog.songs[this.$route.params.id];
                    const songFromStore = this.getById()(this.$route.params.id);
                    return Vue.util.extend({}, songFromStore);
                }
            }
        }
    }
</script>

<style scoped>
</style>
