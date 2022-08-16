<template>
  <div id="chart"></div>
</template>

<script>
import Highcharts from "highcharts";
import HighchartsMore from "highcharts/highcharts-more";
import { mapActions, mapState } from "vuex";
HighchartsMore(Highcharts);
export default {
  name: "medtaggraph",
  props: ["media", "inputs", "entries", "yColumn"],
  data() {
    return {
      categories: [],
      yAxisCategoryNames: [],
      chart: null,
      allSeries: [],
      interval: 30,
      begin: 0,
      end: 0,
      extremes: null,
    };
  },
  computed: {
    ...mapState({
      yAxisAttribute: (state) => state.graph.yAxisAttribute,
      formatterStatus: (state) => state.graph.formatter,
    }),
  },
  watch: {
    formatterStatus: function (newVal, oldVal) {
      let self = this;
      setTimeout(() => {
        self.updateChart();
      }, 50);
    },
    yAxisAttribute: function (newVal, oldVal) {
      let self = this;
      setTimeout(() => {
        self.updateChart();
      }, 50);
    },
    begin: function (newVal, oldVal) {
      let self = this;
      setTimeout(() => {
        if (self.begin && self.end) {
          self.chart.yAxis[0].setExtremes(
            self.begin.getTime(),
            self.end.getTime()
          );
        }
      }, 100);
    },
    end: function (newVal, oldVal) {
      let self = this;
      setTimeout(() => {
        if (self.begin && self.end) {
          self.chart.yAxis[0].setExtremes(
            self.begin.getTime(),
            self.end.getTime()
          );
        }
      }, 100);
    },
  },
  created() {
    this.categories = this.getYAxisCategories();
    this.yAxisCategoryNames = this.categories.map((category) => category.name);
    this.yAxisCategoryIds = this.categories.map((category) => category.id);

    let self = this;
    setTimeout(() => {
      self.initiateChart();
      self.updateChart();
    }, 500);
  },
  methods: {
    ...mapActions({ download: "downloadChart" }),
    switchInputs: function () {},
    debouncedUpdateChart: function () {
      _.debounce(() => this.updateChart(), 100);
    },
    coloredAttribute: function () {
      return { media: "inputs", inputs: "media" }[this.yAxisAttribute];
    },
    getYAxisCategories: function () {
      return { media: this.media, inputs: this.inputs }[this.yAxisAttribute];
    },
    getColoredCategories: function () {
      return { media: this.media, inputs: this.inputs }[
        this.coloredAttribute()
      ];
    },
    initiateChart: function () {
      Highcharts.setOptions({
        global: {
          useUTC: false,
        },
      });

      let self = this;
      // initialize chart on the element

      let container = document.getElementById("container");
      this.chart = Highcharts.chart({
        chart: {
          type: "columnrange",
          inverted: true,
          renderTo: container,
          zoomType: "y",
          height: (9 / 16) * 100 + "%",
          animation: false,
        },
        credits: {
          enabled: false,
        },
        title: null,
        xAxis: {
          categories: self.categories,
          max: self.categories && self.categories.length - 1,
          labels: {
            step: 1,
          },
        },
        yAxis: {
          type: "datetime",
          title: {
            text: null,
          },
          opposite: true,
          tickInterval: 24 * 60 * 60 * 1000,
          minorTickInterval: 6 * 60 * 60 * 1000,
          minorTickWidth: 1,
          minorTickLength: 10,
          minorGridLineWidth: 0,
          lineWidth: 1,
          startOnTick: false,
          endOnTick: false,
          labels: {
            formatter: null,
          },
        },
        plotOptions: {
          columnrange: {
            grouping: false,
          },
          series: {
            states: {
              inactive: {
                opacity: 1,
              },
              hover: {
                opacity: 1,
              },
            },
          },
        },
        tooltip: {
          pointFormatter: function () {
            let low = Highcharts.dateFormat("%H:%M", this.low);
            let high = Highcharts.dateFormat("%H:%M", this.high);

            let coloredCategories = _.castArray(
              this.meTagEntry[self.coloredAttribute()]
            )
              .map((category) => category.name)
              .join("<br>");
            return `<b>${low} - ${high}</b><br>${coloredCategories}`;
          },
        },
        series: [],
        exporting: {
          enabled: false,
          sourceWidth: 1200,
          sourceHeight: 400,
          scale: 2,
          chartOptions: {
            title: null,
          },
        },
        legend: {
          itemWidth: 275,
        },
      });
    },
    updateChart: function () {
      this.categories = this.getYAxisCategories();
      this.yAxisCategoryNames = this.categories.map(
        (category) => category.name
      );

      this.yAxisCategoryIds = this.categories.map((category) => category.id);
      this.allSeries = this.getColoredCategories().map((category) => {
        let series = {
          id: category.id + "",
          color: "#" + category.color,
          borderWidth: 0,
          name: category.name,
          data: [],
        };
        // for every entry that has a matching colored category, add the entry to the series data
        for (let entry of this.entries) {
          let coloredAttributeAsArray = _.castArray(
            entry[this.coloredAttribute()]
          );

          let yAxisAttributeAsArray = _.castArray(entry[this.yAxisAttribute]);

          // check if the primary (first) colored category matches
          if (
            coloredAttributeAsArray[0] &&
            coloredAttributeAsArray[0].name === category.name
          ) {
            console.log("inside");
            if (!_.isEmpty(entry[this.yAxisAttribute])) {
              // the index of the y axis category
              let x = this.yAxisCategoryNames.indexOf(
                yAxisAttributeAsArray[0].name
              );

              if (x !== -1) {
                let point = {
                  x, // timestamp of the start of the bar
                  low: entry.begin * 1000, // timestamp of the end of the bar
                  high: entry.end * 1000,
                  meTagEntry: entry,
                };
                if (point.high - point.low < this.interval * 60 * 1000) {
                  point.high = point.low + this.interval * 60 * 1000;
                }
                // if there are also other categories, add them as colors
                if (coloredAttributeAsArray.length > 1) {
                  let stops = [];

                  for (
                    let i = 0, length = coloredAttributeAsArray.length;
                    i < length;
                    i++
                  ) {
                    stops.push([
                      i * (1 / length),
                      "#" + coloredAttributeAsArray[i].color,
                    ]);
                    stops.push([
                      (i + 1) * (1 / length),
                      "#" + coloredAttributeAsArray[i].color,
                    ]);
                  }
                  point.color = {
                    linearGradient: { x1: 1, y1: 0.5, x2: 0, y2: 0.5 },
                    stops,
                  };
                }
                series.data.push(point);
              }
            }
          }
        }
        series.data.sort((a, b) => a.x - b.x);
        return series;
      });

      // set categories on the y axis (without redrawing)
      this.chart.xAxis[0].setCategories(this.yAxisCategoryNames, false);
      this.chart.xAxis[0].update(
        { max: this.yAxisCategoryNames.length - 1 },
        false
      );

      // remove all series from the chart (without redrawing)
      while (this.chart.series.length > 0) {
        this.chart.series[0].remove(false);
      }
      // add all the new series (without redrawing)
      for (let series of this.allSeries) {
        this.chart.addSeries(series, false);
      }

      // redraw the chart
      this.chart.redraw();
      this.extremes = this.chart.yAxis[0].getExtremes();

      this.begin = new Date(this.extremes.min);
      this.end = new Date(this.extremes.max);

      // draw plot bands for weekends
      let date = new Date(this.begin.getTime());
      date.setHours(0);
      date.setMinutes(0);
      date.setSeconds(0);
      date.setMilliseconds(0);
      // begin at last saturday

      while (date.getDay() !== 6) {
        date.setTime(date.getTime() - 24 * 60 * 60 * 1000);
      }

      // at a plot band for all weekends until the end is reached
      while (date < this.end) {
        this.chart.yAxis[0].addPlotBand({
          color: "#eee",
          from: date.getTime(),
          to: date.getTime() + 2 * 24 * 60 * 60 * 1000,
        });
        date.setTime(date.getTime() + 7 * 24 * 60 * 60 * 1000);
      }

      // if begin and end are not set, initialize values
      if (!this.begin && !isNaN(this.end.getTime())) {
        this.begin.setMinutes(
          Math.ceil(this.begin.getMinutes() / this.interval) * this.interval
        );
      }
      if (!this.end && !isNaN(this.end.getTime())) {
        this.end.setMinutes(
          Math.ceil(this.end.getMinutes() / this.interval) * this.interval
        );
      }
    },
    download: function (type) {
      let container = document.getElementById("container");
      let self = this;
      this.chart.exportChart(
        {
          sourceWidth: this.chart.offsetWidth,
          sourceHeight: this.chart.offsetHeight,
          type: type,
        },
        {
          chart: {
            type: "columnrange",
            inverted: true,
            zoomType: "y",
            height: (9 / 16) * 100 + "%",
            animation: false,
          },

          credits: {
            enabled: false,
          },
          title: null,
          xAxis: {
            labels: {
              step: 10,
            },
          },
          yAxis: {
            type: "datetime",
            title: {
              text: null,
            },
            opposite: true,
            tickInterval: 24 * 60 * 60 * 1000,
            minorTickInterval: 6 * 60 * 60 * 1000,
            minorTickWidth: 1,
            minorTickLength: 10,
            minorGridLineWidth: 0,
            lineWidth: 1,
            startOnTick: false,
            endOnTick: false,
          },
          plotOptions: {
            columnrange: {
              grouping: false,
              allAreas: true,
              crisp: true,
              dataGrouping: false,
            },
            series: {
              states: {
                inactive: {
                  opacity: 1,
                },
                hover: {
                  opacity: 1,
                },
              },
            },
          },
          legend: {
            itemWidth: 275,
          },
        }
      );
    },
  },
};
</script>
