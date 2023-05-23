<template>
  <div :id="'chart' + graphid"></div>
</template>

<script>
import Highcharts from "highcharts";

export default {
  props: ["info", "title", "availabledata"],
  name: "graph",
  created() {
    const self = this;

    this.avdata = this.reindex_array_keys(this.availabledata);

    this.preparedata();
    setTimeout(() => {
      self.setChartTheme();
      self.drawChart();
      self.$forceUpdate();
    }, 250);
  },
  data() {
    return {
      avdata: [],
      realdata: [],
      graphid: Math.floor(Math.random() * 100 + 1),
    };
  },
  methods: {
    reindex_array_keys(array) {
      const temp = [];
      let start = 0;
      for (const i in array) {
        temp[start++] = array[i];
      }
      return temp;
    },
    setChartTheme() {
      Highcharts.theme = {
        credits: {
          enabled: false,
        },
        colors: [
          "#058DC7",
          "#50B432",
          "#ED561B",
          "#DDDF00",
          "#24CBE5",
          "#64E572",
          "#FF9655",
          "#FFF263",
          "#6AF9C4",
        ],
        title: {
          style: {
            color: "#000",
            fontSize: "25px",
            textTransform: "uppercase",
          },
        },
        subtitle: {
          style: {
            color: "#666666",
            font: 'bold 12px "Trebuchet MS", Verdana, sans-serif',
          },
        },
        legend: {
          itemHoverStyle: {
            color: "gray",
          },
        },
      };

      // Apply the theme
      Highcharts.setOptions(Highcharts.theme);
    },
    arraysEqual(a, b) {
      if (a === b) return true;
      if (a == null || b == null) return false;
      if (a.length !== b.length) return false;

      for (let i = 0; i < a.length; ++i) {
        if (a[i] !== b[i]) return false;
      }
      return true;
    },

    isScale() {
      return this.arraysEqual(
        Array.from(this.realdata, (x) => x.name),
        ["0", "1", "2", "3", "4", "5"]
      );
    },

    prepareDataValue(data, rl) {
      if (
        data.value == rl.name ||
        (_.isArray(data.value) && data.value.includes(rl.name)) ||
        this.isScale()
      ) {
        return data.value;
      }
      return "";
    },

    prepareStartEnd(data) {
      var split = data.start
        .substring(0, data.start.indexOf("."))
        .split(/[^0-9]/);
      split[1] -= 1;
      let start = Date.UTC(...split);

      split = data.end.substring(0, data.start.indexOf(".")).split(/[^0-9]/);
      split[1] -= 1;
      let end = Date.UTC(...split);

      return { start, end };
    },

    preparedata() {
      this.avdata.forEach((o) => {
        this.realdata.push({ name: o.toString().trim(), data: [] });
      });

      this.info.forEach((data, key) => {
        if (key === "available" || key === "title") return;

        this.realdata.forEach((rl) => {
          let datavalue = this.prepareDataValue(data, rl);
          if (datavalue !== "") {
            let { start, end } = this.prepareStartEnd(data);
            rl.data.push({
              start,
              end,
              name: datavalue.toString().trim(),
            });
          }
        });
      });

      this.realdata.forEach((d, i) => {
        d.data.forEach((data) => {
          data.y = i;
        });
      });
    },
    drawChart() {
      const self = this;
      Highcharts.ganttChart(`chart${self.graphid}`, {
        plotOptions: {
          column: {
            grouping: false,
            shadow: false,
          },
        },
        title: {
          text: self.title,
        },
        subtitle: {
          text:
            document.ontouchstart === undefined
              ? "Click and drag in the plot area to zoom in"
              : "Pinch the chart to zoom in",
        },
        xAxis: {
          tickInterval: 1000 * 60 * 60 * 12, // Day,
        },
        yAxis: {
          categories: self.avdata,
          breaks: [
            {
              breakSize: 0.1,
              from: 0,
              to: 0,
            },
          ],
        },
        chart: {
          zoomType: "x",
          spacingRight: 10,
        },
        navigator: {
          enabled: true,
          liveRedraw: true,
          series: {
            type: "gantt",
          },
        },
        scrollbar: {
          enabled: true,
        },
        rangeSelector: {
          enabled: true,
          selected: 0,
        },
        series: self.realdata,
      });
    },
  },
};
</script>

<style scoped>
[id^="chart"] {
  width: 100%;
  height: 100%;
  overflow: visible !important;
  margin: 30px auto;
}

.highcharts-root {
  font-family: "Courier New", monospace;
}
</style>
