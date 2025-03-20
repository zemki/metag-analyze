<template>
  <div id="chart"></div>
</template>

<script>
import Highcharts from 'highcharts';
import _ from 'lodash'; // Make sure this is installed
import { mapState, useStore } from 'vuex';

// We don't need to import these modules here as they're already
// loaded in app.js and available globally
// This prevents duplicate loading warnings
const loadHighchartsModules = async () => {
  // Simply verify that the modules are loaded
  if (!Highcharts.stockChart) {
    console.warn('Highcharts Stock module should be loaded in app.js');
  }
  return Promise.resolve();
};

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
      isLoading: true,
      resizeTimer: null
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
  async created() {
    // Load Highcharts modules first
    await loadHighchartsModules();
    
    this.categories = this.getYAxisCategories();
    this.yAxisCategoryNames = this.categories.map((category) => category.name);

    this.$nextTick(() => {
      this.initiateChart();
      this.updateChart();
      
      // Add responsive handling
      window.addEventListener('resize', this.handleResize);
      
      this.isLoading = false;
    });
  },
  unmounted() {
    if (this.chart) {
      this.chart.destroy();
    }
    
    // Remove event listeners
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    handleResize() {
      // Throttle resize events to improve performance
      clearTimeout(this.resizeTimer);
      this.resizeTimer = setTimeout(() => {
        if (this.chart) {
          this.chart.reflow();
        }
      }, 250);
    },
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
          zoomType: 'xy',
          height: (9 / 16) * 100 + '%',
          animation: false,
          panning: true,
          panKey: 'shift',
          style: {
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'
          }
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
            rotation: 0,
            style: {
              fontSize: '12px'
            }
          },
          crosshair: {
            width: 1,
            color: 'rgba(80,180,50,0.5)',
            dashStyle: 'dot'
          }
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
          labels: {
            format: '{value:%H:%M}',
          },
          crosshair: {
            width: 1,
            color: 'rgba(80,180,50,0.5)',
            dashStyle: 'dot'
          }
        },
        plotOptions: {
          columnrange: {
            grouping: false,
            borderRadius: 3,
            pointPadding: 0.2,
            borderWidth: 0
          },
          series: {
            states: {
              inactive: {
                opacity: 0.5, // Change to 0.5 for better distinction
              },
              hover: {
                brightness: 0.15,
                opacity: 1,
              },
            },
            events: {
              click: function(e) {
                // Alert or show details when clicking a point
                const entry = e.point.meTagEntry;
                if (entry) {
                  const time = Highcharts.dateFormat('%H:%M', e.point.low) + ' - ' + 
                               Highcharts.dateFormat('%H:%M', e.point.high);
                  
                  // You can replace this with a custom modal or details panel
                  alert(`Entry: ${entry.id}\nTime: ${time}`);
                }
              }
            }
          },
        },
        tooltip: {
          useHTML: true,
          borderRadius: 8,
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          borderWidth: 1,
          shadow: true,
          hideDelay: 200,
          formatter: function() {
            const low = Highcharts.dateFormat('%H:%M', this.point.low);
            const high = Highcharts.dateFormat('%H:%M', this.point.high);
            const date = Highcharts.dateFormat('%A, %b %e, %Y', this.point.low);
            
            // Calculate duration
            const durationMs = this.point.high - this.point.low;
            const hours = Math.floor(durationMs / (1000 * 60 * 60));
            const minutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));
            const duration = `${hours}h ${minutes}m`;
            
            const coloredCategories = Array.isArray(this.point.meTagEntry[this.series.chart.options.coloredAttribute])
              ? this.point.meTagEntry[this.series.chart.options.coloredAttribute].map((category) => 
                  `<span style="color:${this.series.color}">${category.name}</span>`).join('<br>')
              : '';
            
            return `<div style="min-width:180px; padding:10px;">
                      <div style="font-weight:bold; margin-bottom:8px;">${date}</div>
                      <div style="margin-bottom:5px;"><b>${low} - ${high}</b> (${duration})</div>
                      <div style="margin-top:8px;">${coloredCategories}</div>
                    </div>`;
          },
        },
        series: [],
        navigator: {
          enabled: true,
          adaptToUpdatedData: true,
          height: 30,
          margin: 10,
          outlineColor: '#999',
          handles: {
            backgroundColor: '#f7f7f7',
            borderColor: '#999'
          }
        },
        scrollbar: {
          enabled: true,
          barBackgroundColor: '#eeeeee',
          barBorderColor: '#cccccc',
          buttonBackgroundColor: '#f7f7f7',
          buttonBorderColor: '#cccccc',
          height: 10
        },
        rangeSelector: {
          enabled: true,
          inputEnabled: false,
          buttonPosition: {
            align: 'right'
          },
          buttons: [{
            type: 'day',
            count: 1,
            text: '1d'
          }, {
            type: 'day',
            count: 3,
            text: '3d'
          }, {
            type: 'week',
            count: 1,
            text: '1w'
          }, {
            type: 'all',
            text: 'All'
          }]
        },
        annotations: [{
          draggable: '',
          labelOptions: {
            backgroundColor: 'rgba(255,255,255,0.8)',
            borderColor: '#AAA',
            borderRadius: 3,
            borderWidth: 1,
            verticalAlign: 'top'
          }
        }],
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
          itemWidth: 200,
          itemStyle: {
            fontSize: '12px',
            fontWeight: 'normal'
          },
          symbolHeight: 12,
          symbolWidth: 12,
          symbolRadius: 6
        },
        responsive: {
          rules: [{
            condition: {
              maxWidth: 500
            },
            chartOptions: {
              xAxis: {
                labels: {
                  step: 2,
                  style: {
                    fontSize: '10px'
                  }
                }
              },
              legend: {
                itemWidth: 150
              },
              rangeSelector: {
                dropdown: 'always',
                buttonPosition: {
                  x: 0
                }
              }
            }
          }]
        }
      });
      
      // Add weekend plot bands
      this.addWeekendPlotBands();
      
      // Add touch support
      this.addTouchSupport();
      
      // Set coloredAttribute for tooltip access
      this.chart.options.coloredAttribute = this.coloredAttribute;
    },
    updateChartAsync() {
      // Throttle updates to prevent performance issues
      this.debounceUpdate = this.debounceUpdate || _.debounce(() => {
        this.updateChart();
      }, 300);
      
      this.debounceUpdate();
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

      // Performance: Memoize expensive calculations
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
      // Performance: Use filter->map pattern for large datasets
      const coloredCategories = this.getColoredCategories();
      const entries = this.entries;
      const yAxisAttribute = this.yAxisAttribute;
      const coloredAttribute = this.coloredAttribute;
      const yAxisCategoryNames = this.yAxisCategoryNames;
      const interval = this.interval;
      
      return coloredCategories.map((category) => {
        // Filter entries first to reduce subsequent iterations
        const filteredEntries = entries.filter((entry) => {
          const coloredAttributeArray = Array.isArray(entry[coloredAttribute])
            ? entry[coloredAttribute]
            : [entry[coloredAttribute]];
          return coloredAttributeArray[0]?.name === category.name;
        });
        
        // Then map to chart points
        const seriesData = filteredEntries.map((entry) => {
          const yAxisAttributeArray = Array.isArray(entry[yAxisAttribute])
            ? entry[yAxisAttribute]
            : [entry[yAxisAttribute]];
          const x = yAxisCategoryNames.indexOf(yAxisAttributeArray[0]?.name);

          if (x !== -1) {
            let low = entry.begin * 1000;
            let high = entry.end * 1000;
            if (high - low < interval * 60 * 1000) {
              high = low + interval * 60 * 1000;
            }

            return {
              x,
              low,
              high,
              meTagEntry: entry,
            };
          }
          return null;
        }).filter(Boolean).sort((a, b) => a.x - b.x);

        return {
          id: String(category.id),
          color: `#${category.color}`,
          borderWidth: 0,
          name: category.name,
          data: seriesData,
          cursor: 'pointer'
        };
      });
    },
    updatePlotBands() {
      // Clear existing plot bands
      this.chart.yAxis[0].plotLinesAndBands.forEach((band) => {
        this.chart.yAxis[0].removePlotBand(band.id);
      });

      this.addWeekendPlotBands();
    },
    addWeekendPlotBands() {
      if (!this.begin || !this.end) return;
      
      // Add new plot bands
      let date = new Date(this.begin.getTime());
      date.setHours(0, 0, 0, 0);

      while (date.getDay() !== 6) {
        date.setDate(date.getDate() - 1);
      }

      while (date < this.end) {
        const weekend = date.getTime();
        this.chart.yAxis[0].addPlotBand({
          id: `weekend-${weekend}`,
          color: 'rgba(240, 240, 240, 0.5)',
          from: weekend,
          to: weekend + 2 * 24 * 60 * 60 * 1000,
          label: {
            text: 'Weekend',
            style: {
              color: '#999',
              fontStyle: 'italic'
            },
            rotation: 90,
            textAlign: 'left',
            x: 5
          }
        });
        date.setDate(date.getDate() + 7);
      }
      
      // Add current day line
      const now = new Date();
      this.chart.yAxis[0].addPlotLine({
        id: 'now-line',
        color: 'rgba(255, 0, 0, 0.5)',
        width: 1,
        value: now.getTime(),
        dashStyle: 'Dash',
        zIndex: 5,
        label: {
          text: 'Now',
          align: 'right',
          style: {
            color: '#f00',
            fontWeight: 'bold'
          }
        }
      });
    },
    addTouchSupport() {
      if (!this.chart) return;
      
      const chartContainer = document.getElementById('chart');
      if (chartContainer) {
        // Simple touch event handling for chart panning
        let touchStartX = 0;
        let touchStartY = 0;
        
        chartContainer.addEventListener('touchstart', (e) => {
          const touch = e.touches[0];
          touchStartX = touch.clientX;
          touchStartY = touch.clientY;
        }, { passive: true });
        
        chartContainer.addEventListener('touchmove', _.throttle((e) => {
          if (e.touches.length > 1) return; // Skip multi-touch (zooming)
          
          const touch = e.touches[0];
          const deltaY = touchStartY - touch.clientY;
          
          if (Math.abs(deltaY) > 30) { // Threshold to prevent accidental panning
            const yAxis = this.chart.yAxis[0];
            const extremes = yAxis.getExtremes();
            const range = extremes.max - extremes.min;
            
            // Calculate pan amount based on touch movement
            const panAmount = (deltaY / chartContainer.clientHeight) * range;
            
            // Apply the pan
            yAxis.setExtremes(
              extremes.min + panAmount,
              extremes.max + panAmount
            );
            
            // Update start position for next move
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
          }
        }, 50), { passive: true });
      }
    },
    download(type) {
      this.chart.exportChart(
        {
          sourceWidth: this.chart.container.offsetWidth,
          sourceHeight: this.chart.container.offsetHeight,
          type,
          filename: `chart-export-${new Date().toISOString().split('T')[0]}`
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

<style scoped>
#chart {
  width: 100%;
  height: 500px;
  overflow: visible !important;
  margin: 30px auto;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  border-radius: 8px;
}

@media (max-width: 768px) {
  #chart {
    height: 400px;
  }
}
</style>
