<template>
    <table class="table table-borderless table-sm">
        <tbody>
            <tr>
                <td>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="$emit('cancel')">Cancel</button>
                </td>
                <td>
                    <div class="float-right" v-if="!hideDelete">
                    <button type="button" class="btn-xs btn-outline-secondary" @click="$emit('delete')">Delete</button>
                </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
    export default {
        name: "FormButtons",
        computed: {
            hideDelete() {
                return this.$route.params.id === 'create';
            }
        } 
    }
</script>

<style scoped>

</style>