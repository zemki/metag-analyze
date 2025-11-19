<template>
  <div class="fixed z-50 inset-0 overflow-y-auto" @click.self="close">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              {{ isEditMode ? trans('Edit Questions') : trans('Add Questionnaire') }}
            </h3>
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
          <!-- Warning for edit mode -->
          <div v-if="isEditMode" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
              <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.768 0L3.046 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
              <div>
                <h4 class="text-sm font-medium text-yellow-800 mb-1">{{ trans('Editing Mid-Study') }}</h4>
                <p class="text-sm text-yellow-700">
                  {{ trans('Editing questions will increment the version number. Previous submissions are tracked with the old version for data analysis.') }}
                </p>
                <p class="text-sm text-yellow-700 mt-1">
                  {{ trans('Current version') }}: <strong>{{ schedule.questions_version || 1 }}</strong>
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <!-- Questionnaire Settings (only for new questionnaires) -->
            <div v-if="!isEditMode" class="space-y-4">
              <!-- Questionnaire Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700">{{ trans('Questionnaire Name') }} *</label>
                <input
                    v-model="formData.name"
                    type="text"
                    class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    :placeholder="trans('e.g., Daily Check-in, Morning Survey')"
                />
              </div>

              <!-- Introductory Text -->
              <div>
                <label class="block text-sm font-medium text-gray-700">{{ trans('Introductory Text (optional)') }}</label>
                <textarea
                    v-model="formData.introductory_text"
                    rows="3"
                    class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    :placeholder="trans('Text to display at the top of this questionnaire')"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">{{ trans('This text will be shown at the top of the questionnaire before any questions.') }}</p>
              </div>

              <!-- Questionnaire Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ trans('Questionnaire Type') }} *</label>
                <div class="grid grid-cols-2 gap-4">
                  <button
                      type="button"
                      @click="formData.type = 'single'"
                      :class="[
                        'p-4 border-2 rounded-lg text-left transition-all',
                        formData.type === 'single'
                          ? 'border-blue-500 bg-blue-50'
                          : 'border-gray-300 hover:border-gray-400'
                      ]"
                  >
                    <div class="font-medium text-gray-900">{{ trans('Single') }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ trans('One-time questionnaire') }}</div>
                  </button>
                  <button
                      type="button"
                      @click="formData.type = 'repeating'"
                      :class="[
                        'p-4 border-2 rounded-lg text-left transition-all',
                        formData.type === 'repeating'
                          ? 'border-blue-500 bg-blue-50'
                          : 'border-gray-300 hover:border-gray-400'
                      ]"
                  >
                    <div class="font-medium text-gray-900">{{ trans('Repeating') }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ trans('Multiple times during study') }}</div>
                  </button>
                </div>
              </div>

              <!-- Date/Time Settings -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">{{ trans('Start Date') }} *</label>
                  <input
                      v-model="formData.start_date_time.date"
                      type="date"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">{{ trans('Start Time') }} *</label>
                  <input
                      v-model="formData.start_date_time.time"
                      type="time"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>

              <!-- End Date/Time (for repeating) -->
              <div v-if="formData.type === 'repeating'" class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">{{ trans('End Date') }} *</label>
                  <input
                      v-model="formData.end_date_time.date"
                      type="date"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">{{ trans('End Time') }} *</label>
                  <input
                      v-model="formData.end_date_time.time"
                      type="time"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>

              <!-- Repeating Questionnaire Options -->
              <div v-if="formData.type === 'repeating'" class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">{{ trans('Repeating Questionnaire Options') }}</h4>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Daily Interval Duration (hours)') }}</label>
                    <input
                        v-model.number="formData.daily_interval_duration"
                        type="number"
                        min="1"
                        max="24"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Min Break Between (minutes)') }}</label>
                    <input
                        v-model.number="formData.min_break_between"
                        type="number"
                        min="0"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Max Daily Submits') }}</label>
                    <input
                        v-model.number="formData.max_daily_submits"
                        type="number"
                        min="1"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Quest Available At') }}</label>
                    <select
                        v-model="formData.quest_available_at"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="startOfInterval">{{ trans('Start of Interval') }}</option>
                      <option value="randomTimeWithinInterval">{{ trans('Random Time Within Interval') }}</option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Daily Start Time') }}</label>
                    <input
                        v-model="formData.daily_start_time"
                        type="time"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Daily End Time') }}</label>
                    <input
                        v-model="formData.daily_end_time"
                        type="time"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Notification Settings -->
              <div class="space-y-3">
                <div class="flex items-center">
                  <input
                      v-model="formData.show_progress_bar"
                      type="checkbox"
                      id="show_progress_bar"
                      class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                  />
                  <label for="show_progress_bar" class="ml-2 block text-sm text-gray-700">
                    {{ trans('Show Progress Bar') }}
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                      v-model="formData.show_notifications"
                      type="checkbox"
                      id="show_notifications"
                      class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                  />
                  <label for="show_notifications" class="ml-2 block text-sm text-gray-700">
                    {{ trans('Show Notifications') }}
                  </label>
                </div>

                <div v-if="formData.show_notifications">
                  <label class="block text-sm font-medium text-gray-700">{{ trans('Notification Text') }}</label>
                  <input
                      v-model="formData.notification_text"
                      type="text"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      :placeholder="trans('Time for your questionnaire!')"
                  />
                </div>
              </div>
            </div>

            <!-- Introductory Text (for edit mode) -->
            <div v-if="isEditMode" class="pt-6 border-t border-gray-200">
              <div>
                <label class="block text-sm font-medium text-gray-700">{{ trans('Introductory Text (optional)') }}</label>
                <textarea
                    v-model="formData.introductory_text"
                    rows="3"
                    class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    :placeholder="trans('Text to display at the top of this questionnaire')"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">{{ trans('This text will be shown at the top of the questionnaire before any questions.') }}</p>
              </div>
            </div>

            <!-- Questions Builder -->
            <div class="pt-6 border-t border-gray-200">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-md font-medium text-gray-900">{{ trans('Questions') }}</h4>
                <button
                    type="button"
                    @click="addQuestion"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  {{ trans('Add Question') }}
                </button>
              </div>

              <!-- Questions List -->
              <div v-if="formData.questions.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <svg class="mx-auto h-10 w-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">{{ trans('No questions added yet. Click "Add Question" to get started.') }}</p>
              </div>

              <div v-else class="space-y-4">
                <div
                    v-for="(question, index) in formData.questions"
                    :key="index"
                    class="border-2 border-gray-200 rounded-lg p-4 space-y-4 hover:border-blue-300 transition-colors duration-150"
                    :class="{'border-red-300 bg-red-50': hasQuestionError(index)}"
                >
                  <!-- Question Header -->
                  <div class="flex justify-between items-start">
                    <div class="flex items-center space-x-2">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                        {{ index + 1 }}
                      </span>
                      <span class="text-sm font-medium text-gray-700">{{ trans('Question') }}</span>
                      <span v-if="question.uuid" class="text-xs text-gray-400" :title="trans('Existing question with UUID')">
                        <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                      </span>
                    </div>
                    <button
                        type="button"
                        @click="removeQuestion(index)"
                        class="text-red-600 hover:text-red-900 hover:bg-red-50 p-1 rounded transition-colors"
                        :title="trans('Delete question')"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>

                  <!-- Question Text -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Question Text') }} *</label>
                    <textarea
                        v-model="question.text"
                        rows="2"
                        class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        :placeholder="trans('Enter your question')"
                    ></textarea>
                  </div>

                  <!-- Mandatory Checkbox -->
                  <div class="flex items-center">
                    <input
                        type="checkbox"
                        :id="'mandatory-' + index"
                        v-model="question.mandatory"
                        class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                    />
                    <label :for="'mandatory-' + index" class="ml-2 block text-sm text-gray-700">
                      {{ trans('Required question') }}
                    </label>
                  </div>

                  <!-- iOS Data Collection -->
                  <div class="flex items-center">
                    <input
                        type="checkbox"
                        :id="'ios-data-collection-' + index"
                        v-model="question.isIOSDataCollection"
                        class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                    />
                    <label :for="'ios-data-collection-' + index" class="ml-2 block text-sm text-gray-700">
                      {{ trans('iOS data collection question') }}
                    </label>
                  </div>

                  <!-- Android Data Collection -->
                  <div class="flex items-center">
                    <input
                        type="checkbox"
                        :id="'android-data-collection-' + index"
                        v-model="question.isAndroidDataCollection"
                        class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                    />
                    <label :for="'android-data-collection-' + index" class="ml-2 block text-sm text-gray-700">
                      {{ trans('Android data collection question') }}
                    </label>
                  </div>

                  <!-- Item Group -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Item Group (optional)') }}</label>
                    <input
                        type="text"
                        v-model="question.itemGroup"
                        class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        :placeholder="trans('Enter item group name')"
                    />
                  </div>

                  <!-- Question Type -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Question Type') }} *</label>
                    <select
                        v-model="question.type"
                        class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                      <option disabled value="">{{ trans('Select type...') }}</option>
                      <option value="text">{{ trans('Text Field') }}</option>
                      <option value="textarea">{{ trans('Text Area') }}</option>
                      <option value="number">{{ trans('Number') }}</option>
                      <option value="range">{{ trans('Range/Slider') }}</option>
                      <option value="radio">{{ trans('Single Choice') }}</option>
                      <option value="checkbox">{{ trans('Multiple Choice') }}</option>
                    </select>
                  </div>

                  <!-- Range Options -->
                  <div v-if="question.type === 'range'" class="grid grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Min Value') }}</label>
                      <input
                          type="number"
                          v-model.number="question.config.minValue"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Max Value') }}</label>
                      <input
                          type="number"
                          v-model.number="question.config.maxValue"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Steps') }}</label>
                      <input
                          type="number"
                          v-model.number="question.config.steps"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>

                  <!-- Choice Options -->
                  <div v-if="question.type === 'radio' || question.type === 'checkbox'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Options') }}</label>
                    <div v-for="(answer, aIndex) in question.answers" :key="aIndex" class="flex items-center space-x-2">
                      <input
                          type="text"
                          v-model="question.answers[aIndex]"
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          :placeholder="trans('Enter option text')"
                      />
                      <button
                          v-if="question.answers.length > 1"
                          @click="removeOption(index, aIndex)"
                          class="p-2 text-red-500 hover:bg-red-50 rounded-full"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>
                    <button
                        @click="addOption(index)"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-500 bg-blue-50 rounded-md hover:bg-blue-100"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      {{ trans('Add Option') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ trans('Cancel') }}
          </button>
          <button
              type="button"
              @click="save"
              :disabled="saving || !isValid"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isEditMode ? trans('Update Questions') : trans('Create Questionnaire') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddEditQuestionnaireDialog',

  props: {
    schedule: {
      type: Object,
      default: null
    },
    projectId: {
      type: Number,
      required: true
    },
    nextQuestionnaireId: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      saving: false,
      formData: {
        questionnaire_id: this.nextQuestionnaireId,
        name: '',
        introductory_text: '',
        type: 'single',
        start_date_time: { date: '', time: '09:00' },
        end_date_time: { date: '', time: '21:00' },
        show_progress_bar: true,
        show_notifications: true,
        notification_text: '',
        daily_interval_duration: 4,
        min_break_between: 180,
        max_daily_submits: 6,
        daily_start_time: '09:00',
        daily_end_time: '21:00',
        quest_available_at: 'randomTimeWithinInterval',
        questions: []
      }
    };
  },

  computed: {
    isEditMode() {
      return this.schedule !== null;
    },

    isValid() {
      if (this.formData.questions.length === 0) return false;

      for (const question of this.formData.questions) {
        if (!question.text || !question.type) return false;

        if ((question.type === 'radio' || question.type === 'checkbox') &&
            (!question.answers || question.answers.filter(a => a.trim()).length < 2)) {
          return false;
        }
      }

      if (!this.isEditMode) {
        if (!this.formData.name || !this.formData.start_date_time.date) return false;
        if (this.formData.type === 'repeating' && !this.formData.end_date_time.date) return false;
      }

      return true;
    }
  },

  mounted() {
    if (this.isEditMode) {
      this.formData.introductory_text = this.schedule.introductory_text || '';
      this.loadScheduleData();
    }
  },

  methods: {
    trans(key) {
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      }
      return window.trans[key] === "" ? key : window.trans[key];
    },

    loadScheduleData() {
      // Load questions from schedule with UUID-based structure
      this.formData.questions = (this.schedule.questions || []).map(q => {
        // Extract config values
        const config = q.config || {};

        return {
          uuid: q.uuid,  // Include UUID for updates
          text: q.text || '',
          type: this.mapBackendTypeToFormType(q.type) || '',
          mandatory: q.is_mandatory !== undefined ? q.is_mandatory : true,
          isIOSDataCollection: q.is_ios_data_collection || false,
          isAndroidDataCollection: q.is_android_data_collection || false,
          itemGroup: q.item_group || '',
          answers: config.options || [],
          config: {
            minValue: config.min || 0,
            maxValue: config.max || 10,
            steps: config.step || 1
          }
        };
      });
    },

    // Map backend types (scale, text, one choice, multiple choice) to form types
    mapBackendTypeToFormType(backendType) {
      const mapping = {
        'text': 'text',
        'scale': 'range',
        'one choice': 'radio',
        'multiple choice': 'checkbox'
      };
      return mapping[backendType] || 'text';
    },

    // Map form types (text, range, radio, checkbox) to backend types
    mapFormTypeToBackendType(formType) {
      const mapping = {
        'text': 'text',
        'textarea': 'text',
        'number': 'scale',
        'range': 'scale',
        'radio': 'one choice',
        'checkbox': 'multiple choice'
      };
      return mapping[formType] || 'text';
    },

    addQuestion() {
      this.formData.questions.push({
        text: '',
        type: '',
        mandatory: false,
        isIOSDataCollection: false,
        isAndroidDataCollection: false,
        itemGroup: '',
        answers: [],
        config: {
          minValue: 0,
          maxValue: 10,
          steps: 1
        }
      });
    },

    removeQuestion(index) {
      this.formData.questions.splice(index, 1);
    },

    addOption(questionIndex) {
      if (!this.formData.questions[questionIndex].answers) {
        this.formData.questions[questionIndex].answers = [];
      }
      this.formData.questions[questionIndex].answers.push('');
    },

    removeOption(questionIndex, optionIndex) {
      const answers = this.formData.questions[questionIndex].answers;
      if (answers.length > 1) {
        answers.splice(optionIndex, 1);
      }
    },

    async save() {
      if (!this.isValid) return;

      this.saving = true;

      try {
        // Convert questions to backend format
        const processedQuestions = this.formData.questions.map(q => {
          const backendType = this.mapFormTypeToBackendType(q.type);
          const config = {};

          // Add type-specific config
          if (q.type === 'range') {
            config.min = q.config.minValue;
            config.max = q.config.maxValue;
            config.step = q.config.steps;
          } else if (q.type === 'radio' || q.type === 'checkbox') {
            config.options = q.answers.filter(a => a.trim());
          }

          const questionData = {
            text: q.text,
            type: backendType,
            mandatory: q.mandatory,
            is_ios_data_collection: q.isIOSDataCollection || false,
            is_android_data_collection: q.isAndroidDataCollection || false,
            item_group: q.itemGroup || null,
            config: config
          };

          // Include UUID for updates
          if (this.isEditMode && q.uuid) {
            questionData.uuid = q.uuid;
          }

          return questionData;
        });

        if (this.isEditMode) {
          // Update questions and introductory text
          await window.axios.put(`/questionnaires/${this.schedule.id}/questions`, {
            questions: processedQuestions,
            introductory_text: this.formData.introductory_text
          });
        } else {
          // Create new questionnaire
          await window.axios.post(`/projects/${this.projectId}/questionnaires`, {
            ...this.formData,
            questions: processedQuestions
          });
        }

        this.$emit('saved');
      } catch (error) {
        console.error('Error saving questionnaire:', error);
        let errorMessage = this.trans('Failed to save questionnaire');

        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          errorMessage = errors.join(', ');
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }

        this.$root.showSnackbarMessage(errorMessage);
      } finally {
        this.saving = false;
      }
    },

    hasQuestionError(index) {
      const question = this.formData.questions[index];
      if (!question.text || !question.type) return true;

      if ((question.type === 'radio' || question.type === 'checkbox') &&
          (!question.answers || question.answers.filter(a => a.trim()).length < 2)) {
        return true;
      }

      return false;
    },

    close() {
      this.$emit('close');
    }
  }
};
</script>