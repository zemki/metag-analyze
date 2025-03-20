<template>
  <div class="p-8 space-y-8 bg-white rounded-lg shadow-lg">
    <!-- Header -->
    <div class="pb-6 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">{{ trans('Edit Project') }}</h3>
      <p class="mt-2 text-sm text-gray-600">{{ trans('You can edit your project details here.') }}</p>
    </div>

    <!-- Project Name -->
    <div class="space-y-2">
      <label for="name" class="block text-sm font-medium text-gray-700">{{ trans('Project Name') }} *</label>
      <input
          type="text"
          :disabled="!editable"
          id="name"
          v-model="projectData.name"
          class="block w-full px-4 py-3 rounded-md shadow-sm transition duration-150"
          :class="[
          editable
            ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
            : 'bg-gray-50 border-gray-200',
          {'border-red-500': projectData.name.trim() === '' && editable}
        ]"
          placeholder="Enter project name"
      />
      <p v-if="projectData.name.trim() === '' && editable" class="text-sm text-red-600">
        {{ trans('Project name is required') }}
      </p>
    </div>

    <!-- Project Description -->
    <div class="space-y-2">
      <label for="description" class="block text-sm font-medium text-gray-700">{{ trans('Description') }} *</label>
      <textarea
          :disabled="!editable"
          id="description"
          v-model="projectData.description"
          rows="4"
          class="block w-full px-4 py-3 rounded-md shadow-sm transition duration-150"
          :class="[
          editable
            ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
            : 'bg-gray-50 border-gray-200',
          {'border-red-500': projectData.description.trim() === '' && editable}
        ]"
          placeholder="Enter project description"
      ></textarea>
      <p v-if="projectData.description.trim() === '' && editable" class="text-sm text-red-600">
        {{ trans('Project description is required') }}
      </p>
    </div>

    <!-- Media Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">{{ trans('Media') }}</label>
      </div>

      <div class="space-y-3">
        <!-- Media Inputs -->
        <div v-for="(media, index) in projectData.media" :key="index"
             class="flex items-center space-x-2">
          <input
              type="text"
              v-model="projectData.media[index]"
              :disabled="!editable"
              class="flex-1 px-4 py-3 rounded-md shadow-sm transition duration-150"
              :class="editable ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200' : 'bg-gray-50 border-gray-200'"
              placeholder="Enter media"
          />
          <button
              v-if="editable && projectData.media.length > 1"
              @click="removeMedia(index)"
              class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors duration-150"
              aria-label="Remove Media"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Add Media Button -->
        <button
            v-if="editable"
            @click="addMedia"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-500 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors duration-150"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ trans('Add Media') }}
        </button>
      </div>
    </div>

    <!-- Inputs Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">{{ trans('Number of Inputs') }} (0-3)</label>
        <div class="flex items-center space-x-2 bg-gray-100 rounded-lg p-1">
          <button
              :disabled="!editable || projectData.inputs.length <= 0"
              @click="decrementInputs"
              class="w-8 h-8 flex items-center justify-center rounded-md transition-colors duration-150"
              :class="editable && projectData.inputs.length > 0
              ? 'bg-blue-500 text-white hover:bg-blue-600'
              : 'bg-gray-200 text-gray-400'"
          >
            &minus;
          </button>
          <span class="w-8 text-center font-medium">{{ projectData.inputs.length }}</span>
          <button
              :disabled="!editable || projectData.inputs.length >= 3"
              @click="incrementInputs"
              class="w-8 h-8 flex items-center justify-center rounded-md transition-colors duration-150"
              :class="editable && projectData.inputs.length < 3
              ? 'bg-blue-500 text-white hover:bg-blue-600'
              : 'bg-gray-200 text-gray-400'"
          >
            &plus;
          </button>
        </div>
      </div>

      <!-- Dynamic Inputs -->
      <div v-for="(input, index) in projectData.inputs" :key="index"
           class="p-6 border border-gray-200 rounded-lg space-y-4 bg-gray-50">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ trans('Input Name') }} *</label>
            <input
                type="text"
                v-model="input.name"
                :disabled="!editable"
                class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm transition duration-150"
                :class="[
                editable
                  ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
                  : 'bg-gray-100 border-gray-200',
                {'border-red-500': input.name.trim() === '' && editable}
              ]"
                placeholder="Enter input name"
            />
          </div>
          <div class="flex items-center">
            <input
                type="checkbox"
                :id="'mandatory-' + index"
                v-model="input.mandatory"
                :disabled="!editable"
                class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
            />
            <label :for="'mandatory-' + index" class="ml-2 block text-sm text-gray-700">
              {{ trans('Required field for users') }}
            </label>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ trans('Type') }} *</label>
            <select
                v-model="input.type"
                :disabled="!editable"
                class="mt-1 block w-full px-4 py-3 rounded-md shadow-sm transition duration-150"
                :class="[
                editable
                  ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
                  : 'bg-gray-100 border-gray-200',
                {'border-red-500': input.type.trim() === '' && editable}
              ]"
            >
              <option disabled value="">{{ trans('Select Type') }}</option>
              <option v-for="type in config.available" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>

          <!-- Answers for choice types -->
          <div v-if="isChoiceType(input.type)" class="space-y-3">
            <label class="block text-sm font-medium text-gray-700">{{ trans('Answers') }}</label>
            <div v-for="(answer, aIndex) in input.answers" :key="aIndex"
                 class="flex items-center space-x-2">
              <input
                  type="text"
                  v-model="input.answers[aIndex]"
                  @keyup="handleAdditionalInputs(index, aIndex, answer)"
                  :disabled="!editable"
                  class="flex-1 px-4 py-3 rounded-md shadow-sm transition duration-150"
                  :class="editable ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200' : 'bg-gray-100 border-gray-200'"
                  placeholder="Enter answer"
              />
              <button
                  v-if="editable && input.answers.length > 1"
                  @click="removeAnswer(index, aIndex)"
                  class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors duration-150"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <button
                v-if="editable"
                @click="addAnswer(index)"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-500 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors duration-150"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ trans('Add Answer') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
      <button
          @click="toggleEditMode"
          class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-150"
      >
        {{ editable ? trans('Cancel') : trans('Edit Project') }}
      </button>
      <button
          @click="save"
          :disabled="!editable || isLoading"
          class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
      >
        <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ trans('Save Changes') }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditProject',

  props: {
    editable: {
      type: Boolean,
      default: false
    },
    project: {
      type: Object,
      required: true
    },
    config: {
      type: Object,
      required: true
    },
    projectmedia: {
      type: Array,
      default: () => []
    }
  },
  
  emits: ['update:editable'],

  data() {
    return {
      isLoading: false,
      response: "",
      projectData: {
        name: "",
        description: "",
        inputs: [],
        media: [],
      },
    };
  },

  watch: {
    project: {
      immediate: true,
      handler() {
        this.initializeProjectData();
      },
    },
  },

  methods: {
    trans(key) {
      // Translation helper
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      } else {
        if (window.trans[key] === "") return key;
        return window.trans[key];
      }
    },
    
    initializeProjectData() {
      // Basic project info
      this.projectData.name = this.project.name || '';
      this.projectData.description = this.project.description || '';

      // Initialize inputs
      try {
        const inputsData = JSON.parse(this.project.inputs || '[]');
        this.projectData.inputs = Array.isArray(inputsData) ? inputsData.map(input => ({
          name: input.name || '',
          type: input.type || '',
          mandatory: input.mandatory !== undefined ? input.mandatory : true,
          answers: Array.isArray(input.answers) && input.answers.length > 0 ?
              input.answers.filter(answer => answer.trim() !== '') :
              ['']
        })) : [];
      } catch (error) {
        console.error('Error parsing inputs:', error);
        this.projectData.inputs = [];
      }

      // Initialize media
      this.projectData.media = Array.isArray(this.projectmedia) && this.projectmedia.length > 0 ?
          [...this.projectmedia] :
          [''];  // Start with one empty media input
    },

    // Input Management
    incrementInputs() {
      if (this.projectData.inputs.length < 3) {
        this.projectData.inputs.push({
          name: "",
          type: "",
          mandatory: true,
          answers: [""]
        });
      }
    },

    decrementInputs() {
      if (this.projectData.inputs.length > 0) {
        this.projectData.inputs.pop();
      }
    },

    addMedia() {
      const lastMedia = this.projectData.media[this.projectData.media.length - 1];
      if (lastMedia.trim() !== '') {
        this.projectData.media.push('');
      } else {
        this.showSnackbarMessage(this.trans('Please fill out the last media field before adding a new one.'));
      }
    },

    handleMediaInputs(index, media) {
      if (media.trim() === '' && index !== this.projectData.media.length - 1) {
        this.projectData.media.splice(index, 1);
      }
    },

    removeMedia(index) {
      if (this.projectData.media.length > 1) {
        this.projectData.media.splice(index, 1);
      }
    },

    // Answer Management
    handleAdditionalInputs(questionIndex, answerIndex, answer) {
      const answers = this.projectData.inputs[questionIndex].answers;
      const isLast = answerIndex === answers.length - 1;
      const isEmpty = answer.trim() === "";

      if (isLast && !isEmpty) {
        answers.push("");
      } else if (!isLast && isEmpty) {
        answers.splice(answerIndex, 1);
      }
    },

    addAnswer(index) {
      const answers = this.projectData.inputs[index].answers;
      const lastAnswer = answers[answers.length - 1];

      if (lastAnswer.trim() !== '') {
        answers.push("");
      } else {
        this.showSnackbarMessage(this.trans('Please fill out the last answer before adding a new one.'));
      }
    },

    removeAnswer(questionIndex, answerIndex) {
      const answers = this.projectData.inputs[questionIndex].answers;
      if (answers.length > 1) {
        answers.splice(answerIndex, 1);
      }
    },

    // Utility Methods
    isChoiceType(type) {
      return ['multiple choice', 'one choice'].includes(type);
    },

    showSnackbarMessage(message) {
      this.$root.showSnackbarMessage(message);
    },

    // Edit Mode Management
    toggleEditMode() {
      if (this.editable) {
        this.initializeProjectData();
        this.response = "";
      }
      this.$emit('update:editable', !this.editable);
    },

    // Save Project
    validateProjectData() {
      if (!this.projectData.name.trim()) return false;
      if (!this.projectData.description.trim()) return false;

      for (const input of this.projectData.inputs) {
        if (!input.name.trim() || !input.type) return false;
        if (this.isChoiceType(input.type) &&
            (!input.answers.length || !input.answers.some(a => a.trim()))) {
          return false;
        }
      }

      return true;
    },

    async save() {
      if (!this.validateProjectData()) {
        this.showSnackbarMessage(this.trans('Please fill in all required fields.'));
        return;
      }

      this.isLoading = true;

      try {
        const submitData = {
          id: this.project.id,
          name: this.projectData.name.trim(),
          description: this.projectData.description.trim(),
          inputs: this.projectData.inputs.map(input => ({
            ...input,
            answers: input.answers.filter(a => a.trim() !== '')
          })),
          media: this.projectData.media.filter(media => media.trim() !== ""),
        };

        const response = await window.axios.patch(this.productionUrl+`/projects/${submitData.id}`, submitData);
        this.showSnackbarMessage(response.data.message || this.trans('Project updated successfully.'));

      } catch (error) {
        this.response = error.response?.data?.message || this.trans('An error occurred while saving.');
        this.showSnackbarMessage(this.response);

      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>
