<template>
  <!-- Cases List -->
  <aside class="w-1/2 pr-4 overflow-y-auto">
    <div v-if="cases.length > 0">
      <ul class="space-y-2">
        <li
            v-for="caseItem in cases"
            :key="caseItem.id"
            @click="updateSelectedCase(caseItem)"
            :class="{
              'p-4 bg-blue-100 rounded-md cursor-pointer': selectedCase.id === caseItem.id,
              'p-4 bg-white rounded-md hover:bg-blue-50 cursor-pointer': selectedCase.id !== caseItem.id
            }"
        >
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">{{ caseItem.name }}</h2>
              <p class="text-sm text-gray-500">
                {{ caseItem.user ? caseItem.user.email : trans('No user assigned') }}
              </p>
              <span v-if="caseItem.isBackend"
                    class="inline-block px-2 py-1 mt-2 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                  {{ trans('Backend') }}
                </span>
              <!-- Conditional Start Message -->
              <p class="mt-2 text-sm text-gray-700">
                <span v-if="!caseItem.start_day && !caseItem.first_day">
                  {{
                    trans('Case starts when user logs in and lasts')
                  }} {{ getDuration(caseItem.duration) + ' days' || trans('No duration available') }}
                </span>
                <span v-else-if="caseItem.first_day">
                  {{ trans('Started on') }}: {{ caseItem.first_day }}
                </span>
                <span v-else-if="caseItem.start_day">
                  {{
                    isPast(caseItem.start_day) ? trans('Will start on') : trans('Started on')
                  }}: {{ formatDate(caseItem.start_day) }}
                </span>
                <span v-else>
                  {{ trans('Created on') }}: {{ formatDate(caseItem.created_at) }}
                </span>
              </p>
              <p class="text-sm text-gray-700">
                <span v-if="caseItem.is_backend">
                  {{ trans('No last day.') }}
                </span>
                <span v-else>
                  {{
                    trans('Last day')
                  }}: {{ caseItem.last_day ? caseItem.last_day : trans('Case not started by the user') }}
                </span>
              </p>
            </div>
            <div class="flex flex-col space-y-2">
              <a v-if="caseItem.is_consultable && caseItem.entries.length > 0"
                 :href="productionUrl+`/cases/${caseItem.id}/export`"
                 target="_blank">
                <button type="button" class="px-3 py-1 text-sm text-white bg-blue-500 rounded hover:bg-blue-600">
                  {{ trans('Download Case Data as xlsx') }}
                </button>
              </a>
              <button
                  type="button"
                  @click="confirmdeletecase(`../cases/${caseItem.id}`)"
                  class="px-3 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600"
              >
                {{ trans('Delete Case') }}
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <div v-else class="p-4 mt-4 text-center bg-blue-50 rounded-md">
      <div class="flex flex-col items-center">
        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <p class="mt-2 text-lg font-semibold text-blue-700">{{ trans("You don't have any case") }}</p>
        <a :href="productionUrl+urlToCreateCase+'/cases/new'" class="mt-1 text-blue-500 underline hover:text-blue-600">
          {{ trans('Create one') }} &rarr;
        </a>
      </div>
    </div>
    <custom-dialogue v-if="dialog.show"
                     :title="dialog.title"
                     :message="dialog.message"
                     :confirmText="dialog.confirmText"
                     :on-confirm="dialog.onConfirm"
                     :on-cancel="dialog.onCancel"></custom-dialogue>


  </aside>
</template>

<script>
import axios from 'axios';
import CustomDialogue from "./global/CustomDialogue.vue";

export default {
  emits: ['select-case'],
  name: 'CasesTab',
  components: {CustomDialogue},
  props: {
    cases: {
      type: Array,
      required: true
    },
    urlToCreateCase: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      selectedCase: {},
      showSnackbar: false,
      snackbarMessage: "",
      dialog: {
        show: false,
        title: "",
        message: "",
        confirmText: "",
        onConfirm: null,
        onCancel: null,
      },
    };
  },
  methods: {
    updateSelectedCase(caseItem) {
      this.$emit('select-case', caseItem);
      this.selectedCase = caseItem;
    },
    getDuration(duration) {
      // Extract duration string (equivalent to Helper::get_string_between)
      const match = duration.match(/days:(\d+)/);

      return match ? match[1] : '';
    },
    formatDate(dateString) {
      // Format the date string as 'dd.mm.yyyy'
      const date = new Date(dateString);
      return date.toLocaleDateString('de-DE');
    },
    isPast(dateString) {
      const date = new Date(dateString);
      return date > new Date();
    },

    confirmdeletecase(url) {
      this.dialog.show = true;
      this.dialog.message = 'Do you want to delete this case and all the entries?';
      this.dialog.title = "Confirm Case deletion";

      this.dialog.confirmText = "YES delete case and all the entries";
      this.dialog.onConfirm = () => this.deleteCase(url);
      this.dialog.onCancel = () => {
        this.dialog.show = false;
      };


    },
    showSnackbarMessage(message) {

      this.$root.showSnackbarMessage(message);

    },

    deleteCase(url) {
      const self = this;
      axios
          .delete(url)
          .then((response) => {
            // Store the message before reload
            localStorage.setItem('snackbarMessage', self.trans("Case deleted"));
            window.location.reload();
          })
          .catch((error) => {
            let message = "A problem occurred";
            if (error.response && error.response.data && error.response.data.message) {
              message = error.response.data.message;
            }
            self.loading = false;
            self.showSnackbarMessage(message);
          });
    }
  }
};
</script>

<style scoped>
/* Add any necessary styles here */
</style>
