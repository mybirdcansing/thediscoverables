<template>
    <div>
        <h2>Manage Songs</h2>
  
        <div class="container">
            <button class="btn btn-sm btn-outline-secondary" type="button"><strong>+</strong> Add a Song</button>
            <div style="margin-top:10px">
                <div class="row border-top justify-content-between some-vpadding" v-for="song in allSongs" :key="song.id">
                    <div class="col"><span>{{ song.title }}</span></div>
                    <div class="col-sm-2.1">
                        <div class="row">
                            <div class="col-sm-1">
                                <router-link :to="`/manager/song/${ song.id }`"><img src="../../assets/images/edit-button.svg"/></router-link>
                            </div>
                            <div class="col-sm-1">
                                <a @click="confirmDeleteItem(song)"><img src="../../assets/images/delete-button.svg" /></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

      <!-- use the modal component, pass in the prop -->
      <modal v-if="showModal" @close="closeDeleteItemModal" @submit="deleteItem">
        <h3 slot="header">Confirm!</h3>
        <div slot="body">Are you sure you want to delete {{itemToDelete.title}}</div>
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
                itemToDelete: ''
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
            $(document).keydown((e) => {
                e = e || window.event;
                if (this.$data.showModal && e.key == "Escape") {
                    this.closeDeleteItemModal();
                }
            });
        }
    }
</script>

<style scoped>
    a {
        cursor: pointer;
    }
</style>
