<template>
  <div :id="'chart' + graphid"></div>
</template>

<script>
import Highcharts from 'highcharts';
import HighchartsGantt from 'highcharts/modules/gantt';
import { v4 as uuidv4 } from 'uuid';

HighchartsGantt(Highcharts);

export default {
  name: 'Graph',
  props: ['info', 'title', 'availabledata'],
  data() {
    return {
      avdata: [],
      realdata: [],
      graphid: uuidv4(),
      infoData: [],
    };
  },
  mounted() {
    // Process availabledata
    if (Array.isArray(this.availabledata)) {
      this.avdata = this.availabledata;
    } else if (typeof this.availabledata === 'object') {
      this.avdata = Object.values(this.availabledata);
    } else {
      this.avdata = [];
    }

    this.avdata = this.avdata.map((item) => item.toString().trim());

    // Process info
    if (Array.isArray(this.info)) {
      this.infoData = this.info;
    } else if (typeof this.info === 'object') {
      this.infoData = Object.values(this.info);
    } else {
      this.infoData = [];
    }

    this.prepareData();
    this.setChartTheme();
    this.drawChart();
  },
  methods: {
    setChartTheme() {
      Highcharts.theme = {
        credits: {
          enabled: false,
        },
        colors: [
          '#058DC7',
          '#50B432',
          '#ED561B',
          '#DDDF00',
          '#24CBE5',
          '#64E572',
          '#FF9655',
          '#FFF263',
          '#6AF9C4',
        ],
        title: {
          style: {
            color: '#000',
            fontSize: '25px',
            textTransform: 'uppercase',
          },
        },
        subtitle: {
          style: {
            color: '#666666',
            font: 'bold 12px "Trebuchet MS", Verdana, sans-serif',
          },
        },
        legend: {
          itemHoverStyle: {
            color: 'gray',
          },
        },
      };

      Highcharts.setOptions(Highcharts.theme);
    },
    prepareData() {
      const categoryMap = this.avdata.reduce((acc, category, index) => {
        acc[category] = index;
        return acc;
      }, {});

      this.realdata = [];

      this.infoData.forEach((data) => {
        if (!data.value) {
          return;
        }
        const value = data.value.toString().trim();
        const categoryIndex = categoryMap[value];

        if (categoryIndex !== undefined) {
          const start = Date.parse(data.start);
          const end = Date.parse(data.end);

          this.realdata.push({
            start,
            end,
            y: categoryIndex,
            name: value,
          });
        }
      });
    },
    drawChart() {
      Highcharts.ganttChart(`chart${this.graphid}`, {
        chart: {
          zoomType: 'x',
          spacingRight: 10,
        },
        title: {
          text: this.title,
        },
        xAxis: {
          tickInterval: 1000 * 60 * 60 * 12,
        },
        yAxis: {
          categories: this.avdata,
          reversed: true,
          title: null,
        },
        navigator: {
          enabled: true,
          liveRedraw: true,
          series: {
            type: 'gantt',
          },
        },
        scrollbar: {
          enabled: true,
        },
        rangeSelector: {
          enabled: true,
          selected: 0,
        },
        series: [{
          name: this.title,
          data: this.realdata,
        }],
      });
    },
  },
};
</script>

<style scoped>
[id^='chart'] {
  width: 100%;
  height: 500px;
  overflow: visible !important;
  margin: 30px auto;
}

.highcharts-root {
  font-family: 'Courier New', monospace;
}
</style>
