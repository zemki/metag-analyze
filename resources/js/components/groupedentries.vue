<template>
  <div id="chart"></div>
</template>

<script>
import Highcharts from 'highcharts';
import HighchartsMore from 'highcharts/highcharts-more';
import { mapState, useStore } from 'vuex';
HighchartsMore(Highcharts);

export default {
  name: 'MedtagGraph',
  props: {
    media: {
      type: Array,
      default: () => []
    },
    inputs: {
      type: Array,
      default: () => []
    },
    entries: {
      type: Array,
      default: () => []
    },
    yColumn: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      categories: [],
      yAxisCategoryNames: [],
      chart: null,
      allSeries: [],
      interval: 30,
      begin: null,
      end: null,
      extremes: null,
    };
  },
  computed: {
    ...mapState({
      yAxisAttribute: (state) => state.graph.yAxisAttribute,
      formatterStatus: (state) => state.graph.formatter,
    }),
    coloredAttribute() {
      return { media: 'inputs', inputs: 'media' }[this.yAxisAttribute];
    },
    yAxisCategoryIds() {
      return this.categories.map((category) => category.id);
    },
  },
  watch: {
    formatterStatus: 'updateChartAsync',
    yAxisAttribute: 'updateChartAsync',
    begin: 'updateChartExtremes',
    end: 'updateChartExtremes',
  },
  created() {
    this.categories = this.getYAxisCategories();
    this.yAxisCategoryNames = this.categories.map((category) => category.name);

    this.$nextTick(() => {
      this.initiateChart();
      this.updateChart();
    });
  },
  unmounted() {
    if (this.chart) {
      this.chart.destroy();
    }
  },
  methods: {
    switchInputs() {
      const store = useStore();
      store.commit("switchyAxisAttribute");
    },
    getYAxisCategories() {
      return { media: this.media, inputs: this.inputs }[this.yAxisAttribute];
    },
    getColoredCategories() {
      return { media: this.media, inputs: this.inputs }[this.coloredAttribute];
    },
    initiateChart() {
      Highcharts.setOptions({
        global: {
          useUTC: false,
        },
      });

      this.chart = Highcharts.chart('chart', {
        chart: {
          type: 'columnrange',
          inverted: true,
          zoomType: 'y',
          height: (9 / 16) * 100 + '%',
          animation: false,
        },
        credits: {
          enabled: false,
        },
        title: null,
        xAxis: {
          categories: this.yAxisCategoryNames,
          max: this.yAxisCategoryNames.length - 1,
          labels: {
            step: 1,
          },
        },
        yAxis: {
          type: 'datetime',
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
            const low = Highcharts.dateFormat('%H:%M', this.low);
            const high = Highcharts.dateFormat('%H:%M', this.high);

            const coloredCategories = Array.isArray(this.meTagEntry[this.series.chart.options.coloredAttribute])
              ? this.meTagEntry[this.series.chart.options.coloredAttribute].map((category) => category.name).join('<br>')
              : '';

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
    updateChartAsync() {
      this.$nextTick(() => {
        this.updateChart();
      });
    },
    updateChartExtremes() {
      if (this.begin && this.end) {
        this.chart.yAxis[0].setExtremes(
          this.begin.getTime(),
          this.end.getTime()
        );
      }
    },
    updateChart() {
      this.categories = this.getYAxisCategories();
      this.yAxisCategoryNames = this.categories.map((category) => category.name);

      this.allSeries = this.prepareSeriesData();

      this.chart.xAxis[0].setCategories(this.yAxisCategoryNames, false);
      this.chart.xAxis[0].update(
        { max: this.yAxisCategoryNames.length - 1 },
        false
      );

      while (this.chart.series.length > 0) {
        this.chart.series[0].remove(false);
      }

      this.allSeries.forEach((series) => {
        this.chart.addSeries(series, false);
      });

      this.chart.redraw();
      this.extremes = this.chart.yAxis[0].getExtremes();

      this.begin = new Date(this.extremes.min);
      this.end = new Date(this.extremes.max);

      this.updatePlotBands();
    },
    prepareSeriesData() {
      return this.getColoredCategories().map((category) => {
        const seriesData = this.entries
          .filter((entry) => {
            const coloredAttributeArray = Array.isArray(entry[this.coloredAttribute])
              ? entry[this.coloredAttribute]
              : [entry[this.coloredAttribute]];
            return coloredAttributeArray[0]?.name === category.name;
          })
          .map((entry) => {
            const yAxisAttributeArray = Array.isArray(entry[this.yAxisAttribute])
              ? entry[this.yAxisAttribute]
              : [entry[this.yAxisAttribute]];
            const x = this.yAxisCategoryNames.indexOf(yAxisAttributeArray[0]?.name);

            if (x !== -1) {
              let low = entry.begin * 1000;
              let high = entry.end * 1000;
              if (high - low < this.interval * 60 * 1000) {
                high = low + this.interval * 60 * 1000;
              }

              return {
                x,
                low,
                high,
                meTagEntry: entry,
              };
            }
            return null;
          })
          .filter(Boolean)
          .sort((a, b) => a.x - b.x);

        return {
          id: String(category.id),
          color: `#${category.color}`,
          borderWidth: 0,
          name: category.name,
          data: seriesData,
        };
      });
    },
    updatePlotBands() {
      // Clear existing plot bands
      this.chart.yAxis[0].plotBands.forEach((band) => {
        this.chart.yAxis[0].removePlotBand(band.id);
      });

      // Add new plot bands
      let date = new Date(this.begin.getTime());
      date.setHours(0, 0, 0, 0);

      while (date.getDay() !== 6) {
        date.setDate(date.getDate() - 1);
      }

      while (date < this.end) {
        this.chart.yAxis[0].addPlotBand({
          id: `weekend-${date.getTime()}`,
          color: '#eee',
          from: date.getTime(),
          to: date.getTime() + 2 * 24 * 60 * 60 * 1000,
        });
        date.setDate(date.getDate() + 7);
      }
    },
    download(type) {
      this.chart.exportChart(
        {
          sourceWidth: this.chart.container.offsetWidth,
          sourceHeight: this.chart.container.offsetHeight,
          type,
        },
        {
          chart: {
            type: 'columnrange',
            inverted: true,
            zoomType: 'y',
            height: (9 / 16) * 100 + '%',
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
            type: 'datetime',
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
