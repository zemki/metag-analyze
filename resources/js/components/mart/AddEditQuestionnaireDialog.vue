<template>
  <div class="fixed z-50 inset-0 overflow-y-auto" @click.self="close">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500/75 transition-opacity z-40"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full relative z-50">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              {{ isEditMode ? trans('Edit Questionnaire') : trans('Add Questionnaire') }}
            </h3>
            <button
                @click="close"
                class="text-gray-400 hover:text-gray-500 focus:outline-hidden"
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
            <!-- Questionnaire Settings -->
            <div class="space-y-4">
              <!-- Questionnaire Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700">{{ trans('Questionnaire Name') }} *</label>
                <input
                    v-model="formData.name"
                    type="text"
                    class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    :placeholder="trans('e.g., Daily Check-in, Morning Survey')"
                />
              </div>

              <!-- Introductory Text -->
              <div>
                <label class="block text-sm font-medium text-gray-700">{{ trans('Introductory Text (optional)') }}</label>
                <textarea
                    v-model="formData.introductory_text"
                    rows="3"
                    class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
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
                      @click="setTypeRepeating"
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

              <!-- Start on First Login Checkbox - Available for all questionnaire types -->
              <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center">
                  <input
                      v-model="formData.start_on_first_login"
                      type="checkbox"
                      id="start_on_first_login"
                      class="h-4 w-4 text-green-600 focus:ring-green-400 border-gray-300 rounded"
                  />
                  <label for="start_on_first_login" class="ml-2 block text-sm text-green-900">
                    {{ trans('Start after participant logs in') }}
                  </label>
                </div>
                <!-- Hours delay input - shown when checkbox is checked -->
                <div v-if="formData.start_on_first_login" class="mt-3 ml-6 flex items-center space-x-2">
                  <label class="text-sm text-green-800">{{ trans('Delay after login:') }}</label>
                  <input
                      v-model.number="formData.start_hours_after_login"
                      type="number"
                      min="0"
                      max="168"
                      class="w-20 px-2 py-1 text-sm border border-green-300 rounded-md focus:ring-green-500 focus:border-green-500"
                  />
                  <span class="text-sm text-green-800">{{ trans('hours') }}</span>
                  <span class="text-xs text-green-600">({{ trans('0 = immediately') }})</span>
                </div>
                <p v-if="!formData.start_on_first_login" class="mt-1 ml-6 text-xs text-green-700">
                  {{ trans('Each participant will have their own start date based on their first login') }}
                </p>
              </div>

              <!-- Show After Repeating Questionnaire - Only for single questionnaires -->
              <div v-if="formData.type === 'single' && repeatingQuestionnaires.length > 0" class="space-y-3 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <div class="flex items-center">
                  <input
                      v-model="formData.use_show_after_repeating"
                      type="checkbox"
                      id="use_show_after_repeating"
                      class="h-4 w-4 text-purple-600 focus:ring-purple-400 border-gray-300 rounded"
                  />
                  <label for="use_show_after_repeating" class="ml-2 block text-sm font-medium text-purple-900">
                    {{ trans('Show after completing a repeating questionnaire') }}
                  </label>
                </div>
                <p class="text-xs text-purple-700">
                  {{ trans('This single questionnaire will only appear after the participant has completed a minimum number of submissions from a repeating questionnaire.') }}
                </p>

                <div v-if="formData.use_show_after_repeating" class="ml-6 space-y-3">
                  <div>
                    <label class="block text-sm font-medium text-purple-800">{{ trans('Select Repeating Questionnaire') }}</label>
                    <select
                        v-model="formData.show_after_repeating_quest_id"
                        class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-xs focus:ring-purple-500 focus:border-purple-500 bg-white"
                    >
                      <option :value="null" disabled>{{ trans('Select a questionnaire...') }}</option>
                      <option
                          v-for="q in repeatingQuestionnaires"
                          :key="q.questionnaire_id"
                          :value="q.questionnaire_id"
                      >
                        {{ q.name }} (ID: {{ q.questionnaire_id }})
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-purple-800">{{ trans('Minimum completions required') }}</label>
                    <input
                        v-model.number="formData.show_after_amount"
                        type="number"
                        min="1"
                        class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-xs focus:ring-purple-500 focus:border-purple-500"
                    />
                    <p class="mt-1 text-xs text-purple-600">
                      {{ trans('This questionnaire will appear after the participant completes at least this many submissions.') }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Date/Time Settings -->
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <h4 class="text-sm font-medium text-gray-900">{{ trans('Schedule Dates') }}</h4>
                  <button
                      type="button"
                      @click="useProjectDates"
                      :disabled="formData.start_on_first_login"
                      :class="[
                        'inline-flex items-center px-3 py-1 text-xs font-medium rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                        formData.start_on_first_login
                          ? 'text-gray-400 bg-gray-100 cursor-not-allowed'
                          : 'text-blue-700 bg-blue-50 hover:bg-blue-100'
                      ]"
                  >
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    {{ trans('Use Project Dates') }}
                  </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ trans('Start Date') }}
                      <span v-if="!formData.start_on_first_login">*</span>
                      <span v-else class="text-xs text-green-600 font-normal ml-1">({{ trans('Set on first login') }})</span>
                    </label>
                    <input
                        v-model="formData.start_date_time.date"
                        type="date"
                        :disabled="formData.start_on_first_login"
                        :placeholder="formData.start_on_first_login ? trans('Set on first login') : ''"
                        :class="[
                          'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs',
                          formData.start_on_first_login
                            ? 'bg-gray-100 cursor-not-allowed text-gray-500'
                            : 'focus:ring-blue-500 focus:border-blue-500'
                        ]"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ trans('Start Time') }}
                      <span v-if="!formData.start_on_first_login">*</span>
                    </label>
                    <input
                        v-model="formData.start_date_time.time"
                        type="time"
                        :disabled="formData.start_on_first_login"
                        :class="[
                          'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs',
                          formData.start_on_first_login
                            ? 'bg-gray-100 cursor-not-allowed text-gray-500'
                            : 'focus:ring-blue-500 focus:border-blue-500'
                        ]"
                    />
                    <p v-if="formData.start_on_first_login" class="mt-1 text-xs text-green-600">
                      {{ trans('Will use daily start time when participant logs in') }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Calculate End Date Dynamically Checkbox -->
              <div v-if="formData.type === 'repeating'" class="space-y-2">
                <div class="flex items-center">
                  <input
                      v-model="formData.use_dynamic_end_date"
                      type="checkbox"
                      id="use_dynamic_end_date"
                      class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                  />
                  <label for="use_dynamic_end_date" class="ml-2 block text-sm text-gray-700">
                    {{ trans('Calculate individual end dates per participant') }}
                  </label>
                </div>
                <p v-if="formData.use_dynamic_end_date && !calculatedEndDate" class="ml-6 text-xs text-orange-600">
                  {{ trans('Fill in Start Date, Submission Opportunities per Participant, and Max Daily Submits to auto-calculate') }}
                </p>

                <!-- Formula Display (Toggleable) -->
                <div v-if="formData.use_dynamic_end_date" class="ml-6 mt-3">
                  <button
                      type="button"
                      @click="showFormula = !showFormula"
                      class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition-colors"
                  >
                    <svg
                        class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-90': showFormula }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ trans('Show calculation formula') }}
                  </button>
                  <div v-if="showFormula" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-amber-700 font-medium mb-2">
                      {{ trans('Example calculation per participant (actual dates calculated at login):') }}
                    </p>
                    <div class="font-mono text-sm text-blue-900 space-y-1">
                      <div class="flex items-center gap-2">
                        <span class="text-blue-600">Duration</span>
                        <span>=</span>
                        <span class="text-blue-500 font-bold">[</span>
                        <span class="px-2 py-0.5 bg-white rounded border border-blue-200" :class="formData.max_total_submits ? 'text-blue-900' : 'text-gray-400'">
                          {{ formData.max_total_submits || '?' }}
                        </span>
                        <span>÷</span>
                        <span class="px-2 py-0.5 bg-white rounded border border-blue-200" :class="formData.max_daily_submits ? 'text-blue-900' : 'text-gray-400'">
                          {{ formData.max_daily_submits || '?' }}
                        </span>
                        <span class="text-blue-500 font-bold">]</span>
                        <span>=</span>
                        <span class="px-2 py-0.5 bg-green-100 rounded border border-green-300 font-semibold" :class="calculatedDurationDays ? 'text-green-800' : 'text-gray-400'">
                          {{ calculatedDurationDays || '?' }} {{ trans('days') }}
                        </span>
                      </div>
                      <div class="flex items-center gap-2 pt-1">
                        <span class="text-blue-600">End</span>
                        <span>=</span>
                        <span class="px-2 py-0.5 bg-white rounded border border-blue-200" :class="formData.start_on_first_login ? 'text-amber-600 italic' : (formData.start_date_time.date ? 'text-blue-900' : 'text-gray-400')">
                          {{ formData.start_on_first_login ? trans('First Login') : formatStartDateTime }}
                        </span>
                        <span>+</span>
                        <span class="px-2 py-0.5 bg-white rounded border border-blue-200" :class="calculatedDurationDays ? 'text-blue-900' : 'text-gray-400'">
                          {{ calculatedDurationDays || '?' }}
                        </span>
                        <span>=</span>
                        <span class="px-2 py-0.5 bg-green-100 rounded border border-green-300 font-semibold" :class="calculatedEndDateTime ? 'text-green-800' : 'text-gray-400'">
                          {{ calculatedEndDateTime || '?' }}
                        </span>
                      </div>
                    </div>
                    <p class="text-xs text-blue-600 mt-2">
                      <span class="font-bold">[ ]</span> = {{ trans('round up to nearest whole number') }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- End Date/Time (for repeating) -->
              <div v-if="formData.type === 'repeating'" class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">
                    {{ trans('End Date') }} *
                  </label>
                  <input
                      v-model="formData.end_date_time.date"
                      type="date"
                      :disabled="formData.use_dynamic_end_date && !!calculatedEndDate"
                      :class="[
                        'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs',
                        formData.use_dynamic_end_date && calculatedEndDate
                          ? 'bg-gray-100 cursor-not-allowed text-gray-500'
                          : 'focus:ring-blue-500 focus:border-blue-500'
                      ]"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">{{ trans('End Time') }} *</label>
                  <input
                      v-model="formData.end_date_time.time"
                      type="time"
                      :disabled="formData.use_dynamic_end_date && !!calculatedEndDate"
                      :class="[
                        'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs',
                        formData.use_dynamic_end_date && calculatedEndDate
                          ? 'bg-gray-100 cursor-not-allowed text-gray-500'
                          : 'focus:ring-blue-500 focus:border-blue-500'
                      ]"
                  />
                </div>
              </div>


              <!-- Repeating Questionnaire Options -->
              <div v-if="formData.type === 'repeating'" class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">{{ trans('Repeating Questionnaire Options') }}</h4>

                <!-- Daily Start/End Time (moved to top) -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Daily Start Time') }}</label>
                    <input
                        v-model="formData.daily_start_time"
                        type="time"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Beginning of the daily active window.') }}
                      <span class="block mt-0.5 text-gray-400">{{ trans('Example: 09:00 - questionnaires available from this time') }}</span>
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Daily End Time') }}</label>
                    <input
                        v-model="formData.daily_end_time"
                        type="time"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('End of the daily active window.') }}
                      <span class="block mt-0.5 text-gray-400">{{ trans('Example: 21:00 - last interval ends at this time') }}</span>
                    </p>
                  </div>
                </div>

                <!-- Other options -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Interval duration (hours)') }}</label>
                    <input
                        v-model.number="formData.daily_interval_duration"
                        type="number"
                        min="1"
                        max="24"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Day divided into intervals. Only 1 questionnaire per interval.') }}
                      <span class="block mt-0.5 text-gray-400">{{ trans('Example: 3 intervals = 4 hours each for 12 daily active hours') }}</span>
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Minimum Break Between Surveys (minutes)') }}</label>
                    <input
                        v-model.number="formData.min_break_between"
                        type="number"
                        min="0"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Minimum time between submissions to avoid overlapping time frames in experience sampling.') }}
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ trans('Maximum Submitted Surveys per Day') }}
                      <span v-if="numberOfIntervals > 0" class="text-xs font-normal text-gray-500 ml-1">({{ trans('max') }}: {{ numberOfIntervals }})</span>
                    </label>
                    <input
                        v-model.number="formData.max_daily_submits"
                        type="number"
                        min="1"
                        :max="numberOfIntervals || undefined"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p v-if="formData.max_daily_submits > numberOfIntervals && numberOfIntervals > 0" class="mt-1 text-xs text-red-600">
                      {{ trans('Cannot exceed number of intervals') }} ({{ numberOfIntervals }})
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500">
                      {{ trans('Maximum submissions allowed per day. Must be less than or equal to number of intervals.') }}
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Submission Opportunities per Participant') }}</label>
                    <input
                        v-model.number="formData.max_total_submits"
                        type="number"
                        min="1"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Total submission opportunities each participant will have across all days.') }}
                      <span class="block mt-0.5 text-gray-400">{{ trans('End date will be calculated based on this value') }}</span>
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Quest Available At') }}</label>
                    <select
                        v-model="formData.quest_available_at"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="startOfInterval">{{ trans('Start of Interval') }}</option>
                      <option value="randomTimeWithinInterval">{{ trans('Random Time Within Interval') }}</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                      <span class="block">{{ trans('Start: Available at 9:00, 13:00, 17:00') }}</span>
                      <span class="block mt-0.5 text-gray-400">{{ trans('Random: Available at random time within each interval') }}</span>
                    </p>
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
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                      :placeholder="trans('Time for your questionnaire!')"
                  />
                </div>
              </div>

              <!-- Data Donation Settings (only for single questionnaires) -->
              <div v-if="formData.type === 'single'" class="space-y-3 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h4 class="text-sm font-medium text-gray-900">{{ trans('Data Donation Settings') }}</h4>
                <p class="text-xs text-gray-600">{{ trans('Mark this questionnaire as a data donation questionnaire for device statistics collection. Only one questionnaire per project can be marked for each platform.') }}</p>

                <div class="space-y-2">
                  <div class="flex items-center">
                    <input
                        v-model="dataDonationType"
                        type="radio"
                        id="data_donation_none"
                        value="none"
                        class="h-4 w-4 text-purple-500 focus:ring-purple-400 border-gray-300"
                    />
                    <label for="data_donation_none" class="ml-2 block text-sm text-gray-700">
                      {{ trans('Not a data donation questionnaire') }}
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                        v-model="dataDonationType"
                        type="radio"
                        id="data_donation_ios"
                        value="ios"
                        class="h-4 w-4 text-purple-500 focus:ring-purple-400 border-gray-300"
                    />
                    <label for="data_donation_ios" class="ml-2 block text-sm text-gray-700">
                      <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-600" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                        {{ trans('iOS Data Donation Questionnaire') }}
                      </span>
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                        v-model="dataDonationType"
                        type="radio"
                        id="data_donation_android"
                        value="android"
                        class="h-4 w-4 text-purple-500 focus:ring-purple-400 border-gray-300"
                    />
                    <label for="data_donation_android" class="ml-2 block text-sm text-gray-700">
                      <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.6 9.48l1.84-3.18c.16-.31.04-.69-.26-.85-.29-.15-.65-.06-.83.22l-1.88 3.24c-1.39-.59-2.94-.92-4.56-.92-1.6 0-3.15.32-4.53.9L5.47 5.67c-.17-.29-.53-.39-.83-.24-.3.16-.43.54-.27.85L6.23 9.5C3.27 11.17 1.36 14.1 1 17.5h22c-.36-3.38-2.27-6.3-5.4-8.02zM7 15c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm10 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg>
                        {{ trans('Android Data Donation Questionnaire') }}
                      </span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Schedule Preview -->
            <SchedulePreview
              :type="formData.type"
              :daily-interval-duration="formData.daily_interval_duration"
              :daily-start-time="formData.daily_start_time"
              :daily-end-time="formData.daily_end_time"
              :min-break-between="formData.min_break_between"
              :max-daily-submits="formData.max_daily_submits"
              :max-total-submits="formData.max_total_submits"
              :quest-available-at="formData.quest_available_at"
              :start-on-first-login="formData.start_on_first_login"
              :use-dynamic-end-date="formData.use_dynamic_end_date"
            />

            <!-- Questions Builder -->
            <div class="pt-6 border-t border-gray-200">
              <div class="mb-4">
                <h4 class="text-md font-medium text-gray-900">{{ trans('Questions') }}</h4>
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
                    :class="{'border-red-300 bg-red-50': saveAttempted && hasQuestionError(index)}"
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
                        class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        :placeholder="trans('Enter your question')"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ trans('HTML formatting supported (e.g., <b>, <i>, <br>)') }}</p>
                  </div>

                  <!-- Image/Video URLs -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Image URL (Optional)') }}</label>
                      <input
                          type="url"
                          v-model="question.imageUrl"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                          :placeholder="trans('https://example.com/image.jpg')"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        {{ trans('Image shown above the question') }}
                      </p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Video URL (Optional)') }}</label>
                      <input
                          type="url"
                          v-model="question.videoUrl"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                          :placeholder="trans('https://example.com/video.mp4')"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        {{ trans('Video shown above the question') }}
                      </p>
                    </div>
                  </div>

                  <!-- Randomization Group -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Randomization Group (Optional)') }}</label>
                    <input
                        type="number"
                        v-model.number="question.randomizationGroupId"
                        min="0"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="trans('e.g., 1')"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Items with same group number will be randomized together. Leave empty for no randomization.') }}
                    </p>
                  </div>

                  <!-- Item Group -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Item Group (Optional)') }}</label>
                    <input
                        type="text"
                        v-model="question.itemGroup"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="trans('e.g., 1 or intro')"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Items with same group value will be shown together on one page. Leave empty to show separately.') }}
                    </p>
                  </div>

                  <!-- Mandatory Checkbox (not shown for display type) -->
                  <div v-if="question.type !== 'display'" class="flex items-center">
                    <input
                        type="checkbox"
                        :id="'mandatory-' + index"
                        v-model="question.mandatory"
                        class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                    />
                    <label :for="'mandatory-' + index" class="ml-2 block text-sm text-gray-700">
                      {{ trans('Mandatory question') }}
                    </label>
                  </div>

                  <!-- Question Type -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Question Type') }} *</label>
                    <select
                        v-model="question.type"
                        class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                      <option disabled value="">{{ trans('Select type...') }}</option>
                      <option value="display">{{ trans('Display Text Only') }}</option>
                      <option value="text">{{ trans('Text Field') }}</option>
                      <option value="textarea">{{ trans('Text Area') }}</option>
                      <option value="number">{{ trans('Number') }}</option>
                      <option value="range">{{ trans('Range/Slider') }}</option>
                      <option value="radio">{{ trans('Single Choice') }}</option>
                      <option value="checkbox">{{ trans('Multiple Choice') }}</option>
                    </select>
                    <p v-if="question.type === 'display'" class="mt-1 text-xs text-blue-600">
                      {{ trans('This item will show text without requiring an answer (instructions, headers, etc.)') }}
                    </p>
                  </div>

                  <!-- Placeholder (for text/textarea) -->
                  <div v-if="question.type === 'text' || question.type === 'textarea'">
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Placeholder (Optional)') }}</label>
                    <input
                        type="text"
                        v-model="question.placeholder"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="trans('e.g., Enter your answer here...')"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Hint text shown in the input field before the user types.') }}
                    </p>
                  </div>

                  <!-- Number Options -->
                  <div v-if="question.type === 'number'" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900">{{ trans('Number Input Options') }}</h4>
                    <div class="grid grid-cols-3 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700">{{ trans('Min Value') }}</label>
                        <input
                            type="number"
                            v-model.number="question.config.minValue"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                            :placeholder="trans('e.g., 0')"
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700">{{ trans('Max Value') }}</label>
                        <input
                            type="number"
                            v-model.number="question.config.maxValue"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                            :placeholder="trans('e.g., 100')"
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700">{{ trans('Max Digits (Optional)') }}</label>
                        <input
                            type="number"
                            v-model.number="question.config.maxDigits"
                            min="1"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                            :placeholder="trans('e.g., 5')"
                        />
                      </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                      {{ trans('Min/Max values limit the allowed range. Max Digits limits input length (e.g., 3 allows max 999).') }}
                    </p>
                  </div>

                  <!-- Range Options -->
                  <div v-if="question.type === 'range'" class="grid grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Min Value') }}</label>
                      <input
                          type="number"
                          v-model.number="question.config.minValue"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Max Value') }}</label>
                      <input
                          type="number"
                          v-model.number="question.config.maxValue"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ trans('Steps') }}</label>
                      <input
                          type="number"
                          v-model.number="question.config.steps"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>

                  <!-- Choice Options -->
                  <div v-if="question.type === 'radio' || question.type === 'checkbox'" class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">{{ trans('Options') }}</label>
                    <div v-for="(answer, aIndex) in question.answers" :key="aIndex" class="flex items-center space-x-2">
                      <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-200 text-gray-700 text-sm font-medium flex-shrink-0">
                        {{ aIndex + 1 }}
                      </span>
                      <input
                          type="text"
                          v-model="question.answers[aIndex]"
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
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

                    <!-- Randomize Answers Checkbox -->
                    <div class="flex items-center mt-3 pt-3 border-t border-gray-200">
                      <input
                          type="checkbox"
                          :id="'randomize-answers-' + index"
                          v-model="question.randomizeAnswers"
                          class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                      />
                      <label :for="'randomize-answers-' + index" class="ml-2 block text-sm text-gray-700">
                        {{ trans('Randomize answer options') }}
                      </label>
                    </div>

                    <!-- Include "Other" Text Field Checkbox -->
                    <div class="flex items-center mt-2">
                      <input
                          type="checkbox"
                          :id="'include-other-' + index"
                          v-model="question.includeOtherOption"
                          class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                      />
                      <label :for="'include-other-' + index" class="ml-2 block text-sm text-gray-700">
                        {{ trans('Include "Other" option with text field') }}
                      </label>
                    </div>
                    <!-- Custom label for "Other" option -->
                    <div v-if="question.includeOtherOption" class="mt-2 ml-6">
                      <label class="block text-sm font-medium text-gray-700">{{ trans('"Other" Option Label') }}</label>
                      <input
                          type="text"
                          v-model="question.otherOptionLabel"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500"
                          :placeholder="trans('e.g., Other, Sonstiges, Autre...')"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        {{ trans('Custom label text shown for the "Other" option. Leave empty for default.') }}
                      </p>
                    </div>
                  </div>

                  <!-- Timer Options (applies to all question types) -->
                  <div class="space-y-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="flex items-center">
                      <input
                          type="checkbox"
                          :id="'use-timer-' + index"
                          v-model="question.useTimer"
                          class="h-4 w-4 text-amber-600 focus:ring-amber-400 border-gray-300 rounded"
                      />
                      <label :for="'use-timer-' + index" class="ml-2 block text-sm font-medium text-amber-900">
                        {{ trans('Timed item') }}
                      </label>
                    </div>

                    <!-- Conditional Timer Settings -->
                    <div v-if="question.useTimer" class="ml-6 space-y-3">
                      <div>
                        <label class="block text-sm font-medium text-amber-900">{{ trans('Time limit (seconds)') }}</label>
                        <input
                            type="number"
                            v-model.number="question.timerSeconds"
                            min="1"
                            class="mt-1 block w-full px-3 py-2 border border-amber-300 rounded-md shadow-xs focus:ring-amber-500 focus:border-amber-500"
                            :placeholder="trans('e.g., 30')"
                        />
                        <p class="mt-1 text-xs text-amber-700">
                          {{ trans('Number of seconds allowed to answer this question.') }}
                        </p>
                      </div>

                      <div class="flex items-center">
                        <input
                            type="checkbox"
                            :id="'show-countdown-' + index"
                            v-model="question.showCountdown"
                            class="h-4 w-4 text-amber-600 focus:ring-amber-400 border-gray-300 rounded"
                        />
                        <label :for="'show-countdown-' + index" class="ml-2 block text-sm text-amber-900">
                          {{ trans('Show countdown to participant') }}
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Jump/Filter Options (radio and checkbox only) -->
                  <div v-if="question.type === 'radio' || question.type === 'checkbox'" class="space-y-3 p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-center">
                      <input
                          type="checkbox"
                          :id="'use-jump-' + index"
                          v-model="question.useJump"
                          class="h-4 w-4 text-purple-600 focus:ring-purple-400 border-gray-300 rounded"
                      />
                      <label :for="'use-jump-' + index" class="ml-2 block text-sm font-medium text-purple-900">
                        {{ trans('Use as a filter') }}
                      </label>
                    </div>

                    <!-- Conditional Jump Settings -->
                    <div v-if="question.useJump" class="ml-6 space-y-3">
                      <div>
                        <label class="block text-sm font-medium text-purple-900">{{ trans('Jump Condition (answer value)') }}</label>
                        <input
                            type="number"
                            v-model.number="question.jumpCondition"
                            min="1"
                            class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-xs focus:ring-purple-500 focus:border-purple-500"
                            :placeholder="trans('e.g., 1')"
                        />
                        <p class="mt-1 text-xs text-purple-700">
                          {{ trans('Enter the option number (shown to the left of each option) that triggers the jump.') }}
                        </p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-purple-900">{{ trans('Jump Over (number of items)') }}</label>
                        <input
                            type="number"
                            v-model.number="question.jumpOver"
                            min="1"
                            class="mt-1 block w-full px-3 py-2 border border-purple-300 rounded-md shadow-xs focus:ring-purple-500 focus:border-purple-500"
                            :placeholder="trans('e.g., 1')"
                        />
                        <p class="mt-1 text-xs text-purple-700">
                          {{ trans('Number of items to skip when condition is met.') }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Question Button (at bottom) -->
              <div class="mt-4">
                <button
                    type="button"
                    @click="addQuestion"
                    class="w-full inline-flex justify-center items-center px-4 py-3 border-2 border-dashed border-gray-300 text-sm font-medium rounded-lg text-gray-600 bg-gray-50 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  {{ trans('Add Question') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ trans('Cancel') }}
          </button>
          <button
              type="button"
              @click="save"
              :disabled="saving || !isValid"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isEditMode ? trans('Update Questionnaire') : trans('Create Questionnaire') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SchedulePreview from './SchedulePreview.vue';
import { emitter } from '../../app.js';

export default {
  name: 'AddEditQuestionnaireDialog',

  components: {
    SchedulePreview
  },

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
    },
    allSchedules: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      saving: false,
      showFormula: false,
      saveAttempted: false,
      formData: {
        questionnaire_id: this.nextQuestionnaireId,
        name: '',
        introductory_text: '',
        type: 'single',
        start_date_time: { date: '', time: '09:00' },
        end_date_time: { date: '', time: '21:00' },
        start_on_first_login: false,
        start_hours_after_login: 0,
        use_dynamic_end_date: false,
        use_show_after_repeating: false,
        show_after_repeating_quest_id: null,
        show_after_amount: 1,
        show_progress_bar: true,
        show_notifications: true,
        notification_text: '',
        is_ios_data_donation: false,
        is_android_data_donation: false,
        daily_interval_duration: 4,
        min_break_between: 30,
        max_daily_submits: null,
        max_total_submits: null,
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

    repeatingQuestionnaires() {
      // Filter to only repeating questionnaires, excluding the current one if editing
      return this.allSchedules.filter(s => {
        if (s.type !== 'repeating') return false;
        // Exclude current schedule if we're editing it (can't reference itself)
        if (this.isEditMode && s.id === this.schedule.id) return false;
        return true;
      });
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
        if (!this.formData.name) return false;
        // Start date is required only if not using "start on first login"
        if (!this.formData.start_on_first_login && !this.formData.start_date_time.date) return false;
        if (this.formData.type === 'repeating' && !this.formData.use_dynamic_end_date && !this.formData.end_date_time.date) return false;

        // Validate max_daily_submits doesn't exceed number of intervals
        if (this.formData.type === 'repeating' && this.numberOfIntervals > 0) {
          if (this.formData.max_daily_submits > this.numberOfIntervals) {
            return false;
          }
        }
      }

      return true;
    },

    calculatedDurationDays() {
      // Calculate duration in days from max_total_submits and max_daily_submits
      if (this.formData.max_total_submits && this.formData.max_daily_submits) {
        return Math.ceil(this.formData.max_total_submits / this.formData.max_daily_submits);
      }
      return null;
    },

    calculatedEndDate() {
      // Auto-calculate end date when checkbox is enabled and max_total_submits is provided
      if (
        this.formData.type === 'repeating' &&
        this.formData.use_dynamic_end_date &&
        this.formData.max_total_submits &&
        this.formData.max_daily_submits
      ) {
        const durationDays = this.calculatedDurationDays;

        // If start_on_first_login is true, we can't calculate exact date but can show duration
        if (this.formData.start_on_first_login) {
          return `+${durationDays} ${this.trans('days from login')}`;
        }

        // If we have a start date, calculate the exact end date
        if (this.formData.start_date_time.date) {
          const startDate = new Date(this.formData.start_date_time.date);
          const endDate = new Date(startDate);
          endDate.setDate(startDate.getDate() + durationDays);

          // Format as YYYY-MM-DD
          const formatted = endDate.toISOString().split('T')[0];

          // Auto-update the end_date_time.date field
          this.formData.end_date_time.date = formatted;

          return formatted;
        }
      }
      return null;
    },

    formatStartDateTime() {
      // Format start date and time for formula display
      const date = this.formData.start_date_time.date;
      const time = this.formData.start_date_time.time;
      if (!date) return '?';
      if (time) {
        return `${date} ${time}`;
      }
      return date;
    },

    calculatedEndDateTime() {
      // Return end date with time for formula display
      if (!this.calculatedEndDate) return null;

      // If it's a relative date (starts with +), just return as-is
      if (typeof this.calculatedEndDate === 'string' && this.calculatedEndDate.startsWith('+')) {
        return this.calculatedEndDate;
      }

      // Include time if start time is set (end time same as start time)
      const time = this.formData.start_date_time.time;
      if (time) {
        // Auto-set end time to match start time
        this.formData.end_date_time.time = time;
        return `${this.calculatedEndDate} ${time}`;
      }
      return this.calculatedEndDate;
    },

    numberOfIntervals() {
      // Calculate number of intervals based on daily window and interval duration
      if (
        this.formData.type === 'repeating' &&
        this.formData.daily_start_time &&
        this.formData.daily_end_time &&
        this.formData.daily_interval_duration > 0
      ) {
        const start = this.formData.daily_start_time.split(':').map(Number);
        const end = this.formData.daily_end_time.split(':').map(Number);

        const startMinutes = start[0] * 60 + start[1];
        const endMinutes = end[0] * 60 + end[1];

        const totalMinutes = endMinutes - startMinutes;
        const intervalMinutes = this.formData.daily_interval_duration * 60;

        return Math.floor(totalMinutes / intervalMinutes);
      }
      return 0;
    },

    dataDonationType: {
      get() {
        if (this.formData.is_ios_data_donation) return 'ios';
        if (this.formData.is_android_data_donation) return 'android';
        return 'none';
      },
      set(value) {
        this.formData.is_ios_data_donation = value === 'ios';
        this.formData.is_android_data_donation = value === 'android';
      }
    }
  },

  watch: {
    numberOfIntervals(newValue) {
      // Auto-set max_daily_submits when interval count changes (only for repeating questionnaires)
      if (this.formData.type === 'repeating' && newValue > 0) {
        // Auto-set if empty, exceeds new max, or was the old default
        if (!this.formData.max_daily_submits || this.formData.max_daily_submits > newValue || this.formData.max_daily_submits === 6) {
          this.formData.max_daily_submits = newValue;
        }
      }
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

    setTypeRepeating() {
      this.formData.type = 'repeating';
      // Reset settings not applicable for repeating questionnaires
      this.formData.start_on_first_login = false;
      // Reset data donation since it's only for single questionnaires
      this.formData.is_ios_data_donation = false;
      this.formData.is_android_data_donation = false;
    },

    loadScheduleData() {
      // Load questions from schedule with UUID-based structure
      this.formData.questions = (this.schedule.questions || []).map(q => {
        // Extract config values
        const config = q.config || {};
        const timer = config.timer || {};
        const jump = config.jump || {};

        return {
          uuid: q.uuid,  // Include UUID for updates
          text: q.text || '',
          imageUrl: q.image_url || '',
          videoUrl: q.video_url || '',
          type: this.mapBackendTypeToFormType(q.type) || '',
          mandatory: q.is_mandatory !== undefined ? q.is_mandatory : true,
          randomizationGroupId: q.randomizationGroupId || null,
          randomizeAnswers: q.randomizeAnswers || false,
          includeOtherOption: config.includeOtherOption || false,
          otherOptionLabel: config.otherOptionLabel || '',
          itemGroup: q.item_group || null,
          useTimer: !!timer.time,
          timerSeconds: timer.time || 30,
          showCountdown: timer.showCountdown !== undefined ? timer.showCountdown : true,
          useJump: !!(jump.jumpCondition !== undefined || jump.jumpOver !== undefined),
          // Convert jumpCondition from 0-based (API) to 1-based (UI)
          jumpCondition: jump.jumpCondition !== undefined ? parseInt(jump.jumpCondition) + 1 : 1,
          jumpOver: jump.jumpOver !== undefined ? jump.jumpOver : 1,
          placeholder: config.placeholder || '',
          answers: config.options || [],
          config: {
            minValue: config.min || 0,
            maxValue: config.max || 10,
            steps: config.step || 1,
            maxDigits: config.maxDigits || null
          }
        };
      });

      // Load basic schedule info
      if (this.schedule.name) {
        this.formData.name = this.schedule.name;
      }
      if (this.schedule.questionnaire_id) {
        this.formData.questionnaire_id = this.schedule.questionnaire_id;
      }
      if (this.schedule.type) {
        this.formData.type = this.schedule.type;
      }

      // Load timing configuration
      if (this.schedule.timing_config) {
        const timing = this.schedule.timing_config;

        // Load date/time settings
        if (timing.start_date_time) {
          this.formData.start_date_time = {
            date: timing.start_date_time.date || '',
            time: timing.start_date_time.time || '09:00'
          };
        }

        if (timing.end_date_time) {
          this.formData.end_date_time = {
            date: timing.end_date_time.date || '',
            time: timing.end_date_time.time || '21:00'
          };
        }

        // Load repeating schedule settings
        if (timing.daily_interval_duration !== undefined) {
          this.formData.daily_interval_duration = timing.daily_interval_duration;
        }

        if (timing.min_break_between !== undefined) {
          this.formData.min_break_between = timing.min_break_between;
        }

        if (timing.max_daily_submits !== undefined) {
          this.formData.max_daily_submits = timing.max_daily_submits;
        }

        if (timing.max_total_submits !== undefined) {
          this.formData.max_total_submits = timing.max_total_submits;
        }

        if (timing.daily_start_time) {
          this.formData.daily_start_time = timing.daily_start_time;
        }

        if (timing.daily_end_time) {
          this.formData.daily_end_time = timing.daily_end_time;
        }

        if (timing.quest_available_at) {
          this.formData.quest_available_at = timing.quest_available_at;
        }

        // Load start_on_first_login flag and hours delay
        if (timing.start_on_first_login !== undefined) {
          this.formData.start_on_first_login = timing.start_on_first_login;
        }
        if (timing.start_hours_after_login !== undefined) {
          this.formData.start_hours_after_login = timing.start_hours_after_login;
        }

        // Load use_dynamic_end_date flag
        if (timing.use_dynamic_end_date !== undefined) {
          this.formData.use_dynamic_end_date = timing.use_dynamic_end_date;
        }

        // Load show_after_repeating settings (for single questionnaires)
        if (timing.show_after_repeating) {
          this.formData.use_show_after_repeating = true;
          this.formData.show_after_repeating_quest_id = timing.show_after_repeating.repeatingQuestId || null;
          this.formData.show_after_amount = timing.show_after_repeating.showAfterAmount || 1;
        }
      }

      // Load notification configuration
      if (this.schedule.notification_config) {
        const notif = this.schedule.notification_config;

        if (notif.show_progress_bar !== undefined) {
          this.formData.show_progress_bar = notif.show_progress_bar;
        }

        if (notif.show_notifications !== undefined) {
          this.formData.show_notifications = notif.show_notifications;
        }

        if (notif.notification_text) {
          this.formData.notification_text = notif.notification_text;
        }
      }

      // Load data donation settings (mutually exclusive)
      if (this.schedule.is_ios_data_donation) {
        this.formData.is_ios_data_donation = true;
        this.formData.is_android_data_donation = false;
      } else if (this.schedule.is_android_data_donation) {
        this.formData.is_ios_data_donation = false;
        this.formData.is_android_data_donation = true;
      }
    },

    // Map backend/DB types to form types
    // DB stores: number, range, text, textarea, one choice, multiple choice, display
    // Form uses: number, range, text, textarea, radio, checkbox, display
    mapBackendTypeToFormType(backendType) {
      const mapping = {
        'text': 'text',
        'textarea': 'textarea',
        'number': 'number',
        'range': 'range',
        'one choice': 'radio',
        'multiple choice': 'checkbox',
        'display': 'display'
      };
      return mapping[backendType] || 'text';
    },

    // Map form types to backend/DB types
    // DB stores: number, range, text, textarea, one choice, multiple choice, display
    // Mobile API expects: number, range, text, textarea, radio, checkbox (display has no scale)
    mapFormTypeToBackendType(formType) {
      const mapping = {
        'text': 'text',
        'textarea': 'textarea',
        'number': 'number',
        'range': 'range',
        'radio': 'one choice',
        'checkbox': 'multiple choice',
        'display': 'display'
      };
      return mapping[formType] || 'text';
    },

    addQuestion() {
      this.formData.questions.push({
        text: '',
        imageUrl: '',
        videoUrl: '',
        type: '',
        mandatory: false,
        randomizationGroupId: null,
        randomizeAnswers: false,
        includeOtherOption: false,
        otherOptionLabel: '',
        itemGroup: null,
        useTimer: false,
        timerSeconds: 30,
        showCountdown: true,
        useJump: false,
        jumpCondition: '',
        jumpOver: 1,
        placeholder: '',
        answers: [],
        config: {
          minValue: 0,
          maxValue: 10,
          steps: 1,
          maxDigits: null
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
      this.saveAttempted = true;
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
          } else if (q.type === 'number') {
            config.min = q.config.minValue;
            config.max = q.config.maxValue;
            if (q.config.maxDigits) {
              config.maxDigits = q.config.maxDigits;
            }
          } else if (q.type === 'radio' || q.type === 'checkbox') {
            config.options = q.answers.filter(a => a.trim());
            // Include "Other" text field option flag and custom label
            if (q.includeOtherOption) {
              config.includeOtherOption = true;
              if (q.otherOptionLabel) {
                config.otherOptionLabel = q.otherOptionLabel;
              }
            }
          } else if (q.type === 'text' || q.type === 'textarea') {
            // Add placeholder for text/textarea types
            if (q.placeholder) {
              config.placeholder = q.placeholder;
            }
          }

          // Add timer config if enabled
          if (q.useTimer) {
            config.timer = {
              time: q.timerSeconds,
              showCountdown: q.showCountdown
            };
          }

          // Add jump config if enabled (only for radio/checkbox)
          // Convert jumpCondition from 1-based (UI) to 0-based (API)
          if (q.useJump && (q.type === 'radio' || q.type === 'checkbox')) {
            config.jump = {
              jumpCondition: String(q.jumpCondition - 1),
              jumpOver: q.jumpOver
            };
          }

          const questionData = {
            text: q.text,
            image_url: q.imageUrl || null,
            video_url: q.videoUrl || null,
            type: backendType,
            mandatory: q.mandatory,
            config: config,
            randomizationGroupId: q.randomizationGroupId || null,
            randomizeAnswers: q.randomizeAnswers || false,
            noValueAllowed: !q.mandatory,
            item_group: q.itemGroup || null
          };

          // Include UUID for updates
          if (this.isEditMode && q.uuid) {
            questionData.uuid = q.uuid;
          }

          return questionData;
        });

        // Build show_after_repeating object if enabled
        const showAfterRepeating = this.formData.use_show_after_repeating && this.formData.show_after_repeating_quest_id
          ? {
              repeatingQuestId: this.formData.show_after_repeating_quest_id,
              showAfterAmount: this.formData.show_after_amount || 1
            }
          : null;

        // Build payload without UI-only fields
        const { use_show_after_repeating, show_after_repeating_quest_id, show_after_amount, ...formDataWithoutUiFields } = this.formData;
        const payload = {
          ...formDataWithoutUiFields,
          show_after_repeating: showAfterRepeating,
          questions: processedQuestions
        };

        if (this.isEditMode) {
          // Update questions, settings, and introductory text
          await window.axios.put(`/questionnaires/${this.schedule.id}/questions`, payload);
        } else {
          // Create new questionnaire
          await window.axios.post(`/projects/${this.projectId}/questionnaires`, payload);
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

        emitter.emit('show-snackbar', errorMessage);
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

    async useProjectDates() {
      try {
        // Fetch project data including MART config
        const response = await window.axios.get(`/projects/${this.projectId}`);
        const project = response.data;

        // Check if project has MART config with dates
        if (project.martConfig && project.martConfig.projectOptions) {
          const projectOptions = project.martConfig.projectOptions;

          // Set start date/time from project
          if (projectOptions.startDateAndTime) {
            this.formData.start_date_time = {
              date: projectOptions.startDateAndTime.date || '',
              time: projectOptions.startDateAndTime.time || '09:00'
            };
          }

          // Set end date/time from project (only for repeating questionnaires)
          if (this.formData.type === 'repeating' && projectOptions.endDateAndTime) {
            this.formData.end_date_time = {
              date: projectOptions.endDateAndTime.date || '',
              time: projectOptions.endDateAndTime.time || '21:00'
            };
          }

          emitter.emit('show-snackbar', this.trans('Project dates applied successfully'));
        } else {
          emitter.emit('show-snackbar', this.trans('No project dates found'));
        }
      } catch (error) {
        console.error('Error fetching project dates:', error);
        emitter.emit('show-snackbar', this.trans('Failed to load project dates'));
      }
    },

    close() {
      this.$emit('close');
    }
  }
};
</script>