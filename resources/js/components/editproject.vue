<template>
  <div class="p-6 space-y-6 bg-white">
    <!-- Header -->
    <div class="pb-6 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold text-gray-900">{{ trans('Edit Project') }}</h3>
          <p class="mt-2 text-sm text-gray-600">{{ trans('You can edit your project details here.') }}</p>
        </div>
        <div v-if="isMartProject" class="flex items-center">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            MART Project
          </span>
        </div>
      </div>
    </div>

    <!-- Editing Disabled Warning -->
    <div v-if="!editable" class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
      <div class="flex">
        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.768 0L3.046 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div>
          <h4 class="text-sm font-medium text-yellow-800 mb-1">{{ trans('Project Editing Disabled') }}</h4>
          <p class="text-sm text-yellow-700">
            {{ trans('This project cannot be edited because it has existing cases with data. To maintain data integrity and prevent inconsistencies, project structure is locked once cases are created.') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Project Name -->
    <div class="space-y-2">
      <label for="name" class="block text-sm font-medium text-gray-700">{{ trans('Project Name') }} *</label>
      <input
          type="text"
          :disabled="!editable"
          id="name"
          v-model="projectData.name"
          class="block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
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
          class="block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
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

    <!-- Entity Name Field -->
    <div class="space-y-2" v-if="!isLegacyProject && !isMartProject">
      <label for="entityName" class="block text-sm font-medium text-gray-700">{{ trans('Entity Field Name') }}</label>
      <input
          type="text"
          :disabled="!editable"
          id="entityName"
          v-model="projectData.entityName"
          class="block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
          :class="editable ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200' : 'bg-gray-50 border-gray-200'"
          placeholder="Enter name for entity field (default: 'entity')"
      />
      <p class="text-xs text-gray-500">{{ trans('This name will be used in the mobile app. Default is \'entity\' if left empty.') }}</p>
    </div>

    <!-- Entity/Media Section -->
    <div class="space-y-4" v-if="!isMartProject">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <label class="block text-sm font-medium text-gray-700">{{ isLegacyProject ? trans('Media') : (projectData.entityName || trans('Entity')) }}</label>
          <div class="ml-4 flex items-center" v-if="!isLegacyProject">
            <input
                type="checkbox"
                id="useEntity"
                v-model="projectData.useEntity"
                :disabled="!editable"
                class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
            />
            <label for="useEntity" class="ml-2 block text-sm text-gray-700">
              {{ trans('Include this field in the project') }}
            </label>
          </div>
        </div>
      </div>

      <!-- Show media input section ONLY if useEntity is true OR if it's a legacy project -->
      <div v-if="projectData.useEntity || isLegacyProject" class="space-y-3">
        <!-- Entity/Media Inputs -->
        <div v-for="(media, index) in projectData.media" :key="index"
             class="flex items-center space-x-2">
          <input
              type="text"
              v-model="projectData.media[index]"
              :disabled="!editable"
              class="flex-1 px-4 py-3 rounded-md shadow-xs transition duration-150"
              :class="editable ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200' : 'bg-gray-50 border-gray-200'"
              :placeholder="isLegacyProject ? trans('Enter media') : `${trans('Enter')} ${projectData.entityName || trans('entity')}`"
          />
          <button
              v-if="editable && projectData.media.length > 1"
              @click="removeMedia(index)"
              class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors duration-150"
              :aria-label="isLegacyProject ? 'Remove Media' : `${trans('Remove')} ${projectData.entityName || trans('Entity')}`"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Add Button -->
        <button
            v-if="editable"
            @click="addMedia"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-500 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors duration-150"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ isLegacyProject ? trans('Add Media') : `${trans('Add')} ${projectData.entityName || trans('Entity')}` }}
        </button>
      </div>
      <!-- Text below is shown when the entity field is disabled -->
      <p v-else-if="!isLegacyProject" class="text-xs text-gray-500">
        {{ trans('This field won\'t be included in the mobile app.') }}
      </p>
    </div>

    <!-- MART Project Sections -->
    <div v-if="isMartProject" class="space-y-6">
      <!-- Questionnaires Section -->
      <div class="pt-8 space-y-6">
        <div class="pb-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ trans('Questionnaires') }}</h3>
          <p class="mt-2 text-sm text-gray-600">
            {{ trans('Manage questionnaires with unique questions for each questionnaire. Questions are always editable with automatic version tracking.') }}
          </p>
        </div>

        <MartQuestionnaireManager
            :project-id="project.id"
            :editable="editable"
        />
      </div>

      <!-- MART Pages Section -->
      <div class="pt-8 space-y-6">
        <div class="pb-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ trans('Instruction Pages') }}</h3>
          <p class="mt-2 text-sm text-gray-600">{{ trans('Manage instruction pages with HTML content shown in the mobile app.') }}</p>
        </div>

        <!-- Pages List -->
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <h4 class="text-md font-medium text-gray-900">{{ trans('Pages') }}</h4>
            <button
                v-if="editable"
                type="button"
                @click="addMartPage"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              {{ trans('Add Page') }}
            </button>
          </div>

          <!-- Page Items -->
          <div v-if="projectData.pages.length === 0" class="text-center py-8 text-gray-500">
            {{ trans('No pages added yet. Click "Add Page" to get started.') }}
          </div>

          <div v-for="(page, index) in projectData.pages" :key="index" class="border border-gray-300 rounded-lg p-4 space-y-4">
            <!-- Page Header -->
            <div class="flex justify-between items-start">
              <span class="text-sm font-medium text-gray-700">{{ trans('Page') }} {{ index + 1 }}</span>
              <button
                  v-if="editable"
                  type="button"
                  @click="removeMartPage(index)"
                  class="text-red-600 hover:text-red-900"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>

            <!-- Page Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ trans('Page Name') }} *</label>
              <input
                  type="text"
                  v-model="page.name"
                  :disabled="!editable"
                  class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
                  :class="[
                  editable
                    ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
                    : 'bg-gray-100 border-gray-200',
                  {'border-red-500': page.name?.trim() === '' && editable}
                ]"
                  placeholder="Enter page name"
              />
            </div>

            <!-- Page Content -->
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ trans('Page Content (HTML)') }} *</label>
              <textarea
                  v-model="page.content"
                  :disabled="!editable"
                  rows="6"
                  class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
                  :class="[
                  editable
                    ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
                    : 'bg-gray-100 border-gray-200',
                  {'border-red-500': page.content?.trim() === '' && editable}
                ]"
                  placeholder="Enter HTML content for the page"
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">{{ trans('You can use HTML tags for formatting (e.g., &lt;h1&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;br&gt;, etc.)') }}</p>
            </div>

            <!-- Button Text -->
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ trans('Button Text') }} *</label>
              <input
                  type="text"
                  v-model="page.buttonText"
                  :disabled="!editable"
                  class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
                  :class="[
                  editable
                    ? 'border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200'
                    : 'bg-gray-100 border-gray-200',
                  {'border-red-500': page.buttonText?.trim() === '' && editable}
                ]"
                  placeholder="Continue"
              />
            </div>

            <!-- Show on First App Start -->
            <div class="flex items-center">
              <input
                  type="checkbox"
                  :id="'page-first-start-' + index"
                  v-model="page.showOnFirstAppStart"
                  :disabled="!editable"
                  class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
              />
              <label :for="'page-first-start-' + index" class="ml-2 block text-sm text-gray-700">
                {{ trans('Show this page on first app start') }}
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Standard Inputs Section -->
    <div v-else class="space-y-4">
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

      <!-- Standard Dynamic Inputs -->
      <div v-for="(input, index) in projectData.inputs" :key="index"
           class="p-6 border border-gray-200 rounded-lg space-y-4 bg-gray-50">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ trans('Input Name') }} *</label>
            <input
                type="text"
                v-model="input.name"
                :disabled="!editable"
                class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
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
                class="mt-1 block w-full px-4 py-3 rounded-md shadow-xs transition duration-150"
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
                  class="flex-1 px-4 py-3 rounded-md shadow-xs transition duration-150"
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

    <!-- Error Message Display -->
    <div v-if="response" class="p-4 bg-red-50 border border-red-200 rounded-md">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ response }}</p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button @click="response = ''" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons (Optional) -->
    <div v-if="showButtons" class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
      <button
          @click="toggleEditMode"
          class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-150"
      >
        {{ editable ? trans('Cancel') : trans('Edit Project') }}
      </button>
      <button
          v-if="editable"
          @click="save(false)"
          :disabled="isLoading"
          class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
      >
        <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ trans('Save') }}
      </button>
      <button
          v-if="editable"
          @click="save(true)"
          :disabled="isLoading"
          class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
      >
        <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ trans('Save and Close') }}
      </button>
    </div>

  </div>
</template>

<script>
import MartQuestionnaireManager from './mart/MartQuestionnaireManager.vue';
import { emitter } from '../app.js';

export default {
  name: 'EditProject',

  components: {
    MartQuestionnaireManager
  },

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
    },
    showButtons: {
      type: Boolean,
      default: true
    }
  },

  emits: ['update:editable', 'project-updated'],

  data() {
    return {
      isLoading: false,
      response: "",
      isLegacyProject: false,
      cutoffDate: '2025-03-21', // Default cutoff date if not available from config
      projectData: {
        name: "",
        description: "",
        entityName: "entity",
        useEntity: true,
        inputs: [],
        media: [],
        questionnaireName: "",
        pages: [],
      },
    };
  },

  computed: {
    isMartProject() {
      try {
        const inputsData = JSON.parse(this.project.inputs || '[]');
        return Array.isArray(inputsData) && 
          inputsData.length > 0 && 
          inputsData[0].type === 'mart';
      } catch (error) {
        return false;
      }
    }
  },

  watch: {
    project: {
      immediate: true,
      handler() {
        this.initializeProjectData();
      },
    },
    'projectData.useEntity': function(newVal) {
      // When useEntity changes, update media array accordingly
      if (!this.isLegacyProject) {
        if (newVal === false) {
          // Clear media when useEntity is set to false
          this.projectData.media = [];
        } else if (this.projectData.media.length === 0) {
          // Initialize with one empty input when useEntity is set to true
          this.projectData.media = [''];
        }
      }
    }
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
      // Check if it's a legacy project (created before cutoff date)
      if (this.project.created_at) {
        const projectDate = new Date(this.project.created_at);
        const cutoffDate = new Date(this.cutoffDate);
        this.isLegacyProject = projectDate < cutoffDate;
      }

      // Basic project info
      this.projectData.name = this.project.name || '';
      this.projectData.description = this.project.description || '';
      this.projectData.entityName = this.project.entity_name || (this.isLegacyProject ? 'media' : 'entity');

      // Handle use_entity - convert database value (1/0) to boolean
      if (this.isLegacyProject) {
        this.projectData.useEntity = true; // Legacy projects always use entity
      } else {
        // For new projects, convert database value (1/0/true/false/null) to boolean
        const useEntityValue = this.project.use_entity;
        if (useEntityValue === null || useEntityValue === undefined) {
          this.projectData.useEntity = true; // Default to true for null/undefined
        } else {
          // Convert 1/0 or true/false to boolean
          this.projectData.useEntity = Boolean(Number(useEntityValue));
        }
      }

      // Initialize inputs
      try {
        const inputsData = JSON.parse(this.project.inputs || '[]');
        
        // Check if this is a MART project
        const isMartProject = Array.isArray(inputsData) && 
          inputsData.length > 0 && 
          inputsData[0].type === 'mart';
          
        if (isMartProject) {
          // For MART projects, extract config and questions
          const martConfig = inputsData.find(input => input.type === 'mart');
          const martQuestions = inputsData.filter(input => input.type !== 'mart');
          
          // Extract questionnaire name from MART config
          this.projectData.questionnaireName = martConfig?.questionnaireName || '';
          
          // Extract pages from MART config
          this.projectData.pages = martConfig?.projectOptions?.pages || [];
          
          this.projectData.inputs = martQuestions.map(input => {
            const inputObj = {
              name: input.name || '',
              type: this.mapMetagTypeToMartType(input.type) || input.martMetadata?.originalType || input.type || '',
              mandatory: input.mandatory !== undefined ? input.mandatory : true,
            };
            

            // Handle MART-specific properties based on the MART type
            const martType = inputObj.type;
            
            if (martType === 'range') {
              inputObj.minValue = input.martMetadata?.minValue || input.minValue || 0;
              inputObj.maxValue = input.martMetadata?.maxValue || input.maxValue || 10;
              inputObj.steps = input.martMetadata?.steps || input.steps || 1;
            }
            
            if (martType === 'radio' || martType === 'checkbox') {
              // For MART choice types, convert from legacy answers to options structure
              if (Array.isArray(input.answers) && input.answers.length > 0) {
                inputObj.options = input.answers.map((answer, idx) => ({ text: answer, value: idx }));
              } else {
                inputObj.options = [{ text: '', value: 0 }];
              }
            } else if (this.isChoiceType(input.type)) {
              // For legacy MetaG choice types, keep answers structure
              inputObj.answers = Array.isArray(input.answers) && input.answers.some(a => a.trim() !== '')
                ? input.answers.filter(a => a.trim() !== '')
                : [''];
            } else {
              // For non-choice types, initialize an empty array
              inputObj.answers = [];
            }

            return inputObj;
          });
        } else {
          // For standard projects, process all inputs as before
          this.projectData.inputs = Array.isArray(inputsData) ? inputsData.map(input => {
            const inputObj = {
              name: input.name || '',
              type: input.type || '',
              mandatory: input.mandatory !== undefined ? input.mandatory : true,
            };

            // Handle answers based on input type
            if (this.isChoiceType(input.type)) {
              // For choice types, ensure we have at least one empty answer if none exist
              inputObj.answers = Array.isArray(input.answers) && input.answers.some(a => a.trim() !== '')
                ? input.answers.filter(a => a.trim() !== '')
                : [''];
            } else {
              // For non-choice types, initialize an empty array
              inputObj.answers = [];
            }

            return inputObj;
          }) : [];
        }
      } catch (error) {
        console.error('Error parsing inputs:', error);
        this.projectData.inputs = [];
      }

      // Initialize media based on useEntity
      if (this.isLegacyProject || this.projectData.useEntity) {
        // Only include media if useEntity is true or it's a legacy project
        this.projectData.media = Array.isArray(this.projectmedia) && this.projectmedia.length > 0 ?
            [...this.projectmedia] :
            [''];  // Start with one empty media input
      } else {
        // Clear media when useEntity is false
        this.projectData.media = [];
      }
    },

    // Input Management
    incrementInputs() {
      if (this.projectData.inputs.length < 3) {
        this.projectData.inputs.push({
          name: "",
          type: "",
          mandatory: true,
          answers: [""] // Initialize with empty answer for all input types
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

    // MART Page Management Methods
    addMartPage() {
      this.projectData.pages.push({
        name: '',
        content: '',
        buttonText: 'Continue',
        showOnFirstAppStart: false
      });
    },

    removeMartPage(index) {
      this.projectData.pages.splice(index, 1);
    },

    // Utility Methods
    isChoiceType(type) {
      return ['multiple choice', 'one choice'].includes(type);
    },

    isMartChoiceType(type) {
      return ['radio', 'checkbox'].includes(type);
    },

    // Map MetaG types back to MART types for editing
    mapMetagTypeToMartType(metagType) {
      const reverseMapping = {
        'text': 'text',
        'scale': 'range', // Default scale to range, but martMetadata.originalType takes precedence
        'one choice': 'radio',
        'multiple choice': 'checkbox'
      };
      
      return reverseMapping[metagType] || 'text';
    },

    // Map MART types to MetaG types for saving (same as createproject.vue)
    mapMartTypeToMetagType(martType) {
      const mapping = {
        'text': 'text',
        'textarea': 'text',
        'number': 'scale',
        'range': 'scale',
        'radio': 'one choice',
        'checkbox': 'multiple choice'
      };
      
      return mapping[martType] || 'text';
    },

    showSnackbarMessage(message) {
      emitter.emit('show-snackbar', message);
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

      // MART project specific validation
      if (this.isMartProject) {
        for (const input of this.projectData.inputs) {
          if (!input.name.trim() || !input.type) return false;

          // Validate MART choice types
          if (this.isMartChoiceType(input.type)) {
            if (!Array.isArray(input.options) || input.options.length < 2) {
              return false;
            }
            if (!input.options.every(opt => opt.text && opt.text.trim() !== '')) {
              return false;
            }
          }
          
          // Validate range type
          if (input.type === 'range') {
            if (input.minValue >= input.maxValue) return false;
          }
        }
        
        // Validate pages
        for (const page of this.projectData.pages) {
          if (!page.name?.trim() || !page.content?.trim() || !page.buttonText?.trim()) {
            return false;
          }
        }
      } else {
        // Standard project validation
        for (const input of this.projectData.inputs) {
          if (!input.name.trim() || !input.type) return false;

          // For choice types, validate that there's at least one non-empty answer
          if (this.isChoiceType(input.type)) {
            // Make sure answers is an array
            if (!Array.isArray(input.answers)) {
              input.answers = [''];
              return false;
            }

            // Make sure there's at least one non-empty answer
            if (!input.answers.some(a => a && a.trim() !== '')) {
              return false;
            }
          } else {
            // For non-choice types, ensure answers is an empty array
            if (!Array.isArray(input.answers)) {
              input.answers = [];
            }
          }
        }
      }

      return true;
    },

    async save(closeAfterSave = false) {
      if (!this.validateProjectData()) {
        this.showSnackbarMessage(this.trans('Please fill in all required fields.'));
        return;
      }

      this.isLoading = true;

      try {
        const submitData = {
          id: this.project.id,
          name: this.projectData.name.trim(),
          description: this.projectData.description.trim()
        };

        // Always include entityName and useEntity for compatibility
        submitData.entityName = this.projectData.entityName?.trim() || 'entity';
        submitData.useEntity = this.projectData.useEntity;

        // Handle inputs differently for MART vs standard projects
        const originalInputsData = JSON.parse(this.project.inputs || '[]');
        const isMartProject = Array.isArray(originalInputsData) && 
          originalInputsData.length > 0 && 
          originalInputsData[0].type === 'mart';

        if (isMartProject) {
          // For MART projects, preserve the MART config and update only the questions
          const martConfig = originalInputsData.find(input => input.type === 'mart');
          
          // Update questionnaire name and pages in MART config
          if (martConfig) {
            martConfig.questionnaireName = this.projectData.questionnaireName;
            
            // Update pages in projectOptions
            if (!martConfig.projectOptions) {
              martConfig.projectOptions = {};
            }
            martConfig.projectOptions.pages = this.projectData.pages.map((page, index) => ({
              name: page.name,
              content: page.content,
              showOnFirstAppStart: page.showOnFirstAppStart,
              buttonText: page.buttonText,
              sortOrder: index
            }));
          }
          
          const processedQuestions = this.projectData.inputs.map(input => {
            const processedInput = { ...input };

            // Convert MART type back to MetaG type for storage
            processedInput.type = this.mapMartTypeToMetagType(input.type);

            // Handle MART choice types with options
            if (this.isMartChoiceType(input.type) && Array.isArray(input.options)) {
              // Convert options back to answers array for MetaG compatibility
              processedInput.answers = input.options
                .filter(opt => opt.text && opt.text.trim() !== '')
                .map(opt => opt.text.trim());
              processedInput.numberofanswer = processedInput.answers.length;
              delete processedInput.options; // Remove MART options structure
            } else if (this.isChoiceType(processedInput.type) && Array.isArray(input.answers)) {
              // For legacy choice types, filter out empty answers
              processedInput.answers = input.answers.filter(a => a && a.trim() !== '');
            } else {
              // For non-choice types, ensure clean structure
              delete processedInput.answers;
              delete processedInput.options;
            }

            // Store MART metadata for future editing
            processedInput.martMetadata = {
              originalType: input.type,
              minValue: input.minValue,
              maxValue: input.maxValue,
              steps: input.steps
            };
            
            return processedInput;
          });
          
          // Reconstruct the full inputs array with MART config + questions
          submitData.inputs = JSON.stringify([martConfig, ...processedQuestions]);
        } else {
          // For standard projects, process normally
          submitData.inputs = this.projectData.inputs.map(input => {
            // Ensure each input has an answers array
            const processedInput = { ...input };

            // If it's a choice type, filter out empty answers
            if (this.isChoiceType(input.type) && Array.isArray(input.answers)) {
              processedInput.answers = input.answers.filter(a => a && a.trim() !== '');
            } else {
              // For non-choice types, initialize an empty array
              processedInput.answers = [];
            }
            return processedInput;
          });

          // Stringify inputs array
          submitData.inputs = JSON.stringify(submitData.inputs);
        }

        // Include media based on project type
        if (this.isLegacyProject || this.projectData.useEntity) {
          submitData.media = this.projectData.media.filter(media => media.trim() !== "");
        } else {
          // When useEntity is false, don't include media at all to prevent it from being processed
          submitData.media = [];
          // Also reset the media array in the local state
          this.projectData.media = [];
        }

        const response = await window.axios.patch(this.productionUrl+`/projects/${submitData.id}`, submitData);

        // Controller returns simple string response
        this.showSnackbarMessage(response.data);

        // Emit the updated project data to parent component
        const updatedProject = {
          ...this.project,
          name: this.projectData.name.trim(),
          description: this.projectData.description.trim(),
          entity_name: this.projectData.entityName?.trim() || 'entity',
          use_entity: this.projectData.useEntity !== false ? 1 : 0,
          inputs: submitData.inputs // Already stringified
        };

        this.$emit('project-updated', updatedProject);

        // Note: Redirect is handled by parent component (ProjectCasesView) when in modal
        // This allows the modal to control the flow

      } catch (error) {
        let errorMessage = this.trans('An error occurred while saving.');

        if (error.response?.data) {
          if (error.response.data.errors) {
            // Handle validation errors
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage = errors.join(', ');
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        }


        this.showSnackbarMessage(errorMessage);

      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>
