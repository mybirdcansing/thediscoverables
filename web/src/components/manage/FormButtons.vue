<template>
    <table class="table table-borderless table-sm">
        <tbody>
            <tr>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="$emit('submit')">Save</button>  
                    <!-- @click="$emit('save')" -->
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="$emit('cancel')">Cancel</button>
                </td>
                <td>
                    <div class="float-right">
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
        props: [

        ],
        data: function() {
            return {

            }
        }
    }
</script>

<style scoped>

</style>