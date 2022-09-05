<template>
  <section
    aria-labelledby="message-heading"
    class="flex flex-col flex-1 h-full min-w-0 overflow-hidden xl:order-last"
  >
    <!--Modal-->
    <div
      class="fixed top-0 left-0 flex items-center justify-center w-full h-auto opacity-0 pointer-events-none modal"
      v-show="editentry.modal"
    >
      <div
        class="absolute w-full h-full bg-gray-900 opacity-50"
        @click="toggleEntryModal()"
      ></div>

      <div
        class="z-50 w-full mx-auto overflow-y-auto bg-white rounded shadow-lg modal-container md:max-w-md"
      >
        <div
          @click="toggleEntryModal()"
          class="absolute top-0 right-0 z-50 flex flex-col items-center mt-4 mr-4 text-sm text-white cursor-pointer"
        >
          <svg
            class="text-white fill-current"
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 18 18"
          >
            <path
              d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"
            ></path>
          </svg>
          <span class="text-sm">(Esc)</span>
        </div>

        <!-- Add margin if you want to see some of the overlay behind the modal-->
        <div class="w-auto h-auto px-6 py-4 text-left modal-content">
          <!--Title-->
          <div class="flex items-center justify-between pb-3">
            <p class="text-2xl font-bold">{{ trans("Edit Entry") }}</p>
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
          <p class="p-3 mt-3 font-bold text-center text-black bg-yellow-500">
            {{ trans("Please scroll if you don't see all the content.") }}
          </p>

          <!--Body-->
          <input type="hidden" :value="editentry.case_id" />
          <div class="my-2">
            <label
              class="text-base font-bold tracking-wide text-gray-700 uppercase"
            >
              Start Date/time *
            </label>
            <b-datetimepicker
              :placeholder="trans('Click to select...')"
              icon="calendar-today"
              name="begin"
              v-model="editentry.data.start"
              @input="newentrydateselected('edit')"
            >
            </b-datetimepicker>
          </div>
          <div class="my-2">
            <label
              class="text-base font-bold tracking-wide text-gray-700 uppercase"
            >
              End Date/time *
            </label>
            <b-datetimepicker
              :placeholder="trans('Click to select...')"
              icon="calendar-today"
              name="end"
              v-model="editentry.data.end"
            >
            </b-datetimepicker>
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
            <b-taginput
              :data="value.answers"
              autocomplete
              size="is-medium"
              open-on-focus
              v-if="value.type === 'multiple choice'"
              v-model="editentry.data.inputs[value.name]"
            >
            </b-taginput>
            <div class="relative" v-if="value.type === 'one choice'">
              <select
                v-model="editentry.data.inputs[value.name]"
                class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500"
              >
                <option
                  v-for="(answer, indexA) in value.answers"
                  :key="indexA"
                  :value="answer"
                >
                  {{ answer }}
                </option>
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

            <div class="relative" v-if="value.type === 'scale'">
              <select
                v-model="editentry.data.inputs[value.name]"
                class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500"
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

          <!--Footer-->
          <div class="flex justify-end pt-2">
            <button
              class="p-3 px-4 mr-2 text-blue-500 bg-transparent rounded-lg hover:bg-gray-100 hover:text-blue-400"
              @click="editEntryAndClose()"
            >
              {{ trans("Save and Close") }}
            </button>
            <button
              class="p-3 px-4 text-white bg-blue-500 rounded-lg hover:bg-blue-400"
              @click="toggleEntryModal()"
            >
              {{ trans("Close") }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <!--End Modal-->

    <!-- Top section -->
    <div
      class="flex-shrink-0 bg-white border-b border-gray-200"
      v-if="showCase"
    >
      <!-- Toolbar-->
      <div class="flex flex-col bg-blue-100">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between py-3">
            <!-- Left buttons -->
            <div>
              <div
                class="relative z-0 inline-flex rounded-md shadow-sm sm:shadow-none sm:space-x-3"
              >
                <span class="inline-flex sm:shadow-sm">
                  <a
                    :href="distinctPath()"
                    v-if="
                      selectedCase.entries.length > 0 || selectedCase.backend
                    "
                  >
                    <button
                      type="button"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white rounded-l-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
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
                      class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white sm:inline-flex focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
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
                    class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white sm:inline-flex rounded-r-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
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

                <div class="relative block -ml-px sm:shadow-sm lg:hidden">
                  <div>
                    <button
                      type="button"
                      class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-900 bg-white rounded-r-md focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 sm:rounded-md sm:px-3"
                      id="menu-2-button"
                      aria-expanded="false"
                      aria-haspopup="true"
                    >
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
    <div class="flex-1 min-h-0 overflow-y-auto" v-if="showCase">
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
          <div class="flex justify-end sm:mt-0 sm:flex-shrink-0">
            <button
              v-if="showCase || selectedCase.entries.length > 0"
              type="button"
              @click="toggleEntryModal(entry)"
              class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-500 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
            <h3 class="font-bold text-gray-500">{{ trans("Inputs") }}</h3>
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
                  v-if="indexJ !== 'file'"
                  class="first:mr-0 mr-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-500"
                >
                  {{ input }}
                </p>
              </div>
            </div>
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
            media_id: this.editentry.data.media_id,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(self.trans("Entry successfully updated."));
          setTimeout(() => window.location.reload(), 500);
        })
        .catch((error) => {
          self.$buefy.snackbar.open(
            self.trans(
              "There it was an error during the request - double check your data or contact the support."
            )
          );
        });
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
      console.log("-----------");
      console.log(entry);
      console.log(inputs);
      console.log("-----------");
      this.editentry.id = entry.id;
      this.editentry.case_id = entry.case_id;
      this.editentry.inputs = this.projectinputs;
      this.editentry.data.inputs = entry.inputs;
      this.editentry.data.media_id = entry.media_id;
      this.editentry.data.media = entry.media;
      this.editentry.data.start = moment(
        entry.begin,
        "YYYY-MM-DD HH:mm"
      ).toDate();
      this.editentry.data.end = moment(entry.end, "YYYY-MM-DD HH:mm").toDate();
      this.editentry.modal = !this.editentry.modal;
      const body = document.querySelector("body");
      const modal = document.querySelector(".modal");
      modal.classList.toggle("opacity-0");
      modal.classList.toggle("pointer-events-none");
      body.classList.toggle("modal-active");
      console.log("-----------");
      console.log(this.editentry);
      console.log("-----------");
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
