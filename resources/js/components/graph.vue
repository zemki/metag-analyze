<template>
  <div :id="'chart' + graphid"></div>
</template>

<script>
// Import Highcharts modules
import Highcharts from 'highcharts';
import { v4 as uuidv4 } from 'uuid';
import _ from 'lodash'; // Make sure this is installed

// We don't need to import these modules here as they're already
// loaded in app.js and available globally
// This prevents duplicate loading warnings
const loadHighchartsModules = async () => {
  // Simply verify that the modules are loaded
  if (!Highcharts.ganttChart) {
    console.warn('Highcharts Gantt module should be loaded in app.js');
  }
  return Promise.resolve();
};

export default {
  name: 'Graph',
  props: {
    info: {
      type: [Array, Object],
      default: () => []
    },
    title: {
      type: String,
      default: 'Chart'
    },
    availabledata: {
      type: [Array, Object],
      default: () => []
    }
  },
  data() {
    return {
      avdata: [],
      realdata: [],
      graphid: uuidv4(),
      infoData: [],
      chart: null,
      isLoading: true
    };
  },
  async mounted() {
    // Load Highcharts modules first
    await loadHighchartsModules();
    
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
    this.isLoading = false;
  },
  methods: {
    setChartTheme() {
      Highcharts.theme = {
        credits: {
          enabled: false,
        },
        colors: [
          '#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5',
          '#64E572', '#FF9655', '#FFF263', '#6AF9C4',
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
      // Performance: Use more efficient data structure and methods
      const categoryMap = this.avdata.reduce((acc, category, index) => {
        acc[category] = index;
        return acc;
      }, {});

      // Use efficient array allocation
      this.realdata = [];
      const now = Date.now();
      const oneDay = 24 * 60 * 60 * 1000;
      const today = new Date(now);
      today.setHours(0, 0, 0, 0);

      this.infoData.forEach((data) => {
        if (!data.value) {
          return;
        }
        const value = data.value.toString().trim();
        const categoryIndex = categoryMap[value];

        if (categoryIndex !== undefined) {
          const start = Date.parse(data.start);
          const end = Date.parse(data.end);

          // Enhanced data points with more information for tooltips
          this.realdata.push({
            start,
            end,
            y: categoryIndex,
            name: value,
            duration: this.formatDuration(end - start),
            daysFromToday: Math.round((start - today) / oneDay),
            completed: start < now && end < now ? 100 : 
                     start < now && end > now ? 
                     Math.round((now - start) / (end - start) * 100) : 0
          });
        }
      });
    },
    formatDuration(ms) {
      // Format duration for tooltips
      const hours = Math.floor(ms / (1000 * 60 * 60));
      const minutes = Math.floor((ms % (1000 * 60 * 60)) / (1000 * 60));
      
      return `${hours}h ${minutes}m`;
    },
    drawChart() {
      // Create weekend plot bands for better visual reference
      const plotBands = this.generateWeekendPlotBands();
      
      this.chart = Highcharts.ganttChart(`chart${this.graphid}`, {
        chart: {
          zoomType: 'xy', // Allow zooming in both directions
          spacingRight: 10,
          panning: true,
          panKey: 'shift', // Enable shift+drag panning
          style: {
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'
          }
        },
        title: {
          text: this.title,
        },
        xAxis: {
          tickInterval: 1000 * 60 * 60 * 12, // 12-hour intervals
          labels: {
            format: '{value:%H:%M}',
          },
          min: this.getMinDate(),
          max: this.getMaxDate(),
          plotBands: plotBands, // Add weekend plot bands
        },
        yAxis: {
          categories: this.avdata,
          reversed: true,
          title: null,
        },
        tooltip: {
          useHTML: true,
          formatter: function() {
            return `<div style="min-width: 150px; padding: 5px;">
                      <b>${this.point.name}</b><br>
                      <span style="color: #666;">Start:</span> ${Highcharts.dateFormat('%d %b %Y, %H:%M', this.point.start)}<br>
                      <span style="color: #666;">End:</span> ${Highcharts.dateFormat('%d %b %Y, %H:%M', this.point.end)}<br>
                      <span style="color: #666;">Duration:</span> ${this.point.duration}<br>
                      ${this.point.completed > 0 ? 
                        `<span style="color: #666;">Progress:</span> 
                         <div style="background:#eee;height:10px;border-radius:3px;margin:3px 0">
                           <div style="background:#50B432;height:10px;border-radius:3px;width:${this.point.completed}%"></div>
                         </div>` : ''}
                    </div>`;
          }
        },
        navigator: {
          enabled: true,
          liveRedraw: true,
          adaptToUpdatedData: true,
          series: {
            type: 'gantt',
          },
          xAxis: {
            labels: {
              formatter: function() {
                return Highcharts.dateFormat('%b %d', this.value);
              }
            }
          }
        },
        scrollbar: {
          enabled: true,
        },
        rangeSelector: {
          enabled: true,
          selected: 0,
          buttonTheme: {
            width: 60
          },
          buttons: [{
            type: 'day',
            count: 1,
            text: '1 day'
          }, {
            type: 'day',
            count: 3,
            text: '3 days'
          }, {
            type: 'week',
            count: 1,
            text: '1 week'
          }, {
            type: 'all',
            text: 'All'
          }]
        },
        series: [{
          name: this.title,
          data: this.realdata,
          dataLabels: {
            enabled: true,
            formatter: function() {
              return this.point.name;
            }
          }
        }],
        responsive: {
          rules: [{
            condition: {
              maxWidth: 600
            },
            chartOptions: {
              yAxis: {
                labels: {
                  style: {
                    fontSize: '10px'
                  }
                }
              },
              navigator: {
                height: 30
              },
              rangeSelector: {
                dropdown: 'always',
                inputEnabled: false
              }
            }
          }]
        }
      });
      
      // Add touch support for mobile/tablet
      this.addTouchSupport();
    },
    getMinDate() {
      if (!this.realdata.length) return new Date().getTime() - 86400000; // yesterday
      return Math.min(...this.realdata.map(d => d.start)) - 86400000; // 1 day before earliest
    },
    getMaxDate() {
      if (!this.realdata.length) return new Date().getTime() + 86400000; // tomorrow
      return Math.max(...this.realdata.map(d => d.end)) + 86400000; // 1 day after latest
    },
    generateWeekendPlotBands() {
      const plotBands = [];
      const startDate = new Date(this.getMinDate());
      const endDate = new Date(this.getMaxDate());
      const oneDay = 24 * 60 * 60 * 1000;
      
      // Go to the first Saturday
      let currentDate = new Date(startDate);
      while (currentDate.getDay() !== 6) { // 6 is Saturday
        currentDate = new Date(currentDate.getTime() + oneDay);
      }
      
      // Create plot bands for all weekends in range
      while (currentDate < endDate) {
        const weekendStart = currentDate.getTime();
        const weekendEnd = weekendStart + (2 * oneDay); // Saturday + Sunday
        
        plotBands.push({
          from: weekendStart,
          to: weekendEnd,
          color: 'rgba(240, 240, 240, 0.5)',
          label: {
            text: 'Weekend',
            style: {
              color: '#999',
              fontStyle: 'italic'
            },
            verticalAlign: 'bottom',
            y: -5
          }
        });
        
        // Move to next Saturday
        currentDate = new Date(currentDate.getTime() + (7 * oneDay));
      }
      
      return plotBands;
    },
    addTouchSupport() {
      if (!this.chart) return;
      
      // Throttled pan/zoom for touch devices
      const chartContainer = document.getElementById(`chart${this.graphid}`);
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
          const deltaX = touchStartX - touch.clientX;
          
          if (Math.abs(deltaX) > 30) { // Threshold to prevent accidental panning
            const xAxis = this.chart.xAxis[0];
            const extremes = xAxis.getExtremes();
            const range = extremes.max - extremes.min;
            
            // Calculate pan amount based on touch movement
            const panAmount = (deltaX / chartContainer.clientWidth) * range;
            
            // Apply the pan
            xAxis.setExtremes(
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
      if (this.chart) {
        this.chart.exportChart({
          type: type.split('/')[1] || 'png',
          filename: `${this.title}-${new Date().toISOString().split('T')[0]}`
        });
      }
    }
  },
  unmounted() {
    // Clean up the chart when component is destroyed
    if (this.chart) {
      this.chart.destroy();
    }
  }
};
</script>

<style scoped>
[id^='chart'] {
  width: 100%;
  height: 500px;
  overflow: visible !important;
  margin: 30px auto;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  border-radius: 8px;
}

.highcharts-root {
  font-family: 'system-ui', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

@media (max-width: 768px) {
  [id^='chart'] {
    height: 400px;
  }
}
</style>
