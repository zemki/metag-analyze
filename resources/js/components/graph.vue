<template>
    <div :id="'chart'+graphid" style="width:100%; height:100%;"></div>
</template>

<script>
    import Highcharts from 'highcharts'


    export default {
        props: ["info", "title", "availabledata"],
        name: "graph",
        created() {
            let self = this;
            this.preparedata();
            setTimeout(function () {

                self.drawChart();

            }, 30);

        },
        data() {
            return {
                realdata: [],
                graphid: Math.floor((Math.random() * 100) + 1)
            }
        },
        methods: {
            preparedata: function () {
                let self = this;
                if(this.title == "" || !this.title)this.title = "Media";
                _.forEach(this.availabledata, function (o) {
                    self.realdata.push({name: o, data: []});
                });

                _.forEach(this.info, function (data,key) {

                if(key == 'available' || key=="title")return;

                    _.forEach(self.realdata, function (rl) {
                    if(data['value'] == rl.name || (_.isArray(data['value']) && data['value'].includes(rl.name))){
                            rl.data.push({start: Date.parse(data['start']), end: Date.parse(data['end']), name: (data['value'] != "") ? data['value'] : "" })
                        }
                    });
                });

                _.forEach(self.realdata, function (d,i) {
                    _.forEach(d.data, function (data) {
                        data.y = i;
                    })
                })

            },
            drawChart: function () {
                let self = this;
                console.log(this.info);
                Highcharts.ganttChart('chart'+self.graphid, {

                    title: {
                        text: self.title
                    },
                    subtitle: {
                        text: document.ontouchstart === undefined ?
                            'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                    },
                    xAxis: {
                        tickInterval: 1000 * 60 * 60 * 24, // Day,
                    },
                    yAxis: {
                        categories: self.availabledata
                    },
                    chart: {
                        zoomType: 'x'
                    },
                    navigator: {
                        enabled: true,
                        liveRedraw: true,
                        series: {
                            type: 'gantt',
                        }
                    },
                    scrollbar: {
                        enabled: true
                    },
                    rangeSelector: {
                        enabled: true,
                        selected: 0
                    },
                    series: self.realdata

                });
            }
        }
    }
</script>

<style scoped>

</style>
