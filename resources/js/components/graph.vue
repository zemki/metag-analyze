<template>
    <div :id="'chart'+graphid"></div>
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
                self.setChartTheme();
                self.drawChart();
                self.$forceUpdate();
            }, 30);

        },
        data() {
            return {
                realdata: [],
                graphid: Math.floor((Math.random() * 100) + 1)
            }
        },
        methods: {
            setChartTheme: function(){
                Highcharts.theme = {
                    colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572',
                        '#FF9655', '#FFF263', '#6AF9C4'],
                    title: {
                        style: {
                            color: '#000',
                            fontSize: '25px',
                            textTransform: 'uppercase'
                        }
                    },
                    subtitle: {
                        style: {
                            color: '#666666',
                            font: 'bold 12px "Trebuchet MS", Verdana, sans-serif'
                        }
                    },
                    legend: {
                        itemHoverStyle:{
                            color: 'gray'
                        }
                    }
                };

                // Apply the theme
                Highcharts.setOptions(Highcharts.theme);



            },
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
                        var split = data['start'].split(/[^0-9]/)
                        console.log(split)

                        let start = Date.UTC(...split);
                        var split = data['end'].split(/[^0-9]/)
                        console.log(split)
                        let end = Date.UTC(...split);



                            rl.data.push({start: start, end: end, name: (data['value'] != "") ? data['value'] : "" })
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
                    plotOptions: {
                        column: {
                            grouping: false,
                            shadow: false
                        }
                    },
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
                        categories: self.availabledata,
                        breaks: [{
                            breakSize: 0.1,
                            from: 0,
                            to: 0
                        }]
                    },
                    chart: {
                        zoomType: 'x',
                        spacingRight: 10

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
    [id^="chart"]{
        width: 100%;
        height: 100%;
        overflow: visible !important;
        margin: 30px auto;

    }


    .highcharts-root {
        font-family: 'Courier New', monospace;

    }
</style>
