<template>
    <div>
        <div class="container some-vpadding">
            <h2>Manage Albums</h2>
            <div class="container">   
                <div class="more-vpadding">
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                        @click="$router.push('/manager/album/create')"><strong>+</strong> Add an Album</button>
                </div>
                <table class="table table-hover table-sm">
                    <tbody>
                        <tr v-for="item in allAlbums" :key="item.id" class='clickable-text'>
                            <td @click.self="openItem(item.id)">
                                <span class="align-middle" @click.self="openItem(item.id)">{{ item.title }}</span>
                                <div class="float-right">
                                    <button class="btn-xs btn-outline-secondary" 
                                        @click.self.prevent="confirmDeleteItem(item)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <modal v-if="showDeleteModal" handler="album" @close="closeDeleteItemModal" @submit="submitDelete">
            <h3 slot="header">Confirm!</h3>
            <div slot="body">Are you sure you want to delete <strong>{{itemToDelete.title}}</strong>?</div>
        </modal>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import DeleteButtonMixin from './layout/DeleteButtonMixin';
    export default {
        name: "ManageAlbums",
        mixins: [DeleteButtonMixin],
        components: {
        },
        methods: {
            openItem(id) {
                 this.$router.push(`/manager/album/${ id }`);
            }          
        },
        computed: {
            ...mapGetters({
                allAlbums: 'albumSet',
            })
        }
    }
</script>

<style scoped></style>
