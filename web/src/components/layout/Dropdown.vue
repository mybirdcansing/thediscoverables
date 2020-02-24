<template>
    <select name="dropdown" id="dropdown" v-model="selectedOption" @change="optionChanged ">
        <option value="null" disabled>Select an option</option>
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
        <option value="3">Option 3</option>
    </select>
</template>

<script>
    export default {
        name: "Dropdown",
        methods: {
            optionChanged: function(value) {
                this.$emit('update:option', this.selectedOption);
            }
        },
        data: function() {
            return {
                    selectedOption: null
            }
        }
    }

</script>

<style scoped></style>
