<template>
  <section
    aria-labelledby="message-heading"
    class="flex flex-col flex-1 h-full min-w-0 overflow-hidden xl:order-last"
  >
    <!--Modal-->
    <div
      class="relative z-10"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
      v-show="editentry.modal"
    >
      <div
        @click="toggleEntryModal()"
        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
      ></div>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0"
        >
          <div
            class="relative px-4 pt-5 pb-4 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-sm sm:p-6"
          >
            <div>
              <div class="flex items-center justify-between pb-3">
                <p class="text-2xl font-bold" v-if="actuallysave">
                  {{ trans("Add Entry") }}
                </p>
                <p class="text-2xl font-bold" v-else-if="!actuallysave">
                  {{ trans("Edit Entry") }}
                </p>
                <div @click="toggleEntryModal()" class="z-50 cursor-pointer">
                  <svg
                    class="text-black fill-current"
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                  >
                    <path
                      d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"
                    ></path>
                  </svg>
                </div>
              </div>
              <input type="hidden" :value="editentry.case_id" />
              <div class="my-2">
                <label
                  class="text-base font-bold tracking-wide text-gray-700 uppercase"
                >
                  Start Date/time *
                </label>
                <input
                  type="datetime-local"
                  id="begin"
                  name="begin"
                  class="w-full px-4 py-2 mt-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none focus:shadow-outline"
                  v-model="editentry.data.start"
                  @input="newentrydateselected('edit')"
                />
              </div>
              <div class="my-2">
                <label
                  class="text-base font-bold tracking-wide text-gray-700 uppercase"
                >
                  End Date/time *
                </label>
                <input
                  type="datetime-local"
                  id="end"
                  name="end"
                  class="w-full px-4 py-2 mt-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none focus:shadow-outline"
                  v-model="editentry.data.end"
                />
              </div>
              <div class="my-2">
                <label
                  class="text-base font-bold tracking-wide text-gray-700 uppercase"
                >
                  Media *
                </label>
                <input
                  type="text"
                  name="media_id"
                  v-model="editentry.data.media"
                  class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                />
              </div>
              <h1
                class="my-4 text-2xl font-bold tracking-wide text-center text-gray-700 uppercase"
              >
                Inputs
              </h1>
              <div v-for="(value, index) in editentry.inputs" :key="index">
                <label
                  v-if="value.type !== 'audio recording'"
                  class="my-2 text-base font-bold tracking-wide text-gray-700 uppercase"
                  v-text="value.mandatory ? value.name + ' *' : value.name"
                >
                </label>
                <input
                  type="text"
                  v-if="value.type === 'text'"
                  :name="'text' + value.name"
                  v-model="editentry.data.inputs[value.name]"
                  class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                />

                <div
                  class="sm:col-span-3"
                  v-if="value.type === 'multiple choice'"
                >
                  <div class="mt-1">
                    <select
                      multiple
                      v-model="editentry.data.inputs[value.name]"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option
                        v-for="(answer, indexA) in value.answers"
                        :key="indexA"
                        :value="answer"
                      >
                        {{ answer }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="sm:col-span-3" v-if="value.type === 'one choice'">
                  >
                  <div class="mt-1">
                    <select
                      v-model="editentry.data.inputs[value.name]"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option
                        v-for="(answer, indexA) in value.answers"
                        :key="indexA"
                        :value="answer"
                      >
                        {{ answer }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="relative" v-if="value.type === 'scale'">
                  <select
                    v-model="editentry.data.inputs[value.name]"
                    class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 appearance-none focus:outline-none focus:bg-white focus:border-gray-500"
                  >
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                  </select>
                  <div
                    class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none"
                  >
                    <svg
                      class="w-4 h-4 fill-current"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                      />
                    </svg>
                  </div>
                </div>
              </div>
              <div class="my-3 text-base">* {{ trans("required") }}</div>
            </div>

            <div class="mt-5 sm:mt-6">
              <button
                type="button"
                @click="editEntryAndClose()"
                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-500 border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm"
              >
                {{ trans("Save and Close") }}
              </button>
              <button
                @click="toggleEntryModal()"
                type="button"
                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-500 border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm"
              >
                {{ trans("Close") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--End Modal-->

    <!-- Top section -->
    <div class="flex justify-center flex-shrink-0 py-2" v-if="showCase">
      <!-- Toolbar-->

      <div class="relative z-0 inline-flex sm:space-x-3">
        <a
          class=""
          :href="distinctPath()"
          v-if="selectedCase.entries.length > 0 || selectedCase.backend"
        >
          <button
            type="button"
            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white hover:bg-blue-500 hover:text-white focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
          >
            <svg
              class="mr-2.5 h-5 w-5 text-gray-400"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"
              />
            </svg>

            <span>{{ trans("Distinct Entries Graph") }}</span>
          </button>
        </a>
        <a :href="groupedCasesPath()">
          <button
            type="button"
            class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white hover:bg-blue-500 hover:text-white focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="mr-2.5 h-5 w-5 text-gray-400"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
              />
            </svg>

            <span>{{ trans("Grouped Entries Graph") }}</span>
          </button>
        </a>
      </div>
    </div>
    <div class="flex-1 min-h-0 overflow-y-auto" v-if="showCase">
      <div class="py-2 bg-white">
        <div class="px-4 sm:flex sm:justify-between sm:items-baseline">
          <div class="sm:w-0 sm:flex-1">
            <h1 id="message-heading" class="text-lg font-medium text-gray-900">
              {{ selectedCase.name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 truncate">
              {{
                selectedCase.user
                  ? selectedCase.user.email
                  : trans("No user assigned")
              }}
            </p>
          </div>
          <div
            class="flex justify-end sm:mt-0 sm:flex-shrink-0"
            v-if="selectedCase.backend"
          >
            <button
              v-if="showCase"
              type="button"
              @click="toggleEntryModal()"
              class="w-full justify-center inline-flex items-center px-2.5 py-1.5 border border-transparent font-medium text-white bg-blue-500 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              {{ trans("Add new entry") }}
            </button>
          </div>
        </div>
      </div>
      <div class="bg-white">
        <div class="px-4 text-center sm:flex">
          <div class="sm:w-0 sm:flex-1">
            <h2 id="message-heading" class="text-lg font-medium text-gray-900">
              {{ trans("ENTRIES") }} - {{ selectedCase.entries.length }}
            </h2>
          </div>
        </div>
      </div>
      <!-- Entries section-->
      <ul
        role="list"
        class="p-2 space-y-2 sm:space-y-4"
        v-if="selectedCase.consultable"
      >
        <li
          v-for="(entry, index) in selectedCase.entries"
          :key="index"
          class="px-2 py-4 bg-white sm:rounded-lg"
        >
          <div class="flex justify-end w-full sm:mt-0 sm:flex-shrink-0">
            <button
              v-if="showCase || selectedCase.entries.length > 0"
              type="button"
              @click="toggleEntryModal(entry)"
              class="w-full justify-center inline-flex items-center px-2.5 py-1.5 border border-transparent font-medium text-white bg-blue-500 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              {{ trans("Edit") }}
            </button>
          </div>
          <div class="sm:flex sm:justify-between sm:items-baseline">
            <h3 class="text-base font-medium">
              <span class="text-gray-500">{{ trans("Media") }}: </span>
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
            <div class="w-full text-center">
              <h3 class="text-lg font-bold text-gray-500">
                {{ trans("Inputs") }}
              </h3>
            </div>

            <div class="" v-for="(input, indexJ) in entry.inputs" :key="indexJ">
              <p class="font-bold" v-if="indexJ !== 'firstValue'">
                {{ indexJ }}
              </p>
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
              <div v-if="Array.isArray(input)">
                <p
                  v-for="(value, indexK) in input"
                  :key="indexK"
                  class="first:mr-0 mr-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
                >
                  {{ value }}
                </p>
              </div>
              <div v-else>
                <p
                  v-if="indexJ !== 'file' && indexJ !== 'firstValue'"
                  class="first:mr-0 mr-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
                >
                  {{ input }}
                </p>
              </div>
            </div>
            <div
              class="w-full mx-auto border border-blue-100 border-solid"
              v-if="entry.inputs.firstValue"
            >
              <div class="overflow-hidden rounded">
                <!-- accordion-tab  -->
                <div class="outline-none group accordion-section" tabindex="1">
                  <div
                    class="relative flex items-center justify-between px-4 py-3 pr-10 transition duration-500 bg-blue-100 cursor-pointer group ease"
                  >
                    <div
                      class="transition duration-500 group-focus:text-gray-800 ease"
                    >
                      {{
                        trans(
                          "Click to show first entry submitted by user on "
                        ) + entry.created_at_readable
                      }}
                    </div>
                    <div
                      class="absolute top-0 right-0 inline-flex items-center justify-center w-8 h-8 mt-2 mb-auto ml-auto mr-2 transition duration-500 transform ease group-focus:-rotate-180"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-6 h-6"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                      </svg>
                    </div>
                  </div>
                  <div
                    class="px-4 overflow-hidden duration-500 bg-white group-focus:max-h-screen max-h-0 ease"
                  >
                    <div class="p-2 text-justify text-gray-400">
                      <div class="sm:flex sm:justify-between sm:items-baseline">
                        <h3 class="text-base font-medium">
                          <span class="text-gray-500"
                            >{{ trans("Media") }}:
                          </span>
                          <span class="text-gray-900">{{
                            entry.mediaforFirstValue
                          }}</span>
                        </h3>
                        <p
                          class="mt-1 text-sm text-gray-600 whitespace-nowrap sm:mt-0 sm:ml-3"
                        >
                          <time
                            :datetime="entry.inputs.firstValue.begin"
                            class="mb-2"
                            ><span class="font-bold"
                              >{{ trans("Begin") }}:</span
                            >
                            {{ entry.inputs.firstValue.begin_readable }}</time
                          >
                          <time
                            :datetime="entry.inputs.firstValue.end"
                            class="block"
                            ><span class="font-bold">{{ trans("End") }}: </span>
                            {{ entry.inputs.firstValue.end_readable }}</time
                          >
                        </p>
                      </div>
                      <div
                        class="mt-4 space-y-6 text-sm text-gray-800"
                        v-if="
                          entry.inputs.firstValue.inputs &&
                          (Array.isArray(entry.inputs.firstValue.inputs) ||
                            typeof entry.inputs.firstValue.inputs === 'object')
                        "
                      >
                        <div class="w-full text-center">
                          <h3 class="text-lg font-bold text-gray-500">
                            {{ trans("Inputs") }}
                          </h3>
                        </div>

                        <div
                          class=""
                          v-for="(input, indexJ) in entry.inputs.firstValue
                            .inputs"
                          :key="indexJ"
                        >
                          <p class="font-bold" v-if="indexJ !== 'firstValue'">
                            {{ indexJ }}
                          </p>
                          <audio-player
                            v-if="indexJ == 'file'"
                            :caseid="cases.id"
                            class="w-96 sm:my-2 sm:px-2"
                            :file="entry.inputs.firstValue.file_object"
                            loop="false"
                            autoplay="false"
                            :name="entry.inputs.firstValue.file_path"
                            :date="
                              entry.inputs.firstValue.created_for_soundplayer
                            "
                          ></audio-player>
                          <div v-if="Array.isArray(input)">
                            <p
                              v-for="(value, indexK) in input"
                              :key="indexK"
                              class="first:mr-0 mr-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
                            >
                              {{ value }}
                            </p>
                          </div>
                          <div v-else>
                            <p
                              v-if="
                                indexJ !== 'file' && indexJ !== 'firstValue'
                              "
                              class="first:mr-0 mr-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
                            >
                              {{ input }}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- accordion-tab -->
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <div v-else-if="!selectedCase.consultable" class="max-w-xl mx-auto">
      <div class="p-4 mt-2 rounded-md bg-blue-50">
        <div class="flex">
          <div class="flex-shrink-0">
            <!-- Heroicon name: mini/information-circle -->
            <svg
              class="w-5 h-5 text-blue-400"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M19 10.5a8.5 8.5 0 11-17 0 8.5 8.5 0 0117 0zM8.25 9.75A.75.75 0 019 9h.253a1.75 1.75 0 011.709 2.13l-.46 2.066a.25.25 0 00.245.304H11a.75.75 0 010 1.5h-.253a1.75 1.75 0 01-1.709-2.13l.46-2.066a.25.25 0 00-.245-.304H9a.75.75 0 01-.75-.75zM10 7a1 1 0 100-2 1 1 0 000 2z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="flex-1 ml-3 md:flex md:justify-between">
            <p class="text-sm text-blue-700">
              {{
                trans(
                  "Case is not consultable because the user is entering entries"
                )
              }}
            </p>
          </div>
        </div>
      </div>
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
    projectinputs: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      caseIsSet: false,
      caseNotEnded: false,
      casesIsSet: false,
      editentry: {
        id: 0,
        case_id: 0,
        inputs: {},
        modal: false,
        actuallysave: false,
        data: {
          start: new Date(),
          end: new Date(new Date().setMinutes(new Date().getMinutes() + 1)),
          media_id: "",
          media: "",
          inputs: {},
        },
      },
    };
  },
  beforeDestroy() {
    this.caseIsSet = false;
    this.casesIsSet = false;
  },
  computed: {
    showCase() {
      return this.caseIsSet && this.selectedCase.consultable;
    },
    selectedCase: {
      // get and set the selected case
      get() {
        if (this.cases && this.cases.name) {
          this.cases.entries.forEach((entry) => {
            this.$set(
              entry,
              "created_at_readable",
              moment(entry.created_at).format("DD.MM.YYYY H:m:ss")
            );
            if (typeof entry.inputs !== "object") {
              entry.inputs = JSON.parse(entry.inputs);
            }

            if (entry.inputs.firstValue) {
              entry.inputs.firstValue.begin_readable = moment(
                entry.inputs.firstValue.begin
              ).format("DD.MM.YYYY HH:mm");
              entry.inputs.firstValue.end_readable = moment(
                entry.inputs.firstValue.end
              ).format("DD.MM.YYYY HH:mm");
              if (typeof entry.inputs.firstValue.inputs !== "object") {
                entry.inputs.firstValue.inputs = JSON.parse(
                  entry.inputs.firstValue.inputs
                );
              }
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
            this.$set(
              entry,
              "created_at_readable",
              moment(entry.created_at).format("DD.MM.YYYY H:m:ss")
            );
            entry.inputs = JSON.parse(entry.inputs);
            if (entry.inputs.file) {
              this.$set(
                entry,
                "created_for_soundplayer",
                moment(entry.file_object.created_at).format("DD.MM.YYYY H:m:ss")
              );
            }
            if (entry.inputs.firstValue) {
              entry.inputs.firstValue.begin_readable = moment(
                entry.inputs.firstValue.begin
              ).format("DD.MM.YYYY HH:mm");
              entry.inputs.firstValue.end_readable = moment(
                entry.inputs.firstValue.end
              ).format("DD.MM.YYYY HH:mm");
              if (typeof entry.inputs.firstValue.inputs !== "object") {
                entry.inputs.firstValue.inputs = JSON.parse(
                  entry.inputs.firstValue.inputs
                );
              }
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
    entrySaveAndClose() {
      if (this.MandatoryNewEntry()) {
        this.$buefy.snackbar.open(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      window.axios
        .post(
          `${window.location.origin + this.productionUrl}/cases/${
            this.editentry.case_id
          }/entries`,
          {
            case_id: this.editentry.case_id,
            inputs: this.editentry.data.inputs,
            begin: moment(this.editentry.data.start).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            end: moment(this.editentry.data.end).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            media_id: this.editentry.data.media_id,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(self.trans("Entry successfully sent."));
        })
        .catch((error) => {
          self.$buefy.snackbar.open(
            self.trans(
              "There it was an error during the request - refresh page and try again"
            )
          );
        });

      this.toggleModal();
      this.editentry.data = {};
    },
    MandatoryNewEntry() {
      const self = this;
      return (
        _.isEmpty(self.newentry.data.media_id) ||
        self.newentry.data.start === "" ||
        self.newentry.data.end === ""
      );
    },
    MandatoryEditEntry() {
      const self = this;

      return (
        self.editentry.data.media_id === "" ||
        self.editentry.data.start === "" ||
        self.editentry.data.end === ""
      );
    },
    editEntryAndClose() {
      if (this.MandatoryEditEntry()) {
        this.$buefy.snackbar.open(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      if (this.editentry.actuallysave) {
        this.entrySaveAndClose();
      } else {
        window.axios
          .patch(
            `${window.location.origin + this.productionUrl}/cases/${
              this.editentry.case_id
            }/entries/${this.editentry.id}`,
            {
              case_id: this.editentry.case_id,
              inputs: this.editentry.data.inputs,
              begin: moment(this.editentry.data.start).format(
                "YYYY-MM-DD HH:mm:ss.SSSSSS"
              ),
              end: moment(this.editentry.data.end).format(
                "YYYY-MM-DD HH:mm:ss.SSSSSS"
              ),
              media_id: this.editentry.data.media,
            }
          )
          .then((response) => {
            self.$buefy.snackbar.open(
              self.trans("Entry successfully updated.")
            );
            setTimeout(() => window.location.reload(), 500);
          })
          .catch((error) => {
            self.$buefy.snackbar.open(
              self.trans(
                "There it was an error during the request - double check your data or contact the support."
              )
            );
          });
      }
    },
    toggleEntryModal(
      entry = {
        id: null,
        case_id: null,
        inputs: {},
        data: {},
        begin: null,
        end: null,
      }
    ) {
      if (entry.id !== null) {
        this.editentry.id = entry.id;
        this.editentry.case_id = entry.case_id;
        this.editentry.inputs = this.projectinputs;
        this.editentry.data.inputs = entry.inputs;
        this.editentry.data.media_id = entry.media_id;
        this.editentry.data.media = entry.media;
        this.editentry.data.start = moment(entry.begin)
          .add(moment(entry.begin).utcOffset(), "minutes")
          .toISOString()
          .replace("Z", "");
        this.editentry.data.end = moment(entry.end)
          .add(moment(entry.end).utcOffset(), "minutes")
          .toISOString()
          .replace("Z", "");
      } else {
        this.editentry.actuallysave = true;
        this.editentry.inputs = this.projectinputs;
      }
      this.editentry.modal = !this.editentry.modal;
      const body = document.querySelector("body");
      const modal = document.querySelector(".modal");
      modal.classList.toggle("opacity-0");
      modal.classList.toggle("pointer-events-none");
      body.classList.toggle("modal-active");
    },
    newentrydateselected(edit = "") {
      if (edit === "") {
        this.newentry.data.end = new Date(
          new Date(this.newentry.data.start).setMinutes(
            new Date(this.newentry.data.start).getMinutes() + 5
          )
        );
      } else {
        this.editentry.data.end = new Date(
          new Date(this.editentry.data.start).setMinutes(
            new Date(this.editentry.data.start).getMinutes() + 5
          )
        );
      }
    },
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
