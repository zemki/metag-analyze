<template>
  <div v-if="isRepeatingSchedule" class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200 overflow-hidden">
    <!-- Header -->
    <button
      @click="isExpanded = !isExpanded"
      class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-blue-100/50 transition-colors"
    >
      <div class="flex items-center space-x-3">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h4 class="text-base font-semibold text-gray-900">{{ trans('Schedule Preview') }}</h4>
          <p class="text-xs text-gray-600 mt-0.5">{{ trans('Visual timeline for a typical day') }}</p>
        </div>
      </div>
      <svg
        class="w-5 h-5 text-gray-500 transition-transform duration-200"
        :class="{ 'rotate-180': isExpanded }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Content -->
    <div v-show="isExpanded" class="px-5 pb-5 space-y-5">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100">
          <div class="flex items-center space-x-2 mb-1">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs font-medium text-gray-600">{{ trans('Daily Window') }}</span>
          </div>
          <div class="text-lg font-bold text-gray-900">{{ windowDuration }} <span class="text-sm font-normal text-gray-500">hrs</span></div>
        </div>

        <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100">
          <div class="flex items-center space-x-2 mb-1">
            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="text-xs font-medium text-gray-600">{{ trans('Intervals/Day') }}</span>
          </div>
          <div class="text-lg font-bold text-gray-900">{{ intervalsPerDay }}</div>
        </div>

        <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100">
          <div class="flex items-center space-x-2 mb-1">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs font-medium text-gray-600">{{ trans('Max Daily Submits') }}</span>
          </div>
          <div class="text-lg font-bold text-gray-900">{{ maxDailySubmits }}</div>
        </div>

        <div v-if="maxTotalSubmits" class="bg-white rounded-lg p-3 shadow-sm border border-purple-100">
          <div class="flex items-center space-x-2 mb-1">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs font-medium text-gray-600">{{ trans('Max Total Submits') }}</span>
          </div>
          <div class="text-lg font-bold text-gray-900">{{ maxTotalSubmits }}</div>
        </div>
      </div>

      <!-- Visual Timeline -->
      <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100">
        <div class="flex items-center justify-between mb-3">
          <h5 class="text-sm font-semibold text-gray-800">{{ trans('Interval Timeline') }}</h5>
          <span class="text-xs text-gray-500">{{ startTime }} - {{ endTime }}</span>
        </div>

        <!-- Timeline Bar -->
        <div class="relative">
          <!-- Background bar -->
          <div class="h-12 bg-gray-100 rounded-lg relative overflow-hidden">
            <!-- Interval segments -->
            <div
              v-for="(interval, index) in intervals"
              :key="index"
              class="absolute top-0 h-full border-r border-white"
              :style="{
                left: interval.leftPercent + '%',
                width: interval.widthPercent + '%',
                backgroundImage: getIntervalColor(index)
              }"
            >
              <div class="flex flex-col items-center justify-center h-full text-xs font-medium text-white">
                <div class="text-shadow">#{{ index + 1 }}</div>
              </div>
            </div>
          </div>

          <!-- Time markers -->
          <div class="flex justify-between mt-2 text-xs text-gray-600 font-medium">
            <span v-for="(interval, index) in intervals" :key="'time-' + index" class="flex-1 text-center">
              {{ interval.startTime }}
            </span>
            <span>{{ endTime }}</span>
          </div>
        </div>

        <!-- Legend -->
        <div class="mt-4 pt-3 border-t border-gray-200">
          <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-4">
              <div class="flex items-center space-x-1.5">
                <div class="w-3 h-3 rounded-sm bg-gradient-to-br from-blue-500 to-blue-600"></div>
                <span class="text-gray-600">{{ trans('Interval Block') }}</span>
              </div>
              <div class="flex items-center space-x-1.5">
                <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span class="text-gray-600">
                  {{ questAvailableAt === 'startOfInterval' ? trans('Available at start') : trans('Random time') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Warnings/Info -->
      <div v-if="warnings.length > 0" class="space-y-2">
        <div
          v-for="(warning, index) in warnings"
          :key="index"
          class="flex items-start space-x-2 p-3 rounded-lg"
          :class="{
            'bg-amber-50 border border-amber-200': warning.type === 'warning',
            'bg-red-50 border border-red-200': warning.type === 'error',
            'bg-green-50 border border-green-200': warning.type === 'info'
          }"
        >
          <svg
            class="w-5 h-5 flex-shrink-0 mt-0.5"
            :class="{
              'text-amber-600': warning.type === 'warning',
              'text-red-600': warning.type === 'error',
              'text-green-600': warning.type === 'info'
            }"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path v-if="warning.type === 'warning'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            <path v-else-if="warning.type === 'error'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            <path v-else fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <p class="text-xs" :class="{
            'text-amber-800': warning.type === 'warning',
            'text-red-800': warning.type === 'error',
            'text-green-800': warning.type === 'info'
          }">
            {{ warning.message }}
          </p>
        </div>
      </div>

      <!-- Success message -->
      <div v-else class="flex items-start space-x-2 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <p class="text-xs text-emerald-800">{{ trans('Schedule configuration looks good!') }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SchedulePreview',
  props: {
    dailyIntervalDuration: {
      type: Number,
      default: null
    },
    dailyStartTime: {
      type: String,
      default: '09:00'
    },
    dailyEndTime: {
      type: String,
      default: '21:00'
    },
    minBreakBetween: {
      type: Number,
      default: null
    },
    maxDailySubmits: {
      type: Number,
      default: null
    },
    maxTotalSubmits: {
      type: Number,
      default: null
    },
    questAvailableAt: {
      type: String,
      default: 'startOfInterval'
    },
    type: {
      type: String,
      default: 'single'
    },
    startOnFirstLogin: {
      type: Boolean,
      default: false
    },
    useDynamicEndDate: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isExpanded: false
    }
  },
  computed: {
    isRepeatingSchedule() {
      return this.type === 'repeating' && this.dailyIntervalDuration;
    },
    windowDuration() {
      if (!this.dailyStartTime || !this.dailyEndTime) return 0;
      const start = this.parseTime(this.dailyStartTime);
      const end = this.parseTime(this.dailyEndTime);
      return Math.round((end - start) / 60 * 10) / 10; // Round to 1 decimal
    },
    intervalsPerDay() {
      if (!this.dailyIntervalDuration || this.windowDuration === 0) return 0;
      return Math.floor(this.windowDuration / this.dailyIntervalDuration);
    },
    startTime() {
      return this.dailyStartTime || '09:00';
    },
    endTime() {
      return this.dailyEndTime || '21:00';
    },
    intervals() {
      const result = [];

      // Safety checks
      if (!this.dailyIntervalDuration || this.windowDuration === 0 || this.intervalsPerDay === 0) {
        return result;
      }

      const totalMinutes = this.windowDuration * 60;
      const intervalMinutes = this.dailyIntervalDuration * 60;

      try {
        for (let i = 0; i < this.intervalsPerDay; i++) {
          const startMinutes = i * intervalMinutes;
          const leftPercent = (startMinutes / totalMinutes) * 100;
          const widthPercent = (intervalMinutes / totalMinutes) * 100;
          const startTime = this.addMinutesToTime(this.startTime, startMinutes);

          result.push({
            leftPercent,
            widthPercent,
            startTime
          });
        }
      } catch (error) {
        console.error('Error calculating intervals:', error);
      }

      return result;
    },
    warnings() {
      const warnings = [];

      // Info: Dynamic start date
      if (this.startOnFirstLogin) {
        warnings.push({
          type: 'info',
          message: this.trans('Start date will be set when each participant logs in for the first time.')
        });
      }

      // Info: Dynamic end date
      if (this.useDynamicEndDate && this.maxTotalSubmits && this.maxDailySubmits) {
        const durationDays = Math.ceil(this.maxTotalSubmits / this.maxDailySubmits);
        warnings.push({
          type: 'info',
          message: this.trans('End date will be calculated as {days} days after start date (based on {total} total submits / {daily} daily submits).', {
            days: durationDays,
            total: this.maxTotalSubmits,
            daily: this.maxDailySubmits
          })
        });
      }

      // Warning: Max submits exceeds intervals (invalid configuration)
      if (this.maxDailySubmits && this.maxDailySubmits > this.intervalsPerDay) {
        warnings.push({
          type: 'error',
          message: this.trans('Maximum submitted surveys per day ({max}) exceeds available intervals ({intervals}). Please reduce max daily submits.', {
            max: this.maxDailySubmits,
            intervals: this.intervalsPerDay
          })
        });
      }

      // Warning: Min break longer than interval
      if (this.minBreakBetween && this.dailyIntervalDuration) {
        const minBreakHours = this.minBreakBetween / 60;
        if (minBreakHours > this.dailyIntervalDuration) {
          warnings.push({
            type: 'warning',
            message: this.trans('Min break ({break} min) is longer than interval duration ({duration} hours). This limits to 1 submission per interval.', {
              break: this.minBreakBetween,
              duration: this.dailyIntervalDuration
            })
          });
        }
      }

      // Warning: Daily window smaller than interval
      if (this.windowDuration < this.dailyIntervalDuration) {
        warnings.push({
          type: 'warning',
          message: this.trans('Daily window ({window}h) is shorter than interval duration ({duration}h). Only 1 interval possible.', {
            window: this.windowDuration,
            duration: this.dailyIntervalDuration
          })
        });
      }

      return warnings;
    }
  },
  methods: {
    parseTime(timeString) {
      if (!timeString) return 0;
      const [hours, minutes] = timeString.split(':').map(Number);
      return hours * 60 + minutes;
    },
    addMinutesToTime(timeString, minutesToAdd) {
      const totalMinutes = this.parseTime(timeString) + minutesToAdd;
      const hours = Math.floor(totalMinutes / 60) % 24;
      const minutes = totalMinutes % 60;
      return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    },
    getIntervalColor(index) {
      // Create gradient colors from blue to indigo
      const colors = [
        'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
        'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
        'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
        'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
        'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
        'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
      ];
      return colors[index % colors.length];
    },
    trans(key, replacements = {}) {
      let translation = key;
      Object.keys(replacements).forEach(placeholder => {
        translation = translation.replace(`{${placeholder}}`, replacements[placeholder]);
      });
      return translation;
    }
  }
}
</script>

<style scoped>
.text-shadow {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.rotate-180 {
  transform: rotate(180deg);
}
</style>
