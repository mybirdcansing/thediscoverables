<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Playlists</h2>
            <div class="more-vpadding">
                <button type="button" class="btn btn-sm btn-outline-secondary" 
                    @click="$router.push('/manager/playlist/create')"><strong>+</strong> Add a Playlist</button>
            </div>
            <table class="table table-hover table-sm">
                <tbody>
                    <tr v-for="playlist in allPlaylists" :key="playlist.id" class='clickable-text'>
                        <td @click.self="openItem(playlist.id)">
                            <span class="align-middle" @click.self="openItem(playlist.id)">{{ playlist.title }}</span>
                            <div class="float-right">
                                <button class="btn-xs btn-outline-secondary" 
                                    @click.self.prevent="confirmDeleteItem(playlist)">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <modal v-if="showModal" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import Modal from './Modal.vue'

    export default {
        name: "ManagePlaylists",
        components: {
            Modal            
        },
        data: function() {
            return {
                showModal: false,
                itemToDelete: null
            }
        },
        methods: {
            confirmDeleteItem(item) {
                this.$data.itemToDelete = item;
                this.$data.showModal = true;    
            },
            closeDeleteItemModal() {
                this.$data.showModal = false;
                this.$data.itemToDelete = null;
            },
            submitDelete() {
                this.deleteItem({
                    id: this.$data.itemToDelete.id,
                    handler: 'playlist'
                }).then(() => {
                    this.closeDeleteItemModal();
                }).catch((data) => {
                    console.log(data.errorMessages);
                })
            },
            ...mapActions([
                  'deleteItem'
            ]),
            openItem(id) {
                 this.$router.push(`/manager/playlist/${ id }`);
            }          
        },
        computed: {
            ...mapGetters({
                allPlaylists: 'playlistSet',
            })
        }
    }
</script>

<style scoped></style>
