<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Song</h2>
        </div>
        <div class="container">
            <form v-on:submit.prevent="submitSong">
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
 
    export default {
        name: "ManageSong",
        components: {
            
        },
        data: function() {
            return {
            }
        },
        methods: {
            ...mapGetters({
                getById: 'getSongById'
            }),
            submitSong(e) {
                // e.preventDefault();
                this.updateSong(this.song).then(() => {
                    this.goToSongsPage();
                });
            },
            goToSongsPage() {
                this.$router.push('/manager/songs');
            },
            ...mapActions([
                  'updateSong'
            ]),
            processFile(e) {
                const files = e.target.files || e.dataTransfer.files;
                if (!files.length) {
                    return;
                }
                const input = e.target;
                const reader = new FileReader();
                reader.onload = function() {
                    this.song.fileInput = reader.result;
                }.bind(this);
                reader.readAsDataURL(files[0]);
            }
        },
        computed: {
            song: function() {
                // const songFromStore = this.$store.state.catalog.songs[this.$route.params.id];
                const songFromStore = this.getById()(this.$route.params.id);
                return Vue.util.extend({}, songFromStore);
            }
        }
    }
</script>

<style scoped></style>
