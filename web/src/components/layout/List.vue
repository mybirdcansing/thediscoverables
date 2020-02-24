<template>
    <div>
        <p v-text="dropdownResult">item</p>
        <dropdown @update:option="optionUpdate">

        </dropdown>
    </div>
</template>

<script>
    import Dropdown from './Dropdown.vue'
    export default {
        name: "List",
        components: {
            Dropdown,
        },
        methods: {
            optionUpdate: function(value) {
                this.dropdownResult = value;
            }
        },
        data: function() {
            return {
                dropdownResult: ""
            }
        }
    }
</script>

<style scoped></style>
