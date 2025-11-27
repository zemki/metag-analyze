<template>
  <div>
    <div class="treemap-controls mb-4">
      <div class="flex flex-wrap gap-2">
        <div class="relative">
          <select v-model="viewMode" @change="updateChart" class="px-3 py-2 pr-8 border rounded-md bg-white" style="-webkit-appearance: none; -moz-appearance: none; appearance: none;">
            <option value="project">Project Overview</option>
            <option value="entity">Entity Type</option>
            <option value="temporal">Time Period</option>
            <option value="participant">Participant</option>
          </select>

        </div>

        <div class="relative">
          <select v-model="sizeMetric" @change="updateChart" class="px-3 py-2 pr-8 border rounded-md bg-white" style="-webkit-appearance: none; -moz-appearance: none; appearance: none;">
            <option value="count">Entry Count</option>
            <option value="duration">Duration (s/min/h/d)</option>
            <option value="participants">Unique Participants</option>
          </select>

        </div>

        <div v-if="viewMode === 'temporal'" class="relative">
          <select v-model="temporalGrouping" @change="updateChart" class="px-3 py-2 pr-8 border rounded-md bg-white" style="-webkit-appearance: none; -moz-appearance: none; appearance: none;">
            <option value="year-month">Year & Month</option>
            <option value="month-week">Month & Week</option>
            <option value="week-day">Week & Day</option>
            <option value="day-hour">Day & Hour</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
            </svg>
          </div>
        </div>

        <button @click="drillUp" v-if="currentLevel > 0" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
          ← Back
        </button>
      </div>
    </div>

    <div :id="'treemap-' + chartId" class="treemap-container"></div>

    <div v-if="isLoading" class="flex justify-center items-center h-96">
      <div class="text-gray-500">Loading visualization...</div>
    </div>
  </div>
</template>

<script>
import Highcharts from 'highcharts';
import { v4 as uuidv4 } from 'uuid';
import _ from 'lodash';
import { parseDate } from '../utils/dateUtils';

export default {
  name: 'Treemap',
  props: {
    project: {
      type: Object,
      required: true
    },
    cases: {
      type: Array,
      default: () => []
    },
    entries: {
      type: Array,
      default: () => []
    },
    media: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      chartId: uuidv4(),
      chart: null,
      isLoading: true,
      viewMode: 'project',
      sizeMetric: 'count',
      currentLevel: 0,
      drilldownPath: [],
      temporalGrouping: 'year-month'
    };
  },
  computed: {
    entityName() {
      return this.project.use_entity ? (this.project.entity_name || 'Entity') : 'Media';
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.initChart();

      // Lazy load the data after initial render
      setTimeout(() => {
        this.updateChart();
        this.isLoading = false;
      }, 100);

      // Make component accessible globally for export
      window.treemapComponent = this;
    });
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy();
    }
  },
  methods: {
    initChart() {
      this.chart = Highcharts.chart(`treemap-${this.chartId}`, {
        chart: {
          height: 800,
          events: {
            load: function() {
              // Ensure proper rendering after load
              this.reflow();
            }
          }
        },
        title: {
          text: `Research Data Overview - ${this.viewMode.charAt(0).toUpperCase() + this.viewMode.slice(1)} View`,
          style: {
            fontSize: '18px'
          }
        },
        credits: {
          enabled: false
        },
        tooltip: {
          useHTML: true,
          pointFormatter: function() {
            const formatValue = (value) => {
              if (typeof value === 'number') {
                return value.toLocaleString();
              }
              return value;
            };

            return `<div class="p-2">
              <b>${this.name}</b><br/>
              ${this.entries ? `<span style="color: #666">Entries:</span> ${this.entries}<br/>` : ''}
              ${this.durationSeconds ? `<span style="color: #666">Duration:</span> ${this.series.chart.userOptions.formatDuration(this.durationSeconds)}<br/>` : ''}
              ${this.participants ? `<span style="color: #666">Participants:</span> ${this.participants}<br/>` : ''}
              <span style="font-size: 11px; color: #999">Click to drill down</span>
            </div>`;
          }
        },
        series: [{
          type: 'treemap',
          layoutAlgorithm: 'squarified',
          allowDrillToNode: true,
          animationLimit: 1000,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            formatter: function() {
              const chart = this.series.chart;
              let value = this.point.value;

              // Format value based on selected metric
              if (chart.userOptions.currentSizeMetric === 'duration') {
                value = chart.userOptions.formatDuration(value);
              } else if (typeof value === 'number') {
                value = value.toLocaleString();
              }

              return `${this.point.name}<br/>${value}`;
            },
            style: {
              textOutline: 'none'
            }
          },
          levelIsConstant: false,
          turboThreshold: 0, // Disable turbo threshold for large datasets
          levels: [{
            level: 1,
            dataLabels: {
              enabled: true,
              align: 'left',
              verticalAlign: 'top',
              style: {
                fontSize: '15px',
                fontWeight: 'bold'
              }
            },
            borderWidth: 3,
            borderColor: '#FFF'
          }, {
            level: 2,
            dataLabels: {
              style: {
                fontSize: '13px'
              }
            },
            borderWidth: 2,
            borderColor: '#FFF'
          }, {
            level: 3,
            dataLabels: {
              style: {
                fontSize: '11px'
              }
            },
            borderWidth: 1,
            borderColor: '#FFF'
          }],
          data: []
        }],
        formatDuration: this.formatDurationForDisplay, // Add helper function to chart
        currentSizeMetric: this.sizeMetric, // Track current metric
        plotOptions: {
          treemap: {
            allowPointSelect: true,
            cursor: 'pointer',
            point: {
              events: {
                click: (event) => {
                  if (event.point.drilldown) {
                    this.drillDown(event.point);
                  }
                }
              }
            },
            events: {
              click: (event) => {
                if (event.point && event.point.drilldown) {
                  this.drillDown(event.point);
                }
              }
            }
          }
        }
      });
    },

    updateChart() {
      const data = this.prepareData();
      this.chart.series[0].setData(data);
      this.chart.setTitle({
        text: `Research Data Overview - ${this.viewMode.charAt(0).toUpperCase() + this.viewMode.slice(1)} View`
      });

      // Update the chart's user options to track current metric
      this.chart.userOptions.currentSizeMetric = this.sizeMetric;

      // Force redraw to update data labels
      this.chart.redraw();
    },

    prepareData() {
      switch (this.viewMode) {
        case 'project':
          return this.prepareProjectView();
        case 'entity':
          return this.prepareEntityView();
        case 'temporal':
          return this.prepareTemporalView();
        case 'participant':
          return this.prepareParticipantView();
        default:
          return [];
      }
    },

    prepareProjectView() {
      const data = [];
      const projectName = this.project.name;
      const projectId = 'project-' + this.project.id;

      // Root level - Project
      const projectNode = {
        id: projectId,
        name: projectName,
        parent: '',
        value: this.getMetricValue(this.entries, 'project'),
        color: '#058DC7',
        entries: this.entries.length,
        participants: this.cases.length,
        durationSeconds: this.calculateTotalDuration(this.entries)
      };
      data.push(projectNode);

      // Second level - Cases
      this.cases.forEach((caseItem, index) => {
        const caseEntries = this.entries.filter(e => e.case_id === caseItem.id);
        const caseId = projectId + '-case-' + caseItem.id;

        const caseNode = {
          id: caseId,
          name: caseItem.name,
          parent: projectId,
          value: this.getMetricValue(caseEntries, 'case'),
          color: this.getColor(index),
          entries: caseEntries.length,
          durationSeconds: this.calculateTotalDuration(caseEntries),
          drilldown: true
        };
        data.push(caseNode);

        // Third level - Entries grouped by entity/media
        if (this.currentLevel > 0 && this.drilldownPath[0] === caseId) {
          const entityGroups = _.groupBy(caseEntries, 'media_id');

          Object.entries(entityGroups).forEach(([mediaId, groupEntries]) => {
            const media = this.media.find(m => m.id == mediaId);
            const entityName = media ? media.name : `${this.entityName} ${mediaId}`;

            data.push({
              id: caseId + '-entity-' + mediaId,
              name: entityName,
              parent: caseId,
              value: this.getMetricValue(groupEntries, 'entity'),
              color: this.adjustBrightness(this.getColor(index), -20),
              entries: groupEntries.length,
              durationSeconds: this.calculateTotalDuration(groupEntries)
            });
          });
        }
      });

      return data;
    },

    prepareEntityView() {
      const data = [];
      const rootId = 'entity-root';

      // Root node
      data.push({
        id: rootId,
        name: `All ${this.entityName}s`,
        parent: '',
        value: this.getMetricValue(this.entries, 'root'),
        color: '#50B432'
      });

      // Group by entity/media
      const entityGroups = _.groupBy(this.entries, 'media_id');

      Object.entries(entityGroups).forEach(([mediaId, groupEntries], index) => {
        const media = this.media.find(m => m.id == mediaId);
        const entityName = media ? media.name : `${this.entityName} ${mediaId}`;
        const entityId = rootId + '-' + mediaId;

        // Entity level
        const caseGroups = _.groupBy(groupEntries, 'case_id');

        data.push({
          id: entityId,
          name: entityName,
          parent: rootId,
          value: this.getMetricValue(groupEntries, 'entity'),
          color: this.getColor(index),
          entries: groupEntries.length,
          participants: Object.keys(caseGroups).length,
          durationSeconds: this.calculateTotalDuration(groupEntries),
          drilldown: true
        });

        // Drill down to cases
        if (this.currentLevel > 0 && this.drilldownPath[0] === entityId) {
          Object.entries(caseGroups).forEach(([caseId, caseEntries]) => {
            const caseItem = this.cases.find(c => c.id == caseId);

            data.push({
              id: entityId + '-case-' + caseId,
              name: caseItem ? caseItem.name : `Case ${caseId}`,
              parent: entityId,
              value: this.getMetricValue(caseEntries, 'case'),
              color: this.adjustBrightness(this.getColor(index), -20),
              entries: caseEntries.length,
              durationSeconds: this.calculateTotalDuration(caseEntries)
            });
          });
        }
      });

      return data;
    },

    prepareTemporalView() {
      const data = [];
      const rootId = 'temporal-root';

      // Root node
      data.push({
        id: rootId,
        name: 'All Time Periods',
        parent: '',
        value: this.getMetricValue(this.entries, 'root'),
        color: '#ED561B'
      });

      switch (this.temporalGrouping) {
        case 'year-month':
          this.prepareYearMonthView(data, rootId);
          break;
        case 'month-week':
          this.prepareMonthWeekView(data, rootId);
          break;
        case 'week-day':
          this.prepareWeekDayView(data, rootId);
          break;
        case 'day-hour':
          this.prepareDayHourView(data, rootId);
          break;
      }

      return data;
    },

    prepareYearMonthView(data, rootId) {
      // Group by year first
      const yearGroups = _.groupBy(this.entries, (entry) => {
        return parseDate(entry.begin).getFullYear();
      });

      Object.entries(yearGroups).sort().forEach(([year, yearEntries], yearIndex) => {
        const yearId = rootId + '-year-' + year;

        data.push({
          id: yearId,
          name: year,
          parent: rootId,
          value: this.getMetricValue(yearEntries, 'year'),
          color: this.getColor(yearIndex),
          entries: yearEntries.length,
          participants: _.uniq(yearEntries.map(e => e.case_id)).length,
          durationSeconds: this.calculateTotalDuration(yearEntries),
          drilldown: true
        });

        // Drill down to months
        if (this.currentLevel > 0 && this.drilldownPath[0] === yearId) {
          const monthGroups = _.groupBy(yearEntries, (entry) => {
            const date = parseDate(entry.begin);
            return date.getMonth();
          });

          Object.entries(monthGroups).sort((a, b) => Number(a[0]) - Number(b[0])).forEach(([month, monthEntries]) => {
            const monthName = new Date(2000, month).toLocaleString('default', { month: 'long' });

            data.push({
              id: yearId + '-month-' + month,
              name: monthName,
              parent: yearId,
              value: this.getMetricValue(monthEntries, 'month'),
              color: this.adjustBrightness(this.getColor(yearIndex), -20),
              entries: monthEntries.length,
              durationSeconds: this.calculateTotalDuration(monthEntries)
            });
          });
        }
      });
    },

    prepareMonthWeekView(data, rootId) {
      // Group by month
      const monthGroups = _.groupBy(this.entries, (entry) => {
        const date = parseDate(entry.begin);
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
      });

      Object.entries(monthGroups).sort().forEach(([month, monthEntries], index) => {
        const [year, monthNum] = month.split('-');
        const monthName = new Date(year, monthNum - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
        const monthId = rootId + '-month-' + month;

        data.push({
          id: monthId,
          name: monthName,
          parent: rootId,
          value: this.getMetricValue(monthEntries, 'month'),
          color: this.getColor(index),
          entries: monthEntries.length,
          participants: _.uniq(monthEntries.map(e => e.case_id)).length,
          durationSeconds: this.calculateTotalDuration(monthEntries),
          drilldown: true
        });

        // Drill down to weeks
        if (this.currentLevel > 0 && this.drilldownPath[0] === monthId) {
          const weekGroups = this.groupByWeekInMonth(monthEntries);

          Object.entries(weekGroups).forEach(([week, weekEntries]) => {
            data.push({
              id: monthId + '-week-' + week,
              name: `Week ${week}`,
              parent: monthId,
              value: this.getMetricValue(weekEntries, 'week'),
              color: this.adjustBrightness(this.getColor(index), -20),
              entries: weekEntries.length,
              durationSeconds: this.calculateTotalDuration(weekEntries)
            });
          });
        }
      });
    },

    prepareWeekDayView(data, rootId) {
      // Group by ISO week
      const weekGroups = _.groupBy(this.entries, (entry) => {
        const date = parseDate(entry.begin);
        const year = date.getFullYear();
        const weekNum = this.getISOWeek(date);
        return `${year}-W${String(weekNum).padStart(2, '0')}`;
      });

      Object.entries(weekGroups).sort().forEach(([week, weekEntries], index) => {
        const [year, weekStr] = week.split('-W');
        const weekId = rootId + '-week-' + week;

        data.push({
          id: weekId,
          name: `${year} Week ${weekStr}`,
          parent: rootId,
          value: this.getMetricValue(weekEntries, 'week'),
          color: this.getColor(index),
          entries: weekEntries.length,
          participants: _.uniq(weekEntries.map(e => e.case_id)).length,
          durationSeconds: this.calculateTotalDuration(weekEntries),
          drilldown: true
        });

        // Drill down to days
        if (this.currentLevel > 0 && this.drilldownPath[0] === weekId) {
          const dayGroups = _.groupBy(weekEntries, (entry) => {
            const date = parseDate(entry.begin);
            return date.getDay();
          });

          const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

          Object.entries(dayGroups).sort((a, b) => Number(a[0]) - Number(b[0])).forEach(([day, dayEntries]) => {
            data.push({
              id: weekId + '-day-' + day,
              name: dayNames[day],
              parent: weekId,
              value: this.getMetricValue(dayEntries, 'day'),
              color: this.adjustBrightness(this.getColor(index), -20),
              entries: dayEntries.length,
              durationSeconds: this.calculateTotalDuration(dayEntries)
            });
          });
        }
      });
    },

    prepareDayHourView(data, rootId) {
      // Group by day
      const dayGroups = _.groupBy(this.entries, (entry) => {
        const date = parseDate(entry.begin);
        return date.toISOString().split('T')[0];
      });

      Object.entries(dayGroups).sort().forEach(([day, dayEntries], index) => {
        const dayDate = new Date(day);
        const dayName = dayDate.toLocaleDateString('default', {
          weekday: 'short',
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
        const dayId = rootId + '-day-' + day;

        data.push({
          id: dayId,
          name: dayName,
          parent: rootId,
          value: this.getMetricValue(dayEntries, 'day'),
          color: this.getColor(index),
          entries: dayEntries.length,
          participants: _.uniq(dayEntries.map(e => e.case_id)).length,
          durationSeconds: this.calculateTotalDuration(dayEntries),
          drilldown: true
        });

        // Drill down to hours
        if (this.currentLevel > 0 && this.drilldownPath[0] === dayId) {
          const hourGroups = _.groupBy(dayEntries, (entry) => {
            return parseDate(entry.begin).getHours();
          });

          Object.entries(hourGroups).sort((a, b) => Number(a[0]) - Number(b[0])).forEach(([hour, hourEntries]) => {
            const hourNum = Number(hour);
            const hourName = `${hourNum === 0 ? 12 : hourNum > 12 ? hourNum - 12 : hourNum}:00 ${hourNum >= 12 ? 'PM' : 'AM'}`;

            data.push({
              id: dayId + '-hour-' + hour,
              name: hourName,
              parent: dayId,
              value: this.getMetricValue(hourEntries, 'hour'),
              color: this.adjustBrightness(this.getColor(index), -20),
              entries: hourEntries.length,
              durationSeconds: this.calculateTotalDuration(hourEntries)
            });
          });
        }
      });
    },

    prepareParticipantView() {
      const data = [];
      const rootId = 'participant-root';

      // Root node
      data.push({
        id: rootId,
        name: 'All Participants',
        parent: '',
        value: this.getMetricValue(this.entries, 'root'),
        color: '#DDDF00'
      });

      // Group by participant (case)
      this.cases.forEach((caseItem, index) => {
        const caseEntries = this.entries.filter(e => e.case_id === caseItem.id);

        if (caseEntries.length > 0) {
          const caseId = rootId + '-case-' + caseItem.id;

          data.push({
            id: caseId,
            name: caseItem.name,
            parent: rootId,
            value: this.getMetricValue(caseEntries, 'participant'),
            color: this.getColor(index),
            entries: caseEntries.length,
            durationSeconds: this.calculateTotalDuration(caseEntries),
            drilldown: true
          });

          // Drill down to time periods
          if (this.currentLevel > 0 && this.drilldownPath[0] === caseId) {
            const weekGroups = this.groupByWeek(caseEntries);

            Object.entries(weekGroups).forEach(([week, weekEntries], weekIndex) => {
              data.push({
                id: caseId + '-week-' + week,
                name: `Week ${week}`,
                parent: caseId,
                value: this.getMetricValue(weekEntries, 'week'),
                color: this.adjustBrightness(this.getColor(index), -20),
                entries: weekEntries.length,
                durationSeconds: this.calculateTotalDuration(weekEntries)
              });
            });
          }
        }
      });

      return data;
    },

    getMetricValue(entries, level) {
      switch (this.sizeMetric) {
        case 'count':
          return entries.length;
        case 'duration':
          const totalSeconds = this.calculateTotalDuration(entries);
          // Return seconds for numeric calculation, but store formatted version for display
          return totalSeconds;
        case 'participants':
          return _.uniq(entries.map(e => e.case_id)).length;
        default:
          return entries.length;
      }
    },

    formatDurationForDisplay(seconds) {
      if (seconds < 60) {
        return `${Math.round(seconds)}s`;
      } else if (seconds < 3600) {
        const minutes = Math.round(seconds / 60);
        return `${minutes}min`;
      } else if (seconds < 86400) {
        const hours = Math.round(seconds / 3600 * 10) / 10; // One decimal place
        return `${hours}h`;
      } else {
        const days = Math.round(seconds / 86400 * 10) / 10; // One decimal place
        return `${days}d`;
      }
    },

    calculateTotalDuration(entries) {
      return entries.reduce((total, entry) => {
        const duration = (parseDate(entry.end) - parseDate(entry.begin)) / 1000; // seconds
        return total + duration;
      }, 0);
    },

    groupByWeek(entries) {
      return _.groupBy(entries, (entry) => {
        const date = parseDate(entry.begin);
        const startOfYear = new Date(date.getFullYear(), 0, 1);
        const weekNumber = Math.ceil(((date - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7);
        return weekNumber;
      });
    },

    groupByWeekInMonth(entries) {
      return _.groupBy(entries, (entry) => {
        const date = parseDate(entry.begin);
        const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);
        const weekOfMonth = Math.ceil((date.getDate() + firstDayOfMonth.getDay()) / 7);
        return weekOfMonth;
      });
    },

    getISOWeek(date) {
      const d = new Date(date);
      d.setHours(0, 0, 0, 0);
      d.setDate(d.getDate() + 3 - (d.getDay() + 6) % 7);
      const week1 = new Date(d.getFullYear(), 0, 4);
      return 1 + Math.round(((d - week1) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
    },

    getColor(index) {
      const colors = [
        '#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5',
        '#64E572', '#FF9655', '#FFF263', '#6AF9C4', '#8085e9',
        '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1'
      ];
      return colors[index % colors.length];
    },

    adjustBrightness(color, percent) {
      const num = parseInt(color.replace('#', ''), 16);
      const amt = Math.round(2.55 * percent);
      const R = (num >> 16) + amt;
      const G = (num >> 8 & 0x00FF) + amt;
      const B = (num & 0x0000FF) + amt;

      return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
        (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
        (B < 255 ? B < 1 ? 0 : B : 255))
        .toString(16).slice(1);
    },

    drillDown(point) {
      if (point.drilldown) {
        this.currentLevel++;
        this.drilldownPath.push(point.id);
        this.updateChart();
      }
    },

    drillUp() {
      if (this.currentLevel > 0) {
        this.currentLevel--;
        this.drilldownPath.pop();
        this.updateChart();
      }
    },

    exportChart(format) {
      if (this.chart) {
        const filename = `treemap-${this.viewMode}-${new Date().toISOString().split('T')[0]}`;

        this.chart.exportChart({
          type: format === 'pdf' ? 'application/pdf' : 'image/png',
          filename: filename,
          sourceWidth: 1200,
          sourceHeight: 800,
          scale: 2,
          chartOptions: {
            title: {
              text: `${this.project.name} - ${this.viewMode.charAt(0).toUpperCase() + this.viewMode.slice(1)} View`
            }
          }
        });
      }
    }
  }
};
</script>

<style scoped>
.treemap-container {
  width: 100%;
  height: 800px;
  margin: 20px auto;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border-radius: 8px;
  overflow: visible;
  position: relative;
}

.treemap-controls {
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.treemap-controls select {
  min-width: 150px;
}

@media (max-width: 768px) {
  .treemap-container {
    height: 500px;
  }

  .treemap-controls .flex {
    flex-direction: column;
  }

  .treemap-controls select,
  .treemap-controls button {
    width: 100%;
  }
}

@media (min-width: 1200px) {
  .treemap-container {
    height: 900px;
  }
}

/* Ensure highcharts tooltips are visible */
:deep(.highcharts-container) {
  overflow: visible !important;
}

:deep(.highcharts-tooltip) {
  z-index: 9999 !important;
}
</style>
