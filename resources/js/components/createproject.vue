<template>
  <div>
    <!-- Breadcrumb Component -->
    <breadcrumb></breadcrumb>

    <!-- Header Section -->
    <div class="flex flex-col h-full">
      <div>
        <div>
          <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
            Create a Project
          </h1>
        </div>
        <div>
          <p class="mt-5 text-xl text-gray-500" v-if="projectType === 'standard'">
            The predefined inputs are Begin Date/Time, End Date/Time, and {{ newProject.entityName || 'Entity' }} used.
            You can enter up to 3 additional inputs giving them name and details,
            this will be reflected in the mobile app.
          </p>
          <p class="mt-5 text-xl text-gray-500" v-if="projectType === 'mart'">
            ESM projects use advanced questionnaire builders with unlimited questions.
            Create custom questionnaires and instruction pages for the MART mobile app.
          </p>
        </div>
      </div>
    </div>

    <!-- Project Type Selection -->
    <div class="mx-auto pt-10">
      <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 mb-8">
        <label class="block text-sm font-medium text-gray-700 mb-4">Project Type</label>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <button
              type="button"
              @click="projectType = 'standard'"
              :class="[
                'relative rounded-lg border p-4 flex flex-col items-center cursor-pointer focus:outline-none transition-all',
                projectType === 'standard' 
                  ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500' 
                  : 'bg-white border-gray-300 hover:border-gray-400'
              ]"
          >
            <svg class="w-12 h-12 mb-3" :class="projectType === 'standard' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <span class="block text-sm font-medium" :class="projectType === 'standard' ? 'text-blue-900' : 'text-gray-900'">Standard Project</span>
            <span class="block text-xs text-center mt-1" :class="projectType === 'standard' ? 'text-blue-700' : 'text-gray-500'">Uses the mobile MetaG app for data collection</span>
          </button>
          
          <button
              type="button"
              @click="projectType = 'mart'"
              :class="[
                'relative rounded-lg border p-4 flex flex-col items-center cursor-pointer focus:outline-none transition-all',
                projectType === 'mart' 
                  ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500' 
                  : 'bg-white border-gray-300 hover:border-gray-400'
              ]"
          >
            <svg class="w-12 h-12 mb-3" :class="projectType === 'mart' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <span class="block text-sm font-medium" :class="projectType === 'mart' ? 'text-blue-900' : 'text-gray-900'">ESM Project (Experience Sampling Method)</span>
            <span class="block text-xs text-center mt-1" :class="projectType === 'mart' ? 'text-blue-700' : 'text-gray-500'">Uses the MART mobile app for advanced questionnaires</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Standard Project Creation Form -->
    <form v-if="projectType === 'standard'" @submit.prevent="validateProject" class="mx-auto" @keydown.enter.prevent="preventSubmitOnEnter">
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
                  @keydown.enter.prevent
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


        <!-- Additional Inputs Count Section -->
        <div>
          <label for="ninputs" class="block text-sm font-medium text-gray-700">
            Number of additional inputs
          </label>

          <input type="hidden" :value="JSON.stringify(newProject.inputs)" name="inputs"/>

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
        <!-- Entity Name Field -->
        <div>
          <label for="entityName" class="block text-sm font-medium text-gray-700">Entity Field Name</label>
          <div class="mt-1 relative">
            <input
                type="text"
                id="entityName"
                v-model="newProject.entityName"
                @keydown.enter.prevent
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                maxlength="50"
                placeholder="Enter name for entity field (default: 'entity')"
            />
            <span class="text-xs text-gray-500 mt-1 block">This name will be used in the mobile app. Default is 'entity' if left empty.</span>
          </div>
        </div>
        <!-- Entity Inputs Section -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <label for="useEntity"
                     class="block text-sm font-medium text-gray-700">{{ newProject.entityName || 'Entity' }}
                Field</label>
              <div class="ml-4 flex items-center">
                <input
                    type="checkbox"
                    id="useEntity"
                    v-model="newProject.useEntity"
                    class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                />
                <label for="useEntity" class="ml-2 block text-sm text-gray-700">
                  Include this field in the project
                </label>
              </div>
            </div>
          </div>

          <div v-if="newProject.useEntity" class="space-y-3">
            <!-- Entity Inputs -->
            <div v-for="(entityItem, index) in newProject.media" :key="index"
                 class="flex items-center space-x-2">
              <input
                  type="text"
                  v-model="newProject.media[index]"
                  @keyup="handleMediaInputs(index, entityItem)"
                  autocomplete="off"
                  @keydown.enter.prevent
                  @keydown.tab.prevent
                  class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  :placeholder="`Enter ${newProject.entityName || 'entity'}`"
              />
              <button
                  v-if="newProject.media.length > 1"
                  @click="removeMedia(index)"
                  class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors duration-150"
                  aria-label="Remove Entity"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Add Entity Button -->
            <button
                @click="addMedia"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-500 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors duration-150"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ `Add ${newProject.entityName || 'Entity'}` }}
            </button>
          </div>
          <p v-else class="text-xs text-gray-500">
            This field won't be included in the mobile app.
          </p>
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
                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                     fill="currentColor" aria-hidden="true">
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
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                       aria-hidden="true">
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
            type="button"
            @click="validateProject"
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
        >
          <div v-html="newProject.response"></div>
          <button class="delete absolute top-2 right-2" @click.prevent="newProject.response = ''">×</button>
        </div>
      </div>
    </form>

    <!-- MART Project Creation Form -->
    <form v-if="projectType === 'mart'" @submit.prevent="validateMartProject" class="mx-auto">
      <div class="p-2 space-y-8 bg-top divide-y divide-gray-200">
        <!-- Basic Project Info -->
        <div class="space-y-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900">MART Project Information</h3>
          
          <!-- Project Name -->
          <div>
            <label for="mart-name" class="block text-sm font-medium text-gray-700">Project Name *</label>
            <div class="mt-1 relative">
              <input
                  type="text"
                  id="mart-name"
                  v-model="martProject.name"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  maxlength="200"
                  placeholder="Enter MART project name"
              />
              <span class="text-xs text-gray-500 w-auto inline-flex float-right">
                {{ inputLength.name - martProject.name.length }}/{{ inputLength.name }}
              </span>
            </div>
          </div>

          <!-- Project Description -->
          <div>
            <label for="mart-description" class="block text-sm font-medium text-gray-700">Description *</label>
            <div class="mt-1 relative">
              <textarea
                  id="mart-description"
                  v-model="martProject.description"
                  rows="3"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  maxlength="255"
                  placeholder="Enter MART project description"
              ></textarea>
              <span class="text-xs text-gray-500 w-auto inline-flex float-right">
                {{ inputLength.description - martProject.description.length }}/{{ inputLength.description }}
              </span>
            </div>
          </div>
        </div>

        <!-- Project Schedule -->
        <div class="pt-8 space-y-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Project Schedule</h3>
          
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Start Date -->
            <div>
              <label for="mart-start-date" class="block text-sm font-medium text-gray-700">Start Date *</label>
              <input
                  type="date"
                  id="mart-start-date"
                  v-model="martProject.startDate"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <!-- Start Time -->
            <div>
              <label for="mart-start-time" class="block text-sm font-medium text-gray-700">Start Time</label>
              <input
                  type="time"
                  id="mart-start-time"
                  v-model="martProject.startTime"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <!-- End Date -->
            <div>
              <label for="mart-end-date" class="block text-sm font-medium text-gray-700">End Date *</label>
              <input
                  type="date"
                  id="mart-end-date"
                  v-model="martProject.endDate"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <!-- End Time -->
            <div>
              <label for="mart-end-time" class="block text-sm font-medium text-gray-700">End Time</label>
              <input
                  type="time"
                  id="mart-end-time"
                  v-model="martProject.endTime"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          </div>
        </div>

        <!-- Questionnaire Schedules -->
        <div class="pt-8 space-y-6">
          <div class="pb-4 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Questionnaire Schedules *</h3>
            <p class="mt-2 text-sm text-gray-600">
              Create at least one schedule with questions. Each schedule can have different questions, timing, and notification settings.
            </p>
          </div>

          <!-- Schedule Builder UI -->
          <div class="space-y-4">
            <!-- Empty State -->
            <div v-if="martProject.schedules.length === 0" class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No schedules created</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating your first questionnaire schedule.</p>
              <div class="mt-6">
                <button
                    type="button"
                    @click="addMartSchedule"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add Schedule
                </button>
              </div>
            </div>

            <!-- Schedules List -->
            <div v-else class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">{{ martProject.schedules.length }} schedule(s) created</span>
                <button
                    type="button"
                    @click="addMartSchedule"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add Schedule
                </button>
              </div>

              <!-- Schedule Cards -->
              <div v-for="(schedule, scheduleIndex) in martProject.schedules" :key="scheduleIndex" class="border-2 border-gray-300 rounded-lg p-5 space-y-4 bg-gray-50">
                <!-- Schedule Header -->
                <div class="flex justify-between items-start pb-3 border-b border-gray-200">
                  <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    <h4 class="text-md font-semibold text-gray-900">{{ schedule.name || `Schedule ${scheduleIndex + 1}` }}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="schedule.type === 'repeating' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                      {{ schedule.type === 'repeating' ? 'Repeating' : 'Single' }}
                    </span>
                  </div>
                  <button
                      type="button"
                      @click="removeMartSchedule(scheduleIndex)"
                      class="text-red-600 hover:text-red-900 hover:bg-red-50 p-1 rounded"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>

                <!-- Collapsible Schedule Content -->
                <div>
                  <button
                      type="button"
                      @click="schedule.expanded = !schedule.expanded"
                      class="w-full text-left inline-flex items-center justify-between text-sm font-medium text-blue-600 hover:text-blue-900"
                  >
                    <span>{{ schedule.expanded ? 'Hide' : 'Edit' }} Schedule Details & Questions ({{ (schedule.questions || []).length }} questions)</span>
                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': schedule.expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                </div>

                <!-- Expanded Schedule Form -->
                <div v-if="schedule.expanded" class="space-y-4 pt-4">
                  <!-- Schedule Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Schedule Name *</label>
                    <input
                        v-model="schedule.name"
                        type="text"
                        class="mt-1 block w-full px-4 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm"
                        placeholder="e.g., Daily Check-in, Morning Survey"
                    />
                  </div>

                  <!-- Schedule Type -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Type *</label>
                    <div class="grid grid-cols-2 gap-3">
                      <button
                          type="button"
                          @click="schedule.type = 'single'"
                          :class="['p-3 border-2 rounded-lg text-left transition-all text-sm',
                                   schedule.type === 'single' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400']"
                      >
                        <div class="font-medium text-gray-900">Single</div>
                        <div class="text-xs text-gray-600 mt-1">One-time questionnaire</div>
                      </button>
                      <button
                          type="button"
                          @click="schedule.type = 'repeating'"
                          :class="['p-3 border-2 rounded-lg text-left transition-all text-sm',
                                   schedule.type === 'repeating' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400']"
                      >
                        <div class="font-medium text-gray-900">Repeating</div>
                        <div class="text-xs text-gray-600 mt-1">Multiple times during study</div>
                      </button>
                    </div>
                  </div>

                  <!-- Date/Time Settings -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                      <input
                          v-model="schedule.start_date_time.date"
                          type="date"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Start Time *</label>
                      <input
                          v-model="schedule.start_date_time.time"
                          type="time"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <!-- End Date/Time (for repeating) -->
                  <div v-if="schedule.type === 'repeating'" class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">End Date *</label>
                      <input
                          v-model="schedule.end_date_time.date"
                          type="date"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">End Time *</label>
                      <input
                          v-model="schedule.end_date_time.time"
                          type="time"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <!-- Notification Settings -->
                  <div class="space-y-3 p-3 bg-white rounded-md border border-gray-200">
                    <div class="flex items-center">
                      <input
                          v-model="schedule.show_progress_bar"
                          type="checkbox"
                          :id="'show_progress_bar_' + scheduleIndex"
                          class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                      />
                      <label :for="'show_progress_bar_' + scheduleIndex" class="ml-2 block text-sm text-gray-700">
                        Show Progress Bar
                      </label>
                    </div>

                    <div class="flex items-center">
                      <input
                          v-model="schedule.show_notifications"
                          type="checkbox"
                          :id="'show_notifications_' + scheduleIndex"
                          class="h-4 w-4 text-blue-500 focus:ring-blue-400 border-gray-300 rounded"
                      />
                      <label :for="'show_notifications_' + scheduleIndex" class="ml-2 block text-sm text-gray-700">
                        Show Notifications
                      </label>
                    </div>

                    <div v-if="schedule.show_notifications">
                      <label class="block text-sm font-medium text-gray-700">Notification Text</label>
                      <input
                          v-model="schedule.notification_text"
                          type="text"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          placeholder="Time for your questionnaire!"
                      />
                    </div>
                  </div>

                  <!-- Questions for this schedule -->
                  <div class="pt-4 border-t border-gray-300">
                    <div class="flex justify-between items-center mb-3">
                      <h5 class="text-sm font-medium text-gray-900">Questions</h5>
                      <button
                          type="button"
                          @click="addQuestionToSchedule(scheduleIndex)"
                          class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200"
                      >
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Question
                      </button>
                    </div>

                    <div v-if="(schedule.questions || []).length === 0" class="text-center py-6 text-gray-500 bg-white rounded-md border border-dashed border-gray-300">
                      <p class="text-xs">No questions yet. Click "Add Question" to get started.</p>
                    </div>

                    <div v-else class="space-y-3">
                      <div v-for="(question, qIndex) in schedule.questions" :key="qIndex"
                           class="border border-gray-200 rounded-md p-3 bg-white space-y-3">
                        <!-- Question Header -->
                        <div class="flex justify-between items-start">
                          <span class="text-xs font-medium text-gray-700">Question {{ qIndex + 1 }}</span>
                          <button
                              type="button"
                              @click="removeQuestionFromSchedule(scheduleIndex, qIndex)"
                              class="text-red-600 hover:text-red-900"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                          </button>
                        </div>

                        <!-- Question Text -->
                        <div>
                          <label class="block text-xs font-medium text-gray-700">Question Text *</label>
                          <textarea
                              v-model="question.text"
                              rows="2"
                              class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm"
                              placeholder="Enter your question"
                          ></textarea>
                        </div>

                        <!-- Question Type -->
                        <div>
                          <label class="block text-xs font-medium text-gray-700">Type *</label>
                          <select
                              v-model="question.type"
                              @change="handleScheduleQuestionTypeChange(scheduleIndex, qIndex)"
                              class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm"
                          >
                            <option value="">Select type...</option>
                            <option value="text">Text Field</option>
                            <option value="textarea">Text Area</option>
                            <option value="number">Number</option>
                            <option value="range">Range Slider</option>
                            <option value="radio">Single Choice</option>
                            <option value="checkbox">Multiple Choice</option>
                          </select>
                        </div>

                        <!-- Range Options -->
                        <div v-if="question.type === 'range'" class="grid grid-cols-3 gap-2">
                          <div>
                            <label class="block text-xs font-medium text-gray-700">Min</label>
                            <input
                                type="number"
                                v-model.number="question.minValue"
                                class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded-md text-sm"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-gray-700">Max</label>
                            <input
                                type="number"
                                v-model.number="question.maxValue"
                                class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded-md text-sm"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-gray-700">Steps</label>
                            <input
                                type="number"
                                v-model.number="question.steps"
                                class="mt-1 block w-full px-2 py-1 border border-gray-300 rounded-md text-sm"
                            />
                          </div>
                        </div>

                        <!-- Choice Options -->
                        <div v-if="question.type === 'radio' || question.type === 'checkbox'" class="space-y-2">
                          <label class="block text-xs font-medium text-gray-700">Options</label>
                          <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center space-x-2">
                            <input
                                type="text"
                                v-model="option.text"
                                class="flex-1 px-2 py-1 border border-gray-300 rounded-md text-sm"
                                :placeholder="'Option ' + (oIndex + 1)"
                            />
                            <button
                                v-if="question.options.length > 1"
                                @click="removeScheduleQuestionOption(scheduleIndex, qIndex, oIndex)"
                                class="p-1 text-red-500 hover:bg-red-50 rounded"
                            >
                              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                              </svg>
                            </button>
                          </div>
                          <button
                              @click="addScheduleQuestionOption(scheduleIndex, qIndex)"
                              class="text-xs text-blue-600 hover:text-blue-900"
                          >
                            + Add Option
                          </button>
                        </div>

                        <!-- Mandatory -->
                        <div class="flex items-center">
                          <input
                              type="checkbox"
                              :id="'mandatory_' + scheduleIndex + '_' + qIndex"
                              v-model="question.mandatory"
                              class="h-3 w-3 text-blue-500 border-gray-300 rounded"
                          />
                          <label :for="'mandatory_' + scheduleIndex + '_' + qIndex" class="ml-2 block text-xs text-gray-700">
                            Required question
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pages Builder -->
        <div class="pt-8 space-y-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Pages Builder</h3>
          <p class="text-sm text-gray-600">Create instruction pages with HTML content for your mobile app.</p>
          
          <!-- Pages List -->
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <h4 class="text-md font-medium text-gray-900">Pages</h4>
              <button
                  type="button"
                  @click="addMartPage"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Page
              </button>
            </div>

            <!-- Page Items -->
            <div v-if="martProject.pages.length === 0" class="text-center py-8 text-gray-500">
              No pages added yet. Click "Add Page" to get started.
            </div>
            
            <div v-for="(page, index) in martProject.pages" :key="index" class="border border-gray-300 rounded-lg p-4 space-y-4">
              <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-gray-700">Page {{ index + 1 }}</span>
                <button
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
                <label :for="'page-name-' + index" class="block text-sm font-medium text-gray-700">Page Name *</label>
                <input
                    :id="'page-name-' + index"
                    type="text"
                    v-model="page.name"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Enter page name"
                />
              </div>

              <!-- Page Content -->
              <div>
                <label :for="'page-content-' + index" class="block text-sm font-medium text-gray-700">Page Content (HTML) *</label>
                <textarea
                    :id="'page-content-' + index"
                    v-model="page.content"
                    rows="6"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Enter HTML content for the page"
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">You can use HTML tags for formatting (e.g., &lt;h1&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;br&gt;, etc.)</p>
              </div>

              <!-- Button Text -->
              <div>
                <label :for="'page-button-' + index" class="block text-sm font-medium text-gray-700">Button Text *</label>
                <input
                    :id="'page-button-' + index"
                    type="text"
                    v-model="page.buttonText"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Continue"
                />
              </div>

              <!-- Show on First App Start -->
              <div class="flex items-center">
                <input
                    :id="'page-first-start-' + index"
                    type="checkbox"
                    v-model="page.showOnFirstAppStart"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label :for="'page-first-start-' + index" class="ml-2 block text-sm text-gray-700">
                  Show this page on first app start
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-8">
          <button
              type="button"
              @click="validateMartProject"
              class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Create ESM Project
          </button>
        </div>
      </div>

      <!-- Response Message -->
      <div class="block mt-2">
        <div
            class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
            v-if="martProject.response"
        >
          <div v-html="martProject.response"></div>
          <button class="delete absolute top-2 right-2" @click.prevent="martProject.response = ''">×</button>
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
      projectType: 'standard', // Default to standard project
      newProject: {
        name: '',
        description: '',
        entityName: 'entity', // Default value
        useEntity: true, // Default to include entity field
        media: [''],
        ninputs: 0,
        inputs: [],
        response: '',
      },
      martProject: {
        name: '',
        description: '',
        startDate: '',
        startTime: '',
        endDate: '',
        endTime: '',
        schedules: [], // New schedule-based approach
        pages: [],
        response: '',
      },
      inputLength: {
        name: 200,
        description: 255,
      },
    };
  },
  
  computed: {
    productionUrl() {
      return this.$root?.productionUrl || '';
    }
  },
  
  watch: {
    'newProject.useEntity': function(newVal) {
      // When useEntity changes, update the media array
      if (newVal === false) {
        // Clear media when useEntity is set to false
        this.newProject.media = [];
      } else if (this.newProject.media.length === 0) {
        // Initialize with one empty input when useEntity is set to true
        this.newProject.media = [''];
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

    // Form Validation and Submission
    validateProject() {
      // Prepare the inputs data to include entity information
      const inputsData = [...this.newProject.inputs];

      // Ensure all inputs have an 'answers' array
      inputsData.forEach(input => {
        // Initialize answers array if not present or if it's not an array
        if (!input.answers || !Array.isArray(input.answers)) {
          input.answers = [];
        }
        // Filter out any empty answers
        input.answers = input.answers.filter(answer => answer && answer.trim() !== '');
      });

      // If useEntity is false, clear the media array
      if (!this.newProject.useEntity) {
        this.newProject.media = [];
      }

      // Add entity configuration to the inputs JSON data
      const entityConfig = {
        isEntityConfig: true, // Special flag to identify this entry
        entityName: this.newProject.entityName || 'entity',
        useEntity: this.newProject.useEntity,
        media: this.newProject.useEntity ? this.newProject.media.filter(m => m.trim() !== '') : [],
        answers: [] // Ensure entity config also has an answers array
      };

      // Prepare form data - include entity fields
      const formData = {
        name: this.newProject.name,
        description: this.newProject.description,
        ninputs: this.newProject.ninputs,
        inputs: JSON.stringify(inputsData), // Only include user inputs, not entity config
        created_by: this.userId,
        entityName: this.newProject.entityName || 'entity',
        useEntity: this.newProject.useEntity,
        media: this.newProject.useEntity ? this.newProject.media.filter(m => m.trim() !== '') : [],
      };

      // Submit the form via Axios
      window.axios.post(this.productionUrl + '/projects', formData)
          .then(response => {
            // Handle successful response
            window.location.href = this.productionUrl + '/projects';
          })
          .catch(error => {
            if (error.response && error.response.data) {
              // Handle validation errors (multiple errors)
              if (error.response.data.errors) {
                let errorMessages = '<ul class="list-disc pl-5">';

                // Loop through all error messages
                Object.keys(error.response.data.errors).forEach(field => {
                  error.response.data.errors[field].forEach(message => {
                    errorMessages += `<li>${message}</li>`;
                  });
                });

                errorMessages += '</ul>';
                this.newProject.response = errorMessages;
              } else {
                // Single error message
                this.newProject.response = error.response.data.message || 'An error occurred.';
              }
            } else {
              this.newProject.response = 'An unexpected error occurred.';
            }
          });
    },

    // Media Inputs Handling
    handleMediaInputs(index, mediaName) {
      // Add a new media field if the last one is filled
      if (mediaName && index === this.newProject.media.length - 1) {
        this.newProject.media.push('');
      }
    },

    // Prevent form submission on Enter key
    preventSubmitOnEnter(e) {
      // Only allow Enter in textarea elements
      if (e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
      }
    },

    // Add Media Item
    addMedia() {
      this.newProject.media.push('');
    },

    // Remove Media Item
    removeMedia(index) {
      if (this.newProject.media.length > 1) {
        this.newProject.media.splice(index, 1);
      }
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
      // Close all other dropdowns first
      this.newProject.inputs.forEach((input, i) => {
        if (i !== index && input.showDropdown) {
          input.showDropdown = false;
        }
      });
      // Toggle the current dropdown
      this.newProject.inputs[index].showDropdown = !this.newProject.inputs[index].showDropdown;
    },

    // Select Input Type
    selectType(index, type) {
      this.newProject.inputs[index].type = type;
      this.newProject.inputs[index].showDropdown = false;

      // Initialize answers if the type requires it
      if (this.isChoiceType(type)) {
        if (!this.newProject.inputs[index].answers.length) {
          this.newProject.inputs[index].answers = [''];
        }
      } else {
        this.newProject.inputs[index].answers = [];
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

    // Validate and Submit MART Project
    async validateMartProject() {
      // Basic validation
      if (!this.martProject.name.trim()) {
        this.martProject.response = 'Project name is required';
        return;
      }

      if (!this.martProject.description.trim()) {
        this.martProject.response = 'Project description is required';
        return;
      }

      if (!this.martProject.startDate) {
        this.martProject.response = 'Start date is required';
        return;
      }

      if (!this.martProject.endDate) {
        this.martProject.response = 'End date is required';
        return;
      }

      // Schedule validation - at least one schedule required
      if (this.martProject.schedules.length === 0) {
        this.martProject.response = 'At least one schedule with questions is required';
        return;
      }

      // Validate each schedule
      for (let s = 0; s < this.martProject.schedules.length; s++) {
        const schedule = this.martProject.schedules[s];

        if (!schedule.name.trim()) {
          this.martProject.response = `Schedule ${s + 1}: Schedule name is required`;
          return;
        }

        if (!schedule.start_date_time.date) {
          this.martProject.response = `Schedule ${s + 1}: Start date is required`;
          return;
        }

        if (schedule.type === 'repeating' && !schedule.end_date_time.date) {
          this.martProject.response = `Schedule ${s + 1}: End date is required for repeating schedules`;
          return;
        }

        // Validate questions in this schedule
        if (!schedule.questions || schedule.questions.length === 0) {
          this.martProject.response = `Schedule ${s + 1}: At least one question is required`;
          return;
        }

        // Validate each question
        for (let q = 0; q < schedule.questions.length; q++) {
          const question = schedule.questions[q];

          if (!question.text.trim()) {
            this.martProject.response = `Schedule ${s + 1}, Question ${q + 1}: Question text is required`;
            return;
          }

          if (!question.type) {
            this.martProject.response = `Schedule ${s + 1}, Question ${q + 1}: Question type is required`;
            return;
          }

          // Validate choice questions have options
          if ((question.type === 'radio' || question.type === 'checkbox') &&
              (!question.options || question.options.filter(opt => opt.text && opt.text.trim()).length < 2)) {
            this.martProject.response = `Schedule ${s + 1}, Question ${q + 1}: At least 2 options are required for choice questions`;
            return;
          }

          // Validate range questions
          if (question.type === 'range') {
            if (question.minValue == null || question.maxValue == null) {
              this.martProject.response = `Schedule ${s + 1}, Question ${q + 1}: Min and max values are required for range questions`;
              return;
            }
            if (question.minValue >= question.maxValue) {
              this.martProject.response = `Schedule ${s + 1}, Question ${q + 1}: Max value must be greater than min value`;
              return;
            }
          }
        }
      }

      // Validate pages
      for (let i = 0; i < this.martProject.pages.length; i++) {
        const page = this.martProject.pages[i];

        if (!page.name.trim()) {
          this.martProject.response = `Page ${i + 1}: Page name is required`;
          return;
        }

        if (!page.content.trim()) {
          this.martProject.response = `Page ${i + 1}: Page content is required`;
          return;
        }

        if (!page.buttonText.trim()) {
          this.martProject.response = `Page ${i + 1}: Button text is required`;
          return;
        }
      }

      // Create MART configuration for legacy compatibility
      const martConfig = {
        type: 'mart',
        projectOptions: {
          startDateAndTime: {
            date: this.martProject.startDate,
            time: this.martProject.startTime || '00:00'
          },
          endDateAndTime: {
            date: this.martProject.endDate,
            time: this.martProject.endTime || '23:59'
          },
          pages: this.martProject.pages.map((page, index) => ({
            name: page.name,
            content: page.content,
            showOnFirstAppStart: page.showOnFirstAppStart,
            buttonText: page.buttonText,
            sortOrder: index
          }))
        }
      };

      // Prepare form data for project creation
      const formData = {
        name: this.martProject.name,
        description: this.martProject.description,
        ninputs: 0,
        inputs: JSON.stringify([martConfig]), // Store MART config only
        created_by: this.userId,
        is_mart: true,
      };

      try {
        // Step 1: Create the project
        const projectResponse = await window.axios.post(this.productionUrl + '/projects', formData);
        const projectId = projectResponse.data.id || projectResponse.data.project?.id;

        if (!projectId) {
          window.location.href = this.productionUrl + '/projects';
          return;
        }

        // Step 2: Create each schedule with its questions
        for (let s = 0; s < this.martProject.schedules.length; s++) {
          const schedule = this.martProject.schedules[s];

          // Build timing config
          const timingConfig = {
            start_date_time: schedule.start_date_time,
            end_date_time: schedule.type === 'repeating' ? schedule.end_date_time : null,
            daily_interval_duration: schedule.daily_interval_duration || null,
            min_break_between: schedule.min_break_between || null,
            max_daily_submits: schedule.max_daily_submits || null,
            daily_start_time: schedule.daily_start_time || null,
            daily_end_time: schedule.daily_end_time || null,
            quest_available_at: schedule.quest_available_at || 'randomTimeWithinInterval',
          };

          // Build notification config
          const notificationConfig = {
            show_progress_bar: schedule.show_progress_bar,
            show_notifications: schedule.show_notifications,
            notification_text: schedule.notification_text || '',
          };

          // Convert questions to backend format
          const processedQuestions = schedule.questions.map(q => {
            const backendType = this.mapMartTypeToMetagType(q.type);
            const config = {};

            // Add type-specific config
            if (q.type === 'range') {
              config.min = q.minValue;
              config.max = q.maxValue;
              config.step = q.steps;
            } else if (q.type === 'radio' || q.type === 'checkbox') {
              config.options = q.options.filter(o => o.text && o.text.trim()).map(o => o.text.trim());
            }

            return {
              text: q.text,
              type: backendType,
              mandatory: q.mandatory,
              config: config
            };
          });

          // Create the schedule via API
          const scheduleData = {
            questionnaire_id: s + 1, // Sequential ID
            name: schedule.name,
            type: schedule.type,
            start_date_time: timingConfig.start_date_time,
            end_date_time: timingConfig.end_date_time,
            show_progress_bar: notificationConfig.show_progress_bar,
            show_notifications: notificationConfig.show_notifications,
            notification_text: notificationConfig.notification_text,
            daily_interval_duration: timingConfig.daily_interval_duration,
            min_break_between: timingConfig.min_break_between,
            max_daily_submits: timingConfig.max_daily_submits,
            daily_start_time: timingConfig.daily_start_time,
            daily_end_time: timingConfig.daily_end_time,
            quest_available_at: timingConfig.quest_available_at,
            questions: processedQuestions
          };

          await window.axios.post(`${this.productionUrl}/projects/${projectId}/schedules`, scheduleData);
        }

        // Success! Redirect to projects page
        window.location.href = this.productionUrl + '/projects';

      } catch (error) {
        console.error('Error creating MART project:', error);
        if (error.response && error.response.data) {
          // Handle validation errors
          if (error.response.data.errors) {
            let errorMessages = '<ul class="list-disc pl-5">';

            Object.keys(error.response.data.errors).forEach(field => {
              error.response.data.errors[field].forEach(message => {
                errorMessages += `<li>${message}</li>`;
              });
            });

            errorMessages += '</ul>';
            this.martProject.response = errorMessages;
          } else {
            this.martProject.response = error.response.data.message || 'An error occurred.';
          }
        } else {
          this.martProject.response = 'An unexpected error occurred while creating the project.';
        }
      }
    },

    // MART Schedule Management Methods
    addMartSchedule() {
      const scheduleId = this.martProject.schedules.length + 1;
      this.martProject.schedules.push({
        name: `Schedule ${scheduleId}`,
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
        questions: [],
        expanded: true // Start expanded for new schedules
      });
    },

    removeMartSchedule(scheduleIndex) {
      this.martProject.schedules.splice(scheduleIndex, 1);
    },

    addQuestionToSchedule(scheduleIndex) {
      const schedule = this.martProject.schedules[scheduleIndex];
      if (!schedule.questions) {
        schedule.questions = [];
      }
      schedule.questions.push({
        text: '',
        type: '',
        mandatory: false,
        minValue: 0,
        maxValue: 10,
        steps: 1,
        options: [{ text: '', value: 0 }]
      });
    },

    removeQuestionFromSchedule(scheduleIndex, questionIndex) {
      this.martProject.schedules[scheduleIndex].questions.splice(questionIndex, 1);
    },

    handleScheduleQuestionTypeChange(scheduleIndex, questionIndex) {
      const question = this.martProject.schedules[scheduleIndex].questions[questionIndex];

      // Reset type-specific properties
      question.minValue = 0;
      question.maxValue = 10;
      question.steps = 1;
      question.options = [];

      // Initialize based on type
      if (question.type === 'number') {
        question.minValue = 1;
        question.maxValue = 10;
      } else if (question.type === 'range') {
        question.minValue = 0;
        question.maxValue = 10;
        question.steps = 1;
      } else if (question.type === 'radio' || question.type === 'checkbox') {
        question.options = [
          { text: '', value: 0 },
          { text: '', value: 1 }
        ];
      }
    },

    addScheduleQuestionOption(scheduleIndex, questionIndex) {
      const question = this.martProject.schedules[scheduleIndex].questions[questionIndex];
      if (!question.options) {
        question.options = [];
      }
      question.options.push({
        text: '',
        value: question.options.length
      });
    },

    removeScheduleQuestionOption(scheduleIndex, questionIndex, optionIndex) {
      const question = this.martProject.schedules[scheduleIndex].questions[questionIndex];
      if (question.options.length > 1) {
        question.options.splice(optionIndex, 1);
        // Renumber values
        question.options.forEach((opt, idx) => {
          opt.value = idx;
        });
      }
    },

    // MART Page Management Methods
    addMartPage() {
      this.martProject.pages.push({
        name: '',
        content: '',
        buttonText: 'Continue',
        showOnFirstAppStart: false
      });
    },

    removeMartPage(index) {
      this.martProject.pages.splice(index, 1);
    },

    // Helper method to map MART types to MetaG types
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
