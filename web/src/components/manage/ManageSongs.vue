<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Songs</h2>
            <div class="container">
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
        </div>

        <modal v-if="showDeleteModal" handler="song" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import DeleteButtonMixin from './DeleteButtonMixin';
    export default {
        name: "ManageSongs",
        mixins: [DeleteButtonMixin],
        components: {
        },
        data: function() {
            return {
            }
        },
        methods: {
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