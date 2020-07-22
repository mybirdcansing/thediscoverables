<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Playlists</h2>
            <div class="container">   
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
        </div>

        <modal v-if="showDeleteModal" handler="playlist" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import DeleteButtonMixin from './layout/DeleteButtonMixin';
    export default {
        name: "ManagePlaylists",
        mixins: [DeleteButtonMixin],
        components: {          
        },
        data: function() {
            return {
            }
        },
        methods: {
            openItem(id) {
                 this.$router.push(`/manager/playlist/${ id }`);
            },
            ...mapActions([
                'deleteItem'
            ]),        
        },
        computed: {
            ...mapGetters({
                allPlaylists: 'playlistSet',
            })
        }  
    }
</script>

<style scoped></style>
