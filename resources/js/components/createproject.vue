<template>
  <div>
    <!-- Breadcrumb Component -->
    <breadcrumb></breadcrumb>

    <!-- Header Section -->
    <div class="flex flex-col h-full">
      <div>
        <div class="my-2">
          <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
            Create a Project
          </h1>
          <p class="mt-5 text-xl text-gray-500">
            The predefined inputs are Begin Date/Time, End Date/Time, and Media used.
            You can enter up to 3 additional inputs giving them name and details,
            this will be reflected in the mobile app.
          </p>
        </div>
      </div>
    </div>

    <!-- Project Creation Form -->
    <form @submit.prevent="validateProject" class="mx-auto pt-10">
      <!-- Hidden Fields -->
      <input type="hidden" :value="userId" name="created_by">

      <div class="p-2 space-y-8 bg-top divide-y-0">
        <!-- Project Details Section -->
        <div class="space-y-8 divide-y-0">
          <!-- Project Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
            <div class="mt-1 relative">
              <input
                type="text"
                id="name"
                v-model="newProject.name"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                maxlength="200"
                placeholder="Enter project name"
              />
              <span
                :class="newProject.name.length > inputLength.name ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'"
              >
                {{ inputLength.name - newProject.name.length }}/{{ inputLength.name }}
              </span>
            </div>
          </div>

          <!-- Project Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
            <div class="mt-1 relative">
              <textarea
                id="description"
                v-model="newProject.description"
                rows="3"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                maxlength="255"
                placeholder="Enter project description"
              ></textarea>
              <span
                :class="newProject.description.length > inputLength.description ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'"
              >
                {{ inputLength.description - newProject.description.length }}/{{ inputLength.description }}
              </span>
            </div>
          </div>
        </div>

        <!-- Media Inputs Section -->
        <div>
          <label for="media" class="block text-sm font-medium text-gray-700">Media</label>
          <div class="mt-1" v-for="(singleMedia, index) in newProject.media" :key="index">
            <input
              type="text"
              v-model="newProject.media[index]"
              @keyup="handleMediaInputs(index, singleMedia)"
              autocomplete="off"
              @keydown.enter.prevent
              @keydown.tab.prevent
              class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Enter media URL"
            />
          </div>
        </div>

        <!-- Additional Inputs Count Section -->
        <div>
          <label for="ninputs" class="block text-sm font-medium text-gray-700">
            Number of additional inputs
          </label>

          <input type="hidden" :value="JSON.stringify(newProject.inputs)" name="inputs" />

          <div class="relative flex flex-row w-64 h-10 mt-1 bg-transparent rounded-lg">
            <button
              type="button"
              class="w-20 h-full text-gray-600 bg-gray-300 rounded-l outline-none cursor-pointer hover:text-gray-700 hover:bg-gray-400"
              @click="decrementInputs"
            >
              <span class="m-auto text-2xl font-thin">−</span>
            </button>
            <input
              v-model.number="newProject.ninputs"
              type="number"
              min="0"
              max="3"
              class="flex items-center w-full font-semibold text-center text-gray-700 bg-white outline-none focus:outline-none text-md hover:text-black focus:text-black md:text-base cursor-default"
              name="ninputs"
              id="ninputs"
            />
            <button
              type="button"
              class="w-20 h-full text-gray-600 bg-gray-300 rounded-r cursor-pointer hover:text-gray-700 hover:bg-gray-400"
              @click="incrementInputs"
            >
              <span class="m-auto text-2xl font-thin">+</span>
            </button>
          </div>
        </div>

        <!-- Dynamic Additional Inputs Section -->
        <div v-for="(input, index) in newProject.inputs" :key="index" class="space-y-4">
          <!-- Input Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Input Name</label>
            <div class="mt-1">
              <input
                type="text"
                v-model="input.name"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Enter input name"
              />
            </div>
          </div>

          <!-- Mandatory Checkbox -->
          <div class="relative flex items-start my-2">
            <div class="flex items-center h-5">
              <input
                type="checkbox"
                v-model="input.mandatory"
                class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500"
                :id="'mandatory-' + index"
              />
            </div>
            <div class="ml-3 text-sm">
              <label :for="'mandatory-' + index" class="font-medium text-gray-700">Mandatory</label>
            </div>
          </div>

          <!-- Input Type Dropdown -->
          <label class="block text-sm font-medium text-gray-700">Type</label>
          <div class="relative mt-1">
            <button
              type="button"
              @click="toggleDropdown(index)"
              :class="dropdownClass(input.type)"
              aria-haspopup="listbox"
            >
              <span class="block truncate">{{ input.type || 'Select Type' }}</span>
              <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <!-- Selector Icon -->
                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path
                    fill-rule="evenodd"
                    d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </button>

            <!-- Dropdown List -->
            <ul
              v-if="input.showDropdown"
              :id="'type-' + index"
              class="absolute z-10 w-full py-1 mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
              role="listbox"
              aria-labelledby="listbox-label"
            >
              <li
                v-for="(type, typeIndex) in inputs.available"
                :key="typeIndex"
                :class="selectedTypeClass(type, input.type)"
                @click="selectType(index, type)"
                role="option"
              >
                <span class="block font-normal truncate">{{ type }}</span>
                <span v-if="type === input.type" class="absolute inset-y-0 right-0 flex items-center pr-4 text-white">
                  <!-- Check Icon -->
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                      fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </span>
              </li>
            </ul>
          </div>

          <!-- Conditional Answers Fields -->
          <div v-if="isChoiceType(input.type)" class="mt-1">
            <label class="block text-sm font-medium text-gray-700">Answers</label>
            <div class="mt-2" v-for="(answer, answerIndex) in input.answers" :key="answerIndex">
              <input
                type="text"
                v-model="input.answers[answerIndex]"
                @keyup="handleAdditionalInputs(index, answerIndex, answer)"
                autocomplete="off"
                @keydown.enter.prevent
                @keydown.tab.prevent
                class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Enter answer"
              />
            </div>
          </div>

          <!-- Divider -->
          <div class="relative mt-4 mb-2">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
              <div class="w-full border-t border-gray-500 border-solid"></div>
            </div>
            <div class="relative flex justify-center">
              <span class="px-2 text-gray-500 bg-white">
                <!-- Plus Icon -->
                <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                  <path
                    fill="#6B7280"
                    fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </div>
          </div>
        </div>

        <!-- Hidden Inputs JSON -->
        <input type="hidden" :value="JSON.stringify(newProject.inputs)" name="inputs">

        <!-- Submit Button -->
        <button
          type="submit"
          class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Create Project
        </button>
      </div>

      <!-- Response Message -->
      <div class="block mt-2">
        <div
          class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
          v-if="newProject.response"
          v-html="newProject.response"
        >
          <button class="delete" @click.prevent="newProject.response = ''">×</button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: 'CreateProject',
  props: {
    inputs: {
      type: Object,
      required: true,
    },
    userId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      newProject: {
        name: '',
        description: '',
        media: [''],
        ninputs: 0,
        inputs: [],
        response: '',
      },
      inputLength: {
        name: 200,
        description: 255,
      },
    };
  },
  methods: {
    // Form Validation and Submission
    validateProject() {
      // Perform client-side validation if needed
      // For example, check if required fields are filled

      // Prepare form data
      const formData = {
        name: this.newProject.name,
        description: this.newProject.description,
        media: this.newProject.media,
        ninputs: this.newProject.ninputs,
        inputs: this.newProject.inputs,
        created_by: this.userId,
      };

      // Submit the form via Axios
      axios.post('/projects', formData)
        .then(response => {
          // Handle successful response
          window.location.href = '/projects';
        })
        .catch(error => {
          // Handle errors
          if (error.response && error.response.data) {
            this.newProject.response = error.response.data.message || 'An error occurred.';
          } else {
            this.newProject.response = 'An unexpected error occurred.';
          }
        });
    },

    // Media Inputs Handling
    handleMediaInputs(index, mediaName) {
      // Implement any specific logic when media input changes
      // For example, validate the media URL
    },

    // Increment Additional Inputs
    incrementInputs() {
      if (this.newProject.ninputs < 3) {
        this.newProject.ninputs++;
        this.newProject.inputs.push({
          name: '',
          type: '',
          mandatory: false,
          answers: [''],
          showDropdown: false,
        });
      }
    },

    // Decrement Additional Inputs
    decrementInputs() {
      if (this.newProject.ninputs > 0) {
        this.newProject.ninputs--;
        this.newProject.inputs.pop();
      }
    },

    // Toggle Dropdown Visibility
    toggleDropdown(index) {
      this.newProject.inputs[index].showDropdown = !this.newProject.inputs[index].showDropdown;
    },

    // Select Input Type
    selectType(index, type) {
      this.$set(this.newProject.inputs[index], 'type', type);
      this.newProject.inputs[index].showDropdown = false;

      // Initialize answers if the type requires it
      if (this.isChoiceType(type)) {
        if (!this.newProject.inputs[index].answers.length) {
          this.$set(this.newProject.inputs[index], 'answers', ['']);
        }
      } else {
        this.$set(this.newProject.inputs[index], 'answers', []);
      }
    },

    // Determine if Input Type Requires Answers
    isChoiceType(type) {
      return ['multiple choice', 'one choice'].includes(type.toLowerCase());
    },

    // Handle Additional Inputs (Answers)
    handleAdditionalInputs(inputIndex, answerIndex, answer) {
      // Add a new answer field if the last one is filled
      if (answer && answerIndex === this.newProject.inputs[inputIndex].answers.length - 1) {
        this.newProject.inputs[inputIndex].answers.push('');
      }
    },

    // Determine Dropdown Class Based on Input Type
    dropdownClass(type) {
      return type
        ? 'relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm'
        : 'relative w-full py-4 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm';
    },

    // Determine Selected Type Class
    selectedTypeClass(type, currentType) {
      return type === currentType
        ? 'relative py-2 pl-3 bg-blue-500 text-white cursor-default select-none pr-9'
        : 'relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9';
    },
  },
};
</script>

<style scoped>
/* Component-specific styles */
.delete {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
}
</style>
