<template>
    <div class="alerts">
        <div class="alert alert-success" role="alert" v-if="showSavingAlert">{{savingMessage}}</div>
        <div v-if="errors.length">
            <div class="alert alert-warning" role="alert" v-for="error in errors" :key="error">{{ error }}</div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FormAlerts",
        props: [
            "errors",
            "savingMessage",
            "showSavingAlert"
        ],
        data: function() {
            return {

            }
        }
    }

</script>

<style scoped>

</style>
