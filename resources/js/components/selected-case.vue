<template>
  <section
    aria-labelledby="message-heading"
    class="flex flex-col flex-1 h-full min-w-0 overflow-hidden xl:order-last"
  >
    <!-- Modal -->
    <modal
      :visible="editentry.modal"
      :title="editentry.actuallysave ? trans('Add Entry') : trans('Edit Entry')"
      @confirm="editEntryAndClose"
      @cancel="toggleEntryModal"
    >
      <input type="hidden" :value="editentry.case_id" />
      <div class="my-2">
        <label
          class="text-base font-bold tracking-wide text-gray-700 uppercase"
        >
          {{ trans("Start Date/time *") }}
        </label>
        <input
          type="datetime-local"
          id="begin"
          name="begin"
          class="w-full px-4 py-2 mt-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none focus:shadow-outline"
          v-model="editentry.data.start"
          @input="editentrydateselected('edit')"
        />
      </div>
      <div class="my-2">
        <label
          class="text-base font-bold tracking-wide text-gray-700 uppercase"
        >
          {{ trans("End Date/time *") }}
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
          {{ trans("Media *") }}
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
        {{ trans("Inputs") }}
      </h1>
      <div v-for="(value, index) in editentry.inputs" :key="index">
        <label
          v-if="value.type !== 'audio recording'"
          class="pb-2 text-base font-bold tracking-wide text-gray-700 uppercase"
          :class="{ 'required-label': value.mandatory }"
        >
          {{ value.mandatory ? `${value.name} *` : value.name }}
        </label>
        <input
          type="text"
          v-if="value.type === 'text'"
          :name="'text' + value.name"
          v-model="editentry.data.inputs[value.name]"
          class="block w-full px-4 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
        />
        <div class="pb-2 sm:col-span-3" v-if="value.type === 'multiple choice'">
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
        <div class="pb-2 sm:col-span-3" v-if="value.type === 'one choice'">
          <div class="mt-1">
            <select
              v-model="editentry.data.inputs[value.name][0]"
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
        <div class="sm:col-span-3" v-if="value.type === 'scale'">
          <div class="mt-1">
            <select
              v-model="editentry.data.inputs[value.name]"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
        </div>
      </div>
      <div class="my-3 text-base">* {{ trans("required") }}</div>
    </modal>

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
            class="relative z-0 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white hover:bg-blue-500 hover:text-white focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
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
            class="relative z-0 inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white hover:bg-blue-500 hover:text-white focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
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
          <div class="flex justify-end sm:mt-0 sm:flex-shrink-0">
            <p class="flex w-full text-sm text-gray-500 word-break">
              ID {{ selectedCase.id }}
            </p>
            <button
              v-if="showCase && selectedCase.backend"
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
          class="px-2 bg-white sm:rounded-lg"
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
                {{ entry.begin_readable }}
              </time>
              <time :datetime="entry.end" class="block"
                ><span class="font-bold">{{ trans("End") }}: </span>
                {{ entry.end_readable }}
              </time>
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
                v-if="indexJ == 'file' && entry.file_object && entry.file_path"
                :caseid="cases.id"
                class="w-96 sm:my-2 sm:px-2"
                :file="entry.file_object"
                loop="false"
                autoplay="false"
                :name="entry.file_path"
                :date="entry.created_for_soundplayer"
              ></audio-player>
              <div v-else-if="indexJ == 'file'" class="italic text-gray-500">
                {{ trans("File was deleted") }}
              </div>
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
                            {{ entry.inputs.firstValue.begin_readable }}
                          </time>
                          <time
                            :datetime="entry.inputs.firstValue.end"
                            class="block"
                            ><span class="font-bold">{{ trans("End") }}: </span>
                            {{ entry.inputs.firstValue.end_readable }}
                          </time>
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
                            v-if="
                              indexJ == 'file' &&
                              entry.inputs.firstValue &&
                              entry.inputs.firstValue.file_object
                            "
                            :caseid="cases.id"
                            class="w-96 sm:my-2 sm:px-2"
                            :file="entry.inputs.firstValue.file_object"
                            loop="false"
                            autoplay="false"
                            :name="entry.inputs.firstValue.file_path"
                            :date="
                              entry.inputs.firstValue.created_for_soundplayer ||
                              ''
                            "
                          ></audio-player>
                          <div
                            v-else-if="indexJ == 'file'"
                            class="italic text-red-500"
                          >
                            {{ trans("File not available") }}
                          </div>
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

          <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center">
              <span class="px-2 text-gray-500 bg-white">
                <!-- Heroicon name: mini/plus -->
                <svg
                  class="w-5 h-5 text-gray-500"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"
                  />
                </svg>
              </span>
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
    <Snackbar v-if="showSnackbar" :message="snackbarMessage" ref="snackbar" />
  </section>
</template>

<script>
import { ref, computed, reactive, onBeforeUnmount } from "vue";
import moment from "moment";
import Modal from "./global/modal.vue";
import Snackbar from "./global/snackbar.vue";

export default {
  name: "SelectedCase",
  components: {
    Modal,
    Snackbar,
  },
  props: {
    cases: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    projectinputs: {
      type: Array,
      required: true,
      default: () => [],
    },
    productionUrl: {
      type: String,
      default: "",
    },
  },
  emits: ["update:selectedCase"],
  setup(props) {
    const caseIsSet = ref(false);
    const caseNotEnded = ref(false);
    const snackbarMessage = ref("");
    const showSnackbar = ref(false);

    const editentry = reactive({
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
    });

    onBeforeUnmount(() => {
      caseIsSet.value = false;
    });

    const showCase = computed(() => {
      return caseIsSet.value && selectedCase.value?.consultable;
    });

    const selectedCase = computed(() => {
      if (props.cases && props.cases.name) {
        caseIsSet.value = true;
        let processedCases = { ...props.cases };
        processedCases.entries = processEntries(props.cases.entries);
        return processedCases;
      }
      return null;
    });

    const trans = (key) => {
      if (
        typeof window.trans === "undefined" ||
        typeof window.trans[key] === "undefined"
      ) {
        return key;
      } else {
        if (window.trans[key] === "") return key;
        return window.trans[key];
      }
    };

    const showSnackbarMessage = (message) => {
      snackbarMessage.value = message;
      showSnackbar.value = true;
      setTimeout(() => {
        showSnackbar.value = false;
      }, 3000); // Snackbar duration
    };

    const processEntries = (entries = []) => {
      return entries.map((entry) => {
        // Basic entry data
        entry.created_at_readable = moment(entry.created_at).format(
          "DD.MM.YYYY H:m:ss"
        );

        // Handle inputs parsing
        if (typeof entry.inputs !== "object") {
          try {
            entry.inputs = JSON.parse(entry.inputs);
          } catch (e) {
            entry.inputs = {};
          }
        }

        // Handle firstValue
        if (entry.inputs && entry.inputs.firstValue) {
          entry.inputs.firstValue.begin_readable = moment(
            entry.inputs.firstValue.begin
          ).format("DD.MM.YYYY HH:mm");
          entry.inputs.firstValue.end_readable = moment(
            entry.inputs.firstValue.end
          ).format("DD.MM.YYYY HH:mm");

          if (typeof entry.inputs.firstValue.inputs !== "object") {
            try {
              entry.inputs.firstValue.inputs = JSON.parse(
                entry.inputs.firstValue.inputs
              );
            } catch (e) {
              entry.inputs.firstValue.inputs = {};
            }
          }
        }

        // Handle file data safely
        if (entry.inputs && entry.inputs.file) {
          // Only set created_for_soundplayer if file_object exists and has created_at
          entry.created_for_soundplayer =
            entry.file_object && entry.file_object.created_at
              ? moment(entry.file_object.created_at).format("DD.MM.YYYY H:m:ss")
              : null;
        }

        // Set basic timestamps
        entry.begin_readable = moment(entry.begin).format("DD.MM.YYYY H:m:ss");
        entry.end_readable = moment(entry.end).format("DD.MM.YYYY H:m:ss");

        return entry;
      });
    };

    const entrySaveAndClose = () => {
      // Get productionUrl from props or default to empty string
      const productionUrl = props.productionUrl || "";
      window.axios
        .post(
          `${window.location.origin + productionUrl}/cases/${
            props.cases.id
          }/entries`,
          {
            case_id: props.cases.id,
            inputs: editentry.data.inputs,
            begin: moment(editentry.data.start).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            end: moment(editentry.data.end).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            media_id: editentry.data.media,
          }
        )
        .then((response) => {
          showSnackbarMessage(trans("Entry successfully sent."));
          setTimeout(() => window.location.reload(), 500);
        })
        .catch((error) => {
          showSnackbarMessage(
            trans(
              "There was an error during the request - refresh page and try again"
            )
          );
        });
    };

    const mandatoryEntry = () => {
      if (editentry.actuallysave) {
        return (
          editentry.data.media === "" ||
          editentry.data.start === "" ||
          editentry.data.end === ""
        );
      } else {
        return (
          editentry.data.media_id === "" ||
          editentry.data.start === "" ||
          editentry.data.end === ""
        );
      }
    };

    const editEntryAndClose = () => {
      if (mandatoryEntry()) {
        showSnackbarMessage(trans("Check your mandatory entries."));
        return;
      }

      if (editentry.actuallysave) {
        entrySaveAndClose();
      } else {
        // Get productionUrl from props or default to empty string
        const productionUrl = props.productionUrl || "";
        window.axios
          .patch(
            `${window.location.origin + productionUrl}/cases/${
              editentry.case_id
            }/entries/${editentry.id}`,
            {
              case_id: editentry.case_id,
              inputs: editentry.data.inputs,
              begin: moment(editentry.data.start).format(
                "YYYY-MM-DD HH:mm:ss.SSSSSS"
              ),
              end: moment(editentry.data.end).format(
                "YYYY-MM-DD HH:mm:ss.SSSSSS"
              ),
              media_id: editentry.data.media,
            }
          )
          .then((response) => {
            showSnackbarMessage(trans("Entry successfully updated."));
            setTimeout(() => window.location.reload(), 500);
          })
          .catch((error) => {
            showSnackbarMessage(
              trans(
                "There was an error during the request - double check your data or contact the support."
              )
            );
          });
      }
    };

    const formatDateForInput = (date) => {
      return moment(date)
        .add(moment(date).utcOffset(), "minutes")
        .toISOString()
        .slice(0, 16); // Format as YYYY-MM-DDTHH:mm
    };

    const clearEditEntryData = () => {
      editentry.id = 0;
      editentry.case_id = 0;
      editentry.inputs = {};
      editentry.actuallysave = false;
      editentry.data = {
        start: new Date(),
        end: new Date(new Date().setMinutes(new Date().getMinutes() + 1)),
        media_id: "",
        media: "",
        inputs: {},
      };
    };

    const toggleEntryModal = (
      entry = {
        id: null,
        case_id: null,
        inputs: {},
        data: {},
        begin: null,
        end: null,
      }
    ) => {
      if (entry.id !== null) {
        // Ensure inputs are properly parsed
        const parsedInputs =
          typeof entry.inputs === "string"
            ? JSON.parse(entry.inputs)
            : entry.inputs;

        editentry.id = entry.id;
        editentry.case_id = entry.case_id;
        editentry.inputs = props.projectinputs;
        editentry.data.inputs = parsedInputs;
        editentry.data.media_id = entry.media_id;
        editentry.data.media = entry.media;

        // Ensure dates are properly formatted
        editentry.data.start = formatDateForInput(entry.begin);
        editentry.data.end = formatDateForInput(entry.end);
      } else {
        editentry.actuallysave = true;
        editentry.inputs = props.projectinputs;
        editentry.data.inputs = {};
      }

      editentry.modal = !editentry.modal;

      if (!editentry.modal) {
        clearEditEntryData();
      }
    };

    const editentrydateselected = (edit = "") => {
      editentry.data.end = new Date(
        new Date(editentry.data.start).setMinutes(
          new Date(editentry.data.start).getMinutes() + 5
        )
      );
    };
    const distinctPath = () => {
      return props.cases.project.id + "/distinctcases/" + props.cases.id;
    };

    const groupedCasesPath = () => {
      return props.cases.project.id + "/groupedcases/" + props.cases.id;
    };

    return {
      caseIsSet,
      caseNotEnded,
      editentry,
      showCase,
      selectedCase,
      snackbarMessage,
      showSnackbar,
      trans,
      showSnackbarMessage,
      processEntries,
      entrySaveAndClose,
      MandatoryEntry: mandatoryEntry,
      editEntryAndClose,
      toggleEntryModal,
      formatDateForInput,
      clearEditEntryData,
      editentrydateselected,
      forceRender,
      distinctPath,
      groupedCasesPath,
    };
  },
};
</script>

<style></style>
