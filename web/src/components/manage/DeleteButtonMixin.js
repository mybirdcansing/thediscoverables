import { mapActions } from 'vuex';
import Modal from './Modal.vue'
export default {
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
        submitDelete(handler) {
            this.deleteItem({
                id: this.$data.itemToDelete.id,
                handler: handler
            }).then(() => {
                this.closeDeleteItemModal();
                const goToPath = `/manager/${handler}s`;
                if (goToPath !== this.$router.currentRoute.path) {
                    this.$router.push(`/manager/${handler}s`);
                }
            }).catch((data) => {
                console.log(data.errorMessages);
            })
        },
        ...mapActions([
              'deleteItem'
        ]),         
    }
};