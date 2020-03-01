<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Songs</h2>
        </div>
        <div class="container">
            <div class="more-vpadding">
                <button class="btn btn-sm btn-outline-secondary" 
                    @click="$router.push('/manager/song/create')"
                    type="button"><strong>+</strong> Add a Song</button>
            </div>
            <div class="container some-vpadding">
                <div class="row border-top some-vpadding" v-for="song in allSongs" :key="song.id">
                    <div class="col">
                        <router-link :to="`/manager/song/${ song.id }`"><span>{{ song.title }}</span></router-link>
                    </div>
                    <div class="col">
                        <!-- <router-link :to="`/manager/song/${ song.id }`"><img src="../../assets/images/edit-button.svg"/></router-link> -->
                        <!-- <a @click="confirmDeleteItem(song)"><img src="../../assets/images/delete-button.svg" /></a> -->
                        <button class="btn btn-sm btn-outline-warning" @click="confirmDeleteItem(song)">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <modal v-if="showModal" @close="closeDeleteItemModal" @submit="deleteItem">
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
            confirmDeleteItem(song) {
                this.$data.itemToDelete = song;
                this.$data.showModal = true;    
            },
            closeDeleteItemModal() {
                this.$data.showModal = false;
                this.$data.itemToDelete = null;
            },
            deleteItem() {
                this.deleteSong(this.$data.itemToDelete.id).then(() => {
                    this.closeDeleteItemModal();
                }).catch((data) => {
                    console.log(data.errorMessages);
                })
            },
            ...mapActions([
                  'deleteSong'
            ]),
        },
        computed: {
            ...mapGetters({
                allSongs: 'songSet',
            })
        },
        mounted() {
            this.$el.ownerDocument.addEventListener("keydown", function(e) {
                e = e || window.event;
                if (this.$data.showModal && e.key == "Escape") {
                    this.closeDeleteItemModal();
                }
            }.bind(this));
        }
    }
</script>

<style scoped>

</style>