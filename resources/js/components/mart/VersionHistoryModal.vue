<template>
  <div class="fixed z-50 inset-0 overflow-y-auto" @click.self="close">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ trans('Question Version History') }}
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                {{ schedule.name }}
              </p>
            </div>
            <button
                @click="close"
                class="text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center py-12">
            <svg class="w-8 h-8 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 rounded-md">
            <div class="flex">
              <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>

          <!-- No History -->
          <div v-else-if="history.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ trans('No edit history') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ trans('This questionnaire has not been edited yet.') }}</p>
          </div>

          <!-- Version History List -->
          <div v-else class="space-y-6">
            <!-- Current Version -->
            <div class="border-2 border-blue-200 bg-blue-50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">
                    {{ trans('Current') }}
                  </span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ trans('Version') }} {{ currentVersion }}
                  </span>
                </div>
                <span class="text-xs text-gray-600">
                  {{ schedule.questions ? schedule.questions.length : 0 }} {{ trans('questions') }}
                </span>
              </div>

              <!-- Current Questions Preview -->
              <div v-if="expandedVersion === currentVersion" class="mt-4 space-y-2">
                <div
                    v-for="(question, index) in schedule.questions"
                    :key="question.uuid || index"
                    class="bg-white p-3 rounded border border-blue-200"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-900">{{ index + 1 }}. {{ question.text }}</p>
                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">v{{ question.version || 1 }}</span>
                      </div>
                      <div class="mt-1 flex items-center flex-wrap gap-2 text-xs text-gray-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100">
                          {{ trans('Type') }}: {{ formatQuestionType(question.type) }}
                        </span>
                        <span v-if="question.is_mandatory" class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-700">
                          {{ trans('Required') }}
                        </span>
                        <span v-if="question.config && question.config.options" class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-700">
                          {{ question.config.options.length }} {{ trans('options') }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <button
                  @click="toggleExpand(currentVersion)"
                  class="mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium"
              >
                {{ expandedVersion === currentVersion ? trans('Hide Questions') : trans('Show Questions') }}
              </button>
            </div>

            <!-- Historical Versions -->
            <div
                v-for="(entry, index) in sortedHistory"
                :key="index"
                class="border border-gray-300 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                    {{ trans('Version') }} {{ entry.version }}
                  </span>
                  <span class="text-xs text-gray-600">
                    {{ formatDate(entry.changed_at) }}
                  </span>
                </div>
                <span class="text-xs text-gray-600">
                  {{ entry.questions ? entry.questions.length : 0 }} {{ trans('questions') }}
                </span>
              </div>

              <!-- Historical Questions Preview -->
              <div v-if="expandedVersion === entry.version" class="mt-4 space-y-2">
                <div
                    v-for="(question, qIndex) in entry.questions"
                    :key="question.uuid || qIndex"
                    class="bg-gray-50 p-3 rounded border border-gray-200"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">{{ qIndex + 1 }}. {{ question.text }}</p>
                      <div class="mt-1 flex items-center flex-wrap gap-2 text-xs text-gray-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100">
                          {{ trans('Type') }}: {{ formatQuestionType(question.type) }}
                        </span>
                        <span v-if="question.is_mandatory" class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-700">
                          {{ trans('Required') }}
                        </span>
                        <span v-if="question.config && question.config.options" class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-700">
                          {{ question.config.options.length }} {{ trans('options') }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <button
                  @click="toggleExpand(entry.version)"
                  class="mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium"
              >
                {{ expandedVersion === entry.version ? trans('Hide Questions') : trans('Show Questions') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
          <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ trans('Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VersionHistoryModal',

  props: {
    schedule: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      loading: true,
      error: null,
      history: [],
      currentVersion: 1,
      expandedVersion: null
    };
  },

  computed: {
    sortedHistory() {
      return [...this.history].sort((a, b) => b.version - a.version);
    }
  },

  mounted() {
    this.loadHistory();
  },

  methods: {
    trans(key) {
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      }
      return window.trans[key] === "" ? key : window.trans[key];
    },

    async loadHistory() {
      this.loading = true;
      this.error = null;

      try {
        const response = await window.axios.get(`/questionnaires/${this.schedule.id}/history`);

        if (!response.data.success) {
          throw new Error(response.data.message || 'Failed to load history');
        }

        // The new API returns per-question history
        const questionsData = response.data.questions || [];

        // Build version-based history from question histories
        this.buildVersionHistory(questionsData);

        // Get current max version from schedule questions
        if (this.schedule.questions && this.schedule.questions.length > 0) {
          this.currentVersion = Math.max(...this.schedule.questions.map(q => q.version || 1));
        }
      } catch (error) {
        console.error('Error loading history:', error);
        this.error = error.response?.data?.message || this.trans('Failed to load version history');
      } finally {
        this.loading = false;
      }
    },

    buildVersionHistory(questionsData) {
      // Group all history entries by version
      const versionMap = new Map();

      questionsData.forEach(questionData => {
        // Add history entries
        if (questionData.history && questionData.history.length > 0) {
          questionData.history.forEach(historyEntry => {
            const version = historyEntry.version;
            if (!versionMap.has(version)) {
              versionMap.set(version, {
                version: version,
                changed_at: historyEntry.changed_at,
                questions: []
              });
            }

            versionMap.get(version).questions.push({
              uuid: historyEntry.question_uuid,
              text: historyEntry.text,
              type: historyEntry.type,
              is_mandatory: historyEntry.is_mandatory,
              config: historyEntry.config
            });
          });
        }
      });

      // Convert map to array and sort
      this.history = Array.from(versionMap.values()).sort((a, b) => a.version - b.version);
    },

    toggleExpand(version) {
      this.expandedVersion = this.expandedVersion === version ? null : version;
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';

      try {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return dateString;
      }
    },

    formatQuestionType(type) {
      const typeMap = {
        'text': this.trans('Text Field'),
        'scale': this.trans('Scale'),
        'one choice': this.trans('Single Choice'),
        'multiple choice': this.trans('Multiple Choice')
      };

      return typeMap[type] || type;
    },

    close() {
      this.$emit('close');
    }
  }
};
</script>