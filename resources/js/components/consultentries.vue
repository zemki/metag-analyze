<template>
    <svg width='200' height='600'></svg>

</template>

<script>

    export default {
        props: ['entries'],
        name: "consultentries",
        data() {
            return {
            loaddata: {}
            }
        },
        mounted() {
            this.fetchData();
            this.drawGraph();
        },
        methods: {
            fetchData() {
                this.loaddatadata = JSON.parse(this.entries);
            },
            drawGraph() {


            }
        }
    }
</script>

<style scoped>

    .line {
        fill: none;
        stroke: steelblue;
        stroke-width: 2px;
    }

    .axisRed line{
        stroke: red;
    }

    .axisRed path{
        stroke: red;
    }

    .axisRed text{
        fill: red;
    }

</style>