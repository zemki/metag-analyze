<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center pb-6 border-b border-gray-200">
      <div>
        <h3 class="text-lg font-medium text-gray-900">{{ trans('Questionnaires') }}</h3>
        <p class="mt-2 text-sm text-gray-600">
          {{ trans('Manage multiple questionnaires with unique questions for each questionnaire.') }}
        </p>
      </div>
      <button
          v-if="editable"
          type="button"
          @click="openAddScheduleDialog"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        {{ trans('Add Questionnaire') }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="w-8 h-8 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Empty State -->
    <div v-else-if="schedules.length === 0" class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">{{ trans('No questionnaires yet') }}</h3>
      <p class="mt-1 text-sm text-gray-500">{{ trans('Get started by creating a new questionnaire.') }}</p>
      <div class="mt-6">
        <button
            v-if="editable"
            type="button"
            @click="openAddScheduleDialog"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-xs text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          {{ trans('Add Questionnaire') }}
        </button>
      </div>
    </div>

    <!-- Schedules List -->
    <div v-else class="space-y-4">
      <div
          v-for="schedule in schedules"
          :key="schedule.id"
          class="bg-white border border-gray-200 rounded-lg shadow-xs hover:shadow-md transition-shadow duration-200"
      >
        <div class="p-6">
          <!-- Schedule Header -->
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center space-x-3">
                <h4 class="text-lg font-semibold text-gray-900">{{ schedule.name }}</h4>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="schedule.type === 'repeating' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                >
                  <svg v-if="schedule.type === 'repeating'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                  </svg>
                  {{ schedule.type === 'repeating' ? trans('Repeating') : trans('Single') }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  {{ trans('ID') }}: {{ schedule.questionnaire_id }}
                </span>
              </div>
              <p class="mt-2 flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ getQuestionCount(schedule) }} {{ trans('questions') }}
                <span v-if="getMaxQuestionVersion(schedule) > 1" class="ml-3 inline-flex items-center">
                  <svg class="w-4 h-4 mr-1 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                  </svg>
                  {{ trans('Versions') }}: 1-{{ getMaxQuestionVersion(schedule) }}
                </span>
              </p>
            </div>

            <!-- Actions -->
            <div v-if="editable" class="flex items-center space-x-2 ml-4">
              <button
                  @click="openEditQuestionsDialog(schedule)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-xs text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  :title="trans('Edit Questions')"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                {{ trans('Edit Questionnaire') }}
              </button>
              <button
                  @click="openVersionHistory(schedule)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-xs text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  :title="trans('Version History')"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ trans('History') }}
              </button>
              <button
                  @click="confirmDeleteSchedule(schedule)"
                  class="inline-flex items-center px-3 py-2 border border-red-300 shadow-xs text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  :title="trans('Delete Questionnaire')"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                {{ trans('Delete') }}
              </button>
            </div>
          </div>

          <!-- Schedule Details -->
          <div class="mt-3 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Left Column: Timing -->
              <div class="space-y-2">
                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ trans('Timing') }}</h5>

                <!-- Start Date -->
                <div class="flex items-center text-sm">
                  <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                  <span class="text-gray-600">{{ trans('Start') }}:</span>
                  <span v-if="getTimingConfig(schedule, 'start_on_first_login')" class="ml-2 px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-medium">
                    {{ trans('When participant logs in') }}
                  </span>
                  <span v-else class="ml-2 font-medium text-gray-900">{{ formatDateTime(getTimingConfig(schedule, 'start_date_time')) }}</span>
                </div>

                <!-- End Date -->
                <div class="flex items-center text-sm">
                  <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd"/></svg>
                  <span class="text-gray-600">{{ trans('End') }}:</span>
                  <span v-if="getTimingConfig(schedule, 'use_dynamic_end_date')" class="ml-2 px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium">
                    {{ trans('Auto-calculated from submissions') }}
                  </span>
                  <span v-else-if="getTimingConfig(schedule, 'end_date_time')" class="ml-2 font-medium text-gray-900">{{ formatDateTime(getTimingConfig(schedule, 'end_date_time')) }}</span>
                  <span v-else class="ml-2 text-gray-400">{{ trans('Not set') }}</span>
                </div>

                <!-- Daily Window (for repeating) -->
                <div v-if="schedule.type === 'repeating' && getTimingConfig(schedule, 'daily_start_time')" class="flex items-center text-sm">
                  <svg class="w-4 h-4 mr-2 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                  <span class="text-gray-600">{{ trans('Daily window') }}:</span>
                  <span class="ml-2 font-medium text-gray-900">{{ getTimingConfig(schedule, 'daily_start_time') }} - {{ getTimingConfig(schedule, 'daily_end_time') }}</span>
                </div>
              </div>

              <!-- Right Column: Settings -->
              <div class="space-y-2">
                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ trans('Settings') }}</h5>

                <!-- Submissions Info -->
                <div v-if="schedule.type === 'repeating'" class="flex flex-wrap items-center gap-3 text-sm">
                  <span v-if="getTimingConfig(schedule, 'max_daily_submits')" class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-600">{{ trans('Max daily') }}:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ getTimingConfig(schedule, 'max_daily_submits') }}</span>
                  </span>
                  <span v-if="getTimingConfig(schedule, 'max_total_submits')" class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-600">{{ trans('Total') }}:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ getTimingConfig(schedule, 'max_total_submits') }}</span>
                  </span>
                </div>

                <!-- Interval & Break (for repeating) -->
                <div v-if="schedule.type === 'repeating' && (getTimingConfig(schedule, 'daily_interval_duration') || getTimingConfig(schedule, 'min_break_between'))" class="flex flex-wrap items-center gap-3 text-sm">
                  <span v-if="getTimingConfig(schedule, 'daily_interval_duration')" class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 text-cyan-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-600">{{ trans('Interval') }}:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ getTimingConfig(schedule, 'daily_interval_duration') }}h</span>
                  </span>
                  <span v-if="getTimingConfig(schedule, 'min_break_between')" class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-600">{{ trans('Min break') }}:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ getTimingConfig(schedule, 'min_break_between') }}m</span>
                  </span>
                </div>

                <!-- Toggles Row -->
                <div class="flex flex-wrap items-center gap-2 pt-1">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium" :class="getNotificationConfig(schedule, 'show_notifications') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400'">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                    {{ trans('Notifications') }}
                  </span>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium" :class="getNotificationConfig(schedule, 'show_progress_bar') ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400'">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ trans('Progress bar') }}
                  </span>
                  <span v-if="schedule.is_ios_data_donation" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-700 text-white">
                    iOS Data Donation
                  </span>
                  <span v-if="schedule.is_android_data_donation" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-green-600 text-white">
                    Android Data Donation
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Collapsible Schedule Timeline (for repeating schedules) -->
          <div v-if="schedule.type === 'repeating' && hasScheduleData(schedule)" class="mt-4">
            <button
              @click="toggleScheduleTimeline(schedule.id)"
              class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>{{ expandedSchedules[schedule.id] ? trans('Hide Schedule Timeline') : trans('View Schedule Timeline') }}</span>
              </div>
              <svg
                class="w-5 h-5 text-gray-400 transition-transform duration-200"
                :class="{ 'rotate-180': expandedSchedules[schedule.id] }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Schedule Preview Component (Lazy Loaded) -->
            <div v-if="expandedSchedules[schedule.id]" class="mt-3">
              <Suspense>
                <template #default>
                  <SchedulePreview
                    :type="schedule.type"
                    :daily-interval-duration="getTimingConfig(schedule, 'daily_interval_duration') || 4"
                    :daily-start-time="getTimingConfig(schedule, 'daily_start_time') || '09:00'"
                    :daily-end-time="getTimingConfig(schedule, 'daily_end_time') || '21:00'"
                    :min-break-between="getTimingConfig(schedule, 'min_break_between') || 0"
                    :max-daily-submits="getTimingConfig(schedule, 'max_daily_submits') || 1"
                    :quest-available-at="getTimingConfig(schedule, 'quest_available_at') || 'startOfInterval'"
                  />
                </template>
                <template #fallback>
                  <div class="flex items-center justify-center p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <svg class="animate-spin h-5 w-5 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-gray-600">{{ trans('Loading timeline...') }}</span>
                  </div>
                </template>
              </Suspense>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Questionnaire Dialog -->
    <AddEditQuestionnaireDialog
        v-if="showScheduleDialog"
        :schedule="selectedSchedule"
        :project-id="projectId"
        :next-questionnaire-id="getNextQuestionnaireId()"
        :all-schedules="schedules"
        @close="closeScheduleDialog"
        @saved="handleScheduleSaved"
    />

    <!-- Version History Modal -->
    <VersionHistoryModal
        v-if="showHistoryModal"
        :schedule="selectedSchedule"
        @close="closeHistoryModal"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed z-50 inset-0 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="cancelDelete"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-50">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ trans('Delete Questionnaire') }}</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    {{ trans('Are you sure you want to delete') }} "<strong>{{ scheduleToDelete?.name }}</strong>"?
                    {{ trans('This will permanently delete all questions in this questionnaire. This action cannot be undone.') }}
                  </p>
                  <p class="mt-2 text-sm text-amber-600">
                    <strong>{{ trans('Note') }}:</strong> {{ trans('Question history will be preserved for data analysis purposes.') }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
                type="button"
                @click="deleteSchedule"
                :disabled="deleting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-xs px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              <svg v-if="deleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ deleting ? trans('Deleting...') : trans('Delete') }}
            </button>
            <button
                type="button"
                @click="cancelDelete"
                :disabled="deleting"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-xs px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              {{ trans('Cancel') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';
import AddEditQuestionnaireDialog from './AddEditQuestionnaireDialog.vue';
import VersionHistoryModal from './VersionHistoryModal.vue';
import { emitter } from '../../app.js';

// Lazy load SchedulePreview component for better performance
const SchedulePreview = defineAsyncComponent(() =>
  import('./SchedulePreview.vue')
);

export default {
  name: 'MartQuestionnaireManager',

  components: {
    AddEditQuestionnaireDialog,
    VersionHistoryModal,
    SchedulePreview
  },

  props: {
    projectId: {
      type: Number,
      required: true
    },
    editable: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      schedules: [],
      loading: true,
      showScheduleDialog: false,
      showHistoryModal: false,
      selectedSchedule: null,
      expandedSchedules: {}, // Track which schedules have timeline expanded
      // Delete confirmation
      showDeleteConfirm: false,
      scheduleToDelete: null,
      deleting: false
    };
  },

  mounted() {
    this.loadSchedules();
  },

  methods: {
    trans(key) {
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      }
      return window.trans[key] === "" ? key : window.trans[key];
    },

    async loadSchedules() {
      this.loading = true;
      try {
        const response = await window.axios.get(`/projects/${this.projectId}/questionnaires`);
        this.schedules = response.data.questionnaires || [];
      } catch (error) {
        // Check if it's a MART project not found error (project not fully set up yet)
        if (error.response?.status === 404 && error.response?.data?.message === 'MART project not found') {
          console.warn('MART project not fully initialized yet');
          this.schedules = []; // Set empty schedules, component will show "No questionnaires yet"
        } else {
          console.error('Error loading questionnaires:', error);
          emitter.emit('show-snackbar', this.trans('Failed to load questionnaires'));
        }
      } finally {
        this.loading = false;
      }
    },

    openAddScheduleDialog() {
      this.selectedSchedule = null;
      this.showScheduleDialog = true;
    },

    openEditQuestionsDialog(schedule) {
      this.selectedSchedule = schedule;
      this.showScheduleDialog = true;
    },

    closeScheduleDialog() {
      this.showScheduleDialog = false;
      this.selectedSchedule = null;
    },

    openVersionHistory(schedule) {
      this.selectedSchedule = schedule;
      this.showHistoryModal = true;
    },

    closeHistoryModal() {
      this.showHistoryModal = false;
      this.selectedSchedule = null;
    },

    confirmDeleteSchedule(schedule) {
      this.scheduleToDelete = schedule;
      this.showDeleteConfirm = true;
    },

    cancelDelete() {
      this.showDeleteConfirm = false;
      this.scheduleToDelete = null;
    },

    async deleteSchedule() {
      if (!this.scheduleToDelete) return;

      this.deleting = true;
      try {
        await window.axios.delete(`/questionnaires/${this.scheduleToDelete.id}`);

        // Remove from local state
        this.schedules = this.schedules.filter(s => s.id !== this.scheduleToDelete.id);

        emitter.emit('show-snackbar', this.trans('Questionnaire deleted successfully'));
        this.cancelDelete();
      } catch (error) {
        console.error('Error deleting questionnaire:', error);
        emitter.emit('show-snackbar', this.trans('Failed to delete questionnaire'));
      } finally {
        this.deleting = false;
      }
    },

    toggleScheduleTimeline(scheduleId) {
      if (!scheduleId) {
        console.error('toggleScheduleTimeline called without scheduleId');
        return;
      }
      try {
        // Vue 3: Direct assignment is reactive
        this.expandedSchedules[scheduleId] = !this.expandedSchedules[scheduleId];
      } catch (error) {
        console.error('Error toggling schedule timeline:', error);
      }
    },

    handleScheduleSaved() {
      this.closeScheduleDialog();
      this.loadSchedules();
      emitter.emit('show-snackbar', this.trans('Questionnaire saved successfully'));
    },

    getQuestionCount(schedule) {
      return schedule.questions ? schedule.questions.length : 0;
    },

    getMaxQuestionVersion(schedule) {
      if (!schedule.questions || schedule.questions.length === 0) return 1;
      return Math.max(...schedule.questions.map(q => q.version || 1));
    },

    getNextQuestionnaireId() {
      if (this.schedules.length === 0) return 1;
      return Math.max(...this.schedules.map(s => s.questionnaire_id)) + 1;
    },

    getTimingConfig(schedule, key) {
      if (!schedule.timing_config) return null;
      return schedule.timing_config[key];
    },

    getNotificationConfig(schedule, key) {
      if (!schedule.notification_config) return null;
      return schedule.notification_config[key];
    },

    hasScheduleData(schedule) {
      // Check if schedule has the minimum timing data needed for visualizer
      if (!schedule.timing_config) return false;
      const timing = schedule.timing_config;
      return timing.daily_interval_duration &&
             timing.daily_start_time &&
             timing.daily_end_time;
    },

    formatDateTime(dateTime) {
      if (!dateTime) return 'N/A';
      if (typeof dateTime === 'string') return dateTime;
      if (typeof dateTime === 'object' && dateTime.date && dateTime.time) {
        return `${dateTime.date} ${dateTime.time}`;
      }
      return 'N/A';
    },

    formatDateTimeCompact(dateTime) {
      if (!dateTime) return 'N/A';
      if (typeof dateTime === 'string') {
        // Parse date string like "2025-01-15" or "15.01.2025"
        const parts = dateTime.includes('-') ? dateTime.split('-') : dateTime.split('.');
        if (parts.length === 3) {
          const day = dateTime.includes('-') ? parts[2] : parts[0];
          const month = dateTime.includes('-') ? parts[1] : parts[1];
          return `${day}/${month}`;
        }
        return dateTime;
      }
      if (typeof dateTime === 'object' && dateTime.date) {
        const date = dateTime.date;
        const parts = date.includes('-') ? date.split('-') : date.split('.');
        if (parts.length === 3) {
          const day = date.includes('-') ? parts[2] : parts[0];
          const month = date.includes('-') ? parts[1] : parts[1];
          const time = dateTime.time ? ` ${dateTime.time}` : '';
          return `${day}/${month}${time}`;
        }
        return dateTime.time ? `${date} ${dateTime.time}` : date;
      }
      return 'N/A';
    }
  }
};
</script>