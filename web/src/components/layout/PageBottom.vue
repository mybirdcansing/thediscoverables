<template>
    <div class="pagebottom">{{copyright}}</div>
</template>
<script>
    export default {
        name: "PageBottom",
        data:function () {
            return {
                copyright: "Copyright © 2019 - " + new Date().getFullYear().toString() + " The Discoverables"
            }
        }
    }
</script>