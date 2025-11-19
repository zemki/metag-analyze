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
                {{ trans('Edit Questions') }}
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
            </div>
          </div>

          <!-- Schedule Details -->
          <div class="mt-4 bg-gray-50 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
              <div class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                <div>
                  <span class="font-medium text-gray-700">{{ trans('Start') }}:</span>
                  <span class="ml-2 text-gray-900">{{ formatDateTime(getTimingConfig(schedule, 'start_date_time')) }}</span>
                </div>
              </div>
              <div v-if="schedule.type === 'repeating' && getTimingConfig(schedule, 'end_date_time')" class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                <div>
                  <span class="font-medium text-gray-700">{{ trans('End') }}:</span>
                  <span class="ml-2 text-gray-900">{{ formatDateTime(getTimingConfig(schedule, 'end_date_time')) }}</span>
                </div>
              </div>
              <div v-if="getNotificationConfig(schedule, 'show_notifications')" class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                </svg>
                <div>
                  <span class="font-medium text-gray-700">{{ trans('Notifications') }}:</span>
                  <span class="ml-2 text-gray-900">{{ getNotificationConfig(schedule, 'notification_text') || trans('Enabled') }}</span>
                </div>
              </div>
              <div v-if="schedule.type === 'repeating' && getTimingConfig(schedule, 'max_daily_submits')" class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 text-purple-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                <div>
                  <span class="font-medium text-gray-700">{{ trans('Max Daily') }}:</span>
                  <span class="ml-2 text-gray-900">{{ getTimingConfig(schedule, 'max_daily_submits') }} {{ trans('submissions') }}</span>
                </div>
              </div>
              <div v-if="schedule.type === 'repeating' && getTimingConfig(schedule, 'daily_start_time')" class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                <div>
                  <span class="font-medium text-gray-700">{{ trans('Daily Window') }}:</span>
                  <span class="ml-2 text-gray-900">{{ getTimingConfig(schedule, 'daily_start_time') }} - {{ getTimingConfig(schedule, 'daily_end_time') }}</span>
                </div>
              </div>
              <div v-if="getNotificationConfig(schedule, 'show_progress_bar')" class="flex items-start">
                <svg class="w-4 h-4 mr-2 mt-0.5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <span class="text-gray-700">{{ trans('Progress bar enabled') }}</span>
                </div>
              </div>
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
        @close="closeScheduleDialog"
        @saved="handleScheduleSaved"
    />

    <!-- Version History Modal -->
    <VersionHistoryModal
        v-if="showHistoryModal"
        :schedule="selectedSchedule"
        @close="closeHistoryModal"
    />
  </div>
</template>

<script>
import AddEditQuestionnaireDialog from './AddEditQuestionnaireDialog.vue';
import VersionHistoryModal from './VersionHistoryModal.vue';

export default {
  name: 'MartQuestionnaireManager',

  components: {
    AddEditQuestionnaireDialog,
    VersionHistoryModal
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
      selectedSchedule: null
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
          this.$root.showSnackbarMessage(this.trans('Failed to load questionnaires'));
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

    handleScheduleSaved() {
      this.closeScheduleDialog();
      this.loadSchedules();
      this.$root.showSnackbarMessage(this.trans('Questionnaire saved successfully'));
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

    formatDateTime(dateTime) {
      if (!dateTime) return 'N/A';
      if (typeof dateTime === 'string') return dateTime;
      if (typeof dateTime === 'object' && dateTime.date && dateTime.time) {
        return `${dateTime.date} ${dateTime.time}`;
      }
      return 'N/A';
    }
  }
};
</script>