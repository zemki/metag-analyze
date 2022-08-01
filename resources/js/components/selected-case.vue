<template>
  <section
    aria-labelledby="message-heading"
    class="flex flex-col flex-1 h-full min-w-0 overflow-hidden xl:order-last"
  >
    <!-- Top section -->
    <div class="flex-shrink-0 bg-white border-b border-gray-200">
      <!-- Toolbar-->
      <div class="flex flex-col justify-center h-16">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between py-3">
            <!-- Left buttons -->
            <div>
              <div
                v-if="caseIsSet && selectedCase.consultable"
                class="relative z-0 inline-flex rounded-md shadow-sm sm:shadow-none sm:space-x-3"
              >
                <span class="inline-flex sm:shadow-sm">
                  <a
                    :href="distinctPath()"
                    v-if="selectedCase.entries.length > 0"
                  >
                    <button
                      type="button"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                    >
                      <!-- Heroicon name: solid/reply -->
                      <svg
                        class="mr-2.5 h-5 w-5 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      <span>Distinct Entries Graph</span>
                    </button>
                  </a>
                  <a :href="groupedCasesPath()">
                    <button
                      type="button"
                      class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 sm:inline-flex hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                    >
                      <!-- Heroicon name: solid/pencil -->
                      <svg
                        class="mr-2.5 h-5 w-5 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                      >
                        <path
                          d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                        />
                      </svg>
                      <span>Grouped Entries Graph</span>
                    </button>
                  </a>
                  <button
                    type="button"
                    class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 sm:inline-flex rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                  >
                    <!-- Heroicon name: solid/user-add -->
                    <svg
                      class="mr-2.5 h-5 w-5 text-gray-400"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                      aria-hidden="true"
                    >
                      <path
                        d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"
                      />
                    </svg>
                    <span>Check Files</span>
                  </button>
                </span>

                <span class="hidden space-x-3 lg:flex">
                  <button
                    type="button"
                    class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-white bg-red-500 border rounded-md sm:inline-flex hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-red-600 focus:border-red-600"
                  >
                    <!-- Heroicon name: solid/archive -->
                    <svg
                      class="mr-2.5 h-5 w-5 text-white"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                      aria-hidden="true"
                    >
                      <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                      <path
                        fill-rule="evenodd"
                        d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    <span>Delete Case</span>
                  </button>
                  <button
                    type="button"
                    class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md sm:inline-flex hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                  >
                    <!-- Heroicon name: solid/folder-download -->
                    <svg
                      class="mr-2.5 h-5 w-5 text-gray-400"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                      aria-hidden="true"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1.586l-.293-.293a1 1 0 10-1.414 1.414l2 2 .002.002a.997.997 0 001.41 0l.002-.002 2-2a1 1 0 00-1.414-1.414l-.293.293V9z"
                      />
                    </svg>
                    <span>Move</span>
                  </button>
                </span>

                <div class="relative block -ml-px sm:shadow-sm lg:hidden">
                  <div>
                    <button
                      type="button"
                      class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 sm:rounded-md sm:px-3"
                      id="menu-2-button"
                      aria-expanded="false"
                      aria-haspopup="true"
                    >
                      <span class="sr-only sm:hidden">More</span>
                      <span class="hidden sm:inline">More</span>
                      <!-- Heroicon name: solid/chevron-down -->
                      <svg
                        class="w-5 h-5 text-gray-400 sm:ml-2 sm:-mr-1"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Message header -->
    </div>

    <!-- 
                change this in VUE to reflect the data from the case
                we can check the status of a case by checking the label that is now "open"
                
                -->
    <div
      class="flex-1 min-h-0 overflow-y-auto"
      v-if="caseIsSet && selectedCase.consultable"
    >
      <div class="pt-5 pb-6 bg-white shadow">
        <div
          class="px-4 sm:flex sm:justify-between sm:items-baseline sm:px-6 lg:px-8"
        >
          <div class="sm:w-0 sm:flex-1">
            <h1 id="message-heading" class="text-lg font-medium text-gray-900">
              {{ selectedCase.name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 truncate">
              {{
                selectedCase.user ? selectedCase.user.email : "no user assigned"
              }}
            </p>
          </div>

          <div
            class="flex items-center justify-between mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0 sm:justify-start"
          >
            <span
              class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
            >
              Open
            </span>
          </div>
        </div>
      </div>
      <!-- Entries section-->
      <ul
        role="list"
        class="py-4 space-y-2 sm:px-6 sm:space-y-4 lg:px-8"
        v-if="selectedCase.consultable"
      >
        <li
          v-for="(entry, index) in selectedCase.entries"
          :key="index"
          class="px-4 py-6 bg-white shadow sm:rounded-lg sm:px-6"
        >
          <div class="sm:flex sm:justify-between sm:items-baseline">
            <h3 class="text-base font-medium">
              <span class="text-gray-600">Media: </span>
              <span class="text-gray-900">{{ entry.media }}</span>
            </h3>
            <p
              class="mt-1 text-sm text-gray-600 whitespace-nowrap sm:mt-0 sm:ml-3"
            >
              <time :datetime="entry.begin" class="mb-2"
                ><span class="font-bold">{{ trans("Begin") }}:</span>
                {{ entry.begin_readable }}</time
              >
              <time :datetime="entry.end" class="block"
                ><span class="font-bold">{{ trans("End") }}: </span>
                {{ entry.end_readable }}</time
              >
            </p>
          </div>
          <div
            class="mt-4 space-y-6 text-sm text-gray-800"
            v-if="
              entry.inputs &&
              (Array.isArray(entry.inputs) || typeof entry.inputs === 'object')
            "
          >
            <h3>Inputs</h3>
            <div class="" v-for="(input, indexJ) in entry.inputs" :key="indexJ">
              <p class="font-bold">{{ indexJ }}</p>
              <audio-player
                v-if="indexJ == 'file'"
                :caseid="cases.id"
                class="w-96 sm:my-2 sm:px-2"
                :file="entry.file_object"
                loop="false"
                autoplay="false"
                :name="entry.file_path"
                :date="entry.created_for_soundplayer"
              ></audio-player>
              <p
                v-if="Array.isArray(input)"
                v-for="(value, indexK) in input"
                :key="indexK"
                class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
              >
                {{ value }}
              </p>
            </div>
          </div>
          <div v-else>
            <h3>Inputs</h3>
            {{ entry.inputs }}
          </div>
        </li>
      </ul>
    </div>
    <div v-else-if="!selectedCase.consultable" class="max-w-xl mx-auto">
      <h3 class="text-3xl font-extrabold text-gray-900 sm:tracking-tight">
        {{
          trans("Case is not consultable because the user is entering entries")
        }}
      </h3>
    </div>
  </section>
</template>

<script>
import moment from "moment";

export default {
  props: {
    cases: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      caseIsSet: false,
      caseNotEnded: false,
      casesIsSet: false,
    };
  },
  beforeDestroy() {
    this.caseIsSet = false;
    this.casesIsSet = false;
  },
  computed: {
    selectedCase: {
      // get and set the selected case
      get() {
        if (this.cases && this.cases.name) {
          this.cases.entries.forEach((entry) => {
            if (typeof entry.inputs !== "object") {
              entry.inputs = JSON.parse(entry.inputs);
            }

            if (entry.inputs.file) {
              this.$set(
                entry,
                "created_for_soundplayer",
                moment(entry.file_object.created_at).format("DD.MM.YYYY H:m:ss")
              );
            }

            this.$set(
              entry,
              "begin_readable",
              moment(entry.begin).format("DD.MM.YYYY H:m:ss")
            );
            this.$set(
              entry,
              "end_readable",
              moment(entry.end).format("DD.MM.YYYY H:m:ss")
            );
          });
          this.caseIsSet = true;

          return this.cases;
        }
      },
      set(newCase) {
        if (newCase && newCase.name) {
          newCase.entries.forEach((entry) => {
            entry.inputs = JSON.parse(entry.inputs);
            if (entry.inputs.file) {
              this.$set(
                entry,
                "created_for_soundplayer",
                moment(entry.file_object.created_at).format("DD.MM.YYYY H:m:ss")
              );
            }

            this.$set(
              entry,
              "begin_readable",
              moment(entry.begin).format("DD.MM.YYYY H:m:ss")
            );
            this.$set(
              entry,
              "end_readable",
              moment(entry.end).format("DD.MM.YYYY H:m:ss")
            );
          });
          this.caseIsSet = true;

          return newCase;
        }
      },
    },
  },
  created() {},
  methods: {
    forceRender(cases) {
      this.selectedCase = cases;
      this.$forceUpdate();
    },
    distinctPath() {
      return this.cases.project.id + "/distinctcases/" + this.cases.id;
    },
    groupedCasesPath() {
      return this.cases.project.id + "/groupedcases/" + this.cases.id;
    },
  },
};
</script>

<style></style>
