<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="mart-questions-title">
    <div class="fixed inset-0 z-40 bg-black/50" @click="$emit('close')"></div>

    <div class="relative z-50 flex min-h-full items-center justify-center p-4">
      <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col rounded-lg bg-white shadow-xl">
        <div class="flex shrink-0 items-start justify-between border-b border-gray-200 px-6 py-4">
          <div>
            <h3 id="mart-questions-title" class="text-lg font-medium text-gray-900">Questions and answer values</h3>
            <p class="mt-1 text-sm text-gray-500">
              Answers are stored as the values shown here, on the case page and in the Excel export (sheet "Questions").
            </p>
          </div>
          <button
            type="button"
            aria-label="Close"
            class="text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
            @click="$emit('close')"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-4 text-sm text-gray-900">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"></div>
          </div>

          <p v-else-if="error" class="rounded-md border border-red-200 bg-red-50 p-3 text-red-800">
            The questions could not be loaded.
          </p>

          <p v-else-if="questionnaires.length === 0" class="py-8 text-center text-gray-500">
            This project has no questionnaires yet.
          </p>

          <template v-else>
          <section v-for="questionnaire in questionnaires" :key="questionnaire.questionnaire_id" class="mb-6 last:mb-0">
            <h4 class="mb-2 font-semibold text-gray-900">
              {{ questionnaire.name }}
              <span class="ml-1 text-xs font-normal text-gray-500">ID {{ questionnaire.questionnaire_id }}</span>
            </h4>

            <ol class="divide-y divide-gray-100 rounded-md border border-gray-200">
              <li v-for="question in questionnaire.questions" :key="question.item" data-question class="px-3 py-2">
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                  <span class="w-6 shrink-0 text-xs text-gray-400">{{ question.item }}</span>
                  <span class="min-w-0 flex-1 break-words font-medium">{{ question.text }}</span>
                  <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700">{{ question.type_label }}</span>
                  <span v-if="isOptional(question)" class="rounded bg-blue-50 px-1.5 py-0.5 text-xs text-blue-700">Optional</span>
                </div>

                <ul v-if="question.values.length" class="ml-8 mt-1 space-y-0.5">
                  <li v-for="option in question.values" :key="option.value" data-value>
                    <span class="inline-block min-w-[2rem] font-mono text-xs text-gray-500">{{ option.value }}</span> {{ optionLabel(option) }}
                  </li>
                </ul>
                <p v-else class="ml-8 mt-1 text-gray-600">{{ question.answer }}</p>

                <p v-if="question.version > 1" class="ml-8 mt-1 text-xs text-amber-700">
                  Edited (version {{ question.version }}): earlier answers may use the earlier options.
                </p>
              </li>
            </ol>
          </section>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * The MART project's questions with the values their answers are stored as
 * (MartCodebook), read-only. MART only: MeTag questions are in the project.
 */
export default {
  name: 'MartQuestionsModal',
  props: {
    projectId: { type: [Number, String], required: true },
  },
  emits: ['close'],
  data() {
    return { loading: true, error: false, questionnaires: [] };
  },
  async mounted() {
    try {
      const response = await window.axios.get(`/projects/${this.projectId}/questionnaires/codebook`);
      this.questionnaires = response.data.questionnaires || [];
    } catch (e) {
      this.error = true;
    } finally {
      this.loading = false;
    }
  },
  methods: {
    isOptional(question) {
      return !question.required && question.type !== 'display';
    },
    optionLabel(option) {
      return option.value === 'text' ? `${option.label}, followed by the typed answer` : option.label;
    },
  },
};
</script>
