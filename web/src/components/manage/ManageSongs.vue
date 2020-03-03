<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Songs</h2>
            <div class="more-vpadding">
                <button type="button" class="btn btn-sm btn-outline-secondary" 
                    @click="$router.push('/manager/song/create')"><strong>+</strong> Add a Song</button>
            </div>
            <table class="table table-hover table-sm">
                <tbody>
                    <tr v-for="song in allSongs" :key="song.id" class='clickable-text'>
                        <td @click.self="openItem(song.id)">
                            <span class="align-middle" @click.self="openItem(song.id)">{{ song.title }}</span>
                            <div class="float-right">
                                <button class="btn-xs btn-outline-secondary" 
                                    @click.self.prevent="confirmDeleteItem(song)">Delete</button>
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
        name: "ManageSongs",
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
            submitDelete(e) {
                const options = {
                    id: this.$data.itemToDelete.id,
                    handler: 'song'
                };
                this.deleteItem(options).then(() => {
                    this.closeDeleteItemModal();
                }).catch((data) => {
                    console.log(data.errorMessages);
                })
            },
            ...mapActions([
                'deleteItem'
            ]),
            openItem(id) {
                 this.$router.push(`/manager/song/${ id }`);
            }
        },
        computed: {
            ...mapGetters({
                allSongs: 'songSet',
            })
        }
    }
</script>

<style scoped>

</style>