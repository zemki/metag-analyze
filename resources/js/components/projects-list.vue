<template>
  <div>
    <!-- Leave Project Modal -->
    <Modal
        v-if="showLeaveProjectModal"
        title="Confirm Leave"
        :visible="showLeaveProjectModal"
        @confirm="detachUser"
        @cancel="closeLeaveProjectModal"
    >
      <p>{{ trans("Are you sure you want to leave this study?") }}</p>
    </Modal>

    <!-- Duplicate Project Modal -->
    <Modal
        v-if="showDuplicateProjectModal"
        title="Confirm Duplicate"
        :visible="showDuplicateProjectModal"
        @confirm="duplicatestudy"
        @cancel="closeDuplicateModal"
    >
      {{ trans('Do you want to duplicate the project ') }} {{ duplicateProjectName }} ?
    </Modal>
    <!-- Delete Project Modal -->
    <Modal
        v-if="showDeleteProjectModal"
        title="Confirm Delete"
        :visible="showDeleteProjectModal"
        @confirm="deleteStudy"
        @cancel="closeDeleteProjectModal"
    >
      <div class="p-2 text-center text-white bg-red-600">
        <p>{{ trans("You are about to delete the study") }}</p>
        <p class="uppercase">{{ deleteProjectName }}</p>
        <p>{{ trans("and all its content?") }}</p>
        <p class="has-text-weight-bold">{{ trans("Continue?") }}</p>
      </div>
    </Modal>


    <div class="pt-4 pb-4 pl-4 pr-6 sm:pl-6 lg:pl-8 xl:pl-6 xl:pt-6 xl:border-t-0">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <h1 class="text-lg font-medium mr-4">{{ trans("Projects") }}</h1>
          <a :href="productionUrl+'/projects/new'" :title="trans('Create a new Project')">
            <button type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                   fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                      clip-rule="evenodd"/>
              </svg>
              {{ trans('New Project') }}
            </button>
          </a>
        </div>
        <div class="flex items-center space-x-4">
          <div v-if="invitesExists" class="flex items-center">
            <input v-model="onlyInvitation" id="invites" aria-describedby="invites-description"
                   name="invites" type="checkbox"
                   class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500"/>
            <label for="invites" class="ml-2 text-sm font-medium text-gray-700">Only Invitations</label>
          </div>
          <div class="w-64">
            <label for="search-studies" class="sr-only">{{ trans("Search Projects") }}</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd"/>
                </svg>
              </div>
              <input type="search" id="search-studies" name="search-projects" v-model="search"
                     autocomplete="off"
                     class="block w-full py-2 pl-10 pr-3 leading-5 text-gray-900 placeholder-gray-500 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-0 focus:border-blue-500 sm:text-sm"
                     :placeholder="trans('Search Projects')"/>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ul role="list" class="relative divide-y divide-gray-200">
      <li
          class="relative py-6 pl-8 pr-6 hover:bg-gray-50 sm:pl-10 lg:pl-12 xl:pl-10"
          v-for="(Project, index) in filteredList"
          :key="index"
      >
        <div class="flex items-start justify-between space-x-6">
          <div class="min-w-0 flex-1 space-y-3">
            <div>
              <h2 class="text-3xl font-semibold text-gray-900">
                <span class="absolute inset-0" aria-hidden="true"></span>
                {{ Project.name }}
              </h2>
            </div>

            <div
                class="w-full mt-2 text-sm font-medium text-gray-500 break-words group-hover:text-gray-900"
            >
              {{ Project.description }}
            </div>

            <p class="mb-2 text-sm font-bold break-words whitespace-normal">
              {{ trans("Info:") }}
            </p>
            <div
                class="max-w-sm p-2 text-sm text-left text-gray-500 lex bg-zinc-100"
            >
              Cases N°: {{ Project.casescount }}<br/>
              Overall Entries N°: {{ Project.entries }}
            </div>
            <span
                v-if="!Project.authiscreator"
                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-blue-100 text-blue-700"
            >
              <svg
                  class="-ml-0.5 mr-1.5 h-2 w-2 text-blue-500"
                  fill="currentColor"
                  viewBox="0 0 8 8"
              >
                <circle cx="4" cy="4" r="3"/>
              </svg>
              {{ trans("Invited By") }} {{ Project.owner }}
            </span>
          </div>

          <div class="flex flex-col items-end space-y-3 flex-shrink-0 sm:flex z-40">
            <div class="space-y-2 w-full">
              <a
                  title="manage Project"
                  :href="productionUrl + '/projects/' + Project.id"
                  class="block text-center w-full px-3 py-1.5 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md shadow-sm cursor-pointer hover:bg-blue-700 hover:text-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                {{ trans("Manage Project") }}
              </a>

              <a
                  v-if="Project.authiscreator"
                  href="#"
                  @click="confirmDelete(Project.id, Project.name)"
                  class="block text-center w-full px-3 py-1.5 text-sm font-medium text-white bg-red-500 rounded-md shadow-sm hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >{{ trans("Delete Project") }}</a
              >
              <a
                  v-if="Project.authiscreator"
                  href="#"
                  @click="confirmduplicate(Project.id, Project.name)"
                  class="block text-center w-full px-3 py-1.5 text-sm font-medium text-white bg-blue-500 rounded-md shadow-sm hover:bg-blue-700 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >{{ trans("Duplicate Project") }}</a
              >
              <a
                  v-if="!Project.authiscreator"
                  href="#"
                  @click="confirmLeaveProject(loggedUser, Project.id)"
                  class="block text-center w-full px-3 py-1.5 text-sm font-medium text-white bg-red-500 rounded-md shadow-sm hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >{{ trans("Leave Project") }}</a
              >
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
<script>
import Modal from "./global/modal.vue";
import { emitter } from '@/emitter';

export default {
  name: "ProjectsList",
  props: {
    projects: {
      type: String,
      required: true
    },
    user: {
      type: String,
      required: true
    }
  },
  components: {
    Modal,
  },
  inject: ['productionUrl'],
  computed: {
    filteredList() {
      return JSON.parse(this.projects).filter((project) => {
        if (this.onlyInvitation) {
          return (
              project.name.toLowerCase().includes(this.search.toLowerCase()) &&
              !project.authiscreator
          );
        } else {
          return project.name.toLowerCase().includes(this.search.toLowerCase());
        }
      });
    },
    invitesExists() {
      return this.filteredList.some((s) => !s.authiscreator);
    },
  },
  data() {
    return {
      search: "",
      loggedUser: JSON.parse(this.user),
      onlyInvitation: false,
      interviewee: "",
      leaveProjectStudyId: null,
      leaveProjectUserId: null,
      duplicateProjectId: null,
      duplicateProjectName: "",
      showDeleteProjectModal: false,
      deleteProjectId: null,
      deleteProjectName: "",
      showLeaveProjectModal: false,
      showDuplicateProjectModal: false,
      loading: false,
      message: ""
    };
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
    confirmLeaveProject(userToDetach, studyId) {
      this.leaveProjectStudyId = studyId;
      this.leaveProjectUserId = userToDetach.id;
      this.showLeaveProjectModal = true;
    },
    detachUser() {
      const self = this;
      window.axios
          .post(
              window.location.origin +
              this.productionUrl +
              "/projects/invite/" +
              self.leaveProjectUserId,
              {
                email: self.loggedUser.email,
                study: self.leaveProjectStudyId,
              }
          )
          .then((response) => {
            self.showSnackbarMessage(response.data.message);
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          })
          .catch((error) => {
            self.showSnackbarMessage(
                "There was an error during the request - refresh page and try again"
            );
          });
    },
    closeLeaveProjectModal() {
      this.showLeaveProjectModal = false;
      this.leaveProjectStudyId = null;
      this.leaveProjectUserId = null;
    },
    closeDuplicateModal() {
      this.showDuplicateProjectModal = false;
      this.duplicateProjectId = null;
      this.duplicateProjectName = "";
    },
    confirmduplicate(id, name) {
      this.duplicateProjectId = id;
      this.duplicateProjectName = name;
      this.showDuplicateProjectModal = true;
    },
    duplicatestudy() {
      this.loading = true;
      this.message = "";
      const self = this;

      window.axios
          .get("projects/" + self.duplicateProjectId + "/duplicate")
          .then((response) => {
            setTimeout(() => {
              self.loading = false;
              localStorage.setItem('snackbarMessage', self.trans("Project duplicated"));
              window.location.reload();
            }, 500);
          })
          .catch((error) => {
            console.error(error);
            self.loading = false;
            self.showSnackbarMessage(
                "There was an error during the request - refresh page and try again"
            );
          });
    },
    // Updated to use the event emitter instead of $refs or $root
    showSnackbarMessage(message) {
      // Always use the emitter for messaging
      emitter.emit('show-snackbar', message);
    },
    confirmDelete(id, name) {
      this.deleteProjectId = id;
      this.deleteProjectName = name;
      this.showDeleteProjectModal = true;
    },
    deleteStudy() {
      this.loading = true;
      this.message = "";
      const self = this;

      window.axios
          .delete(this.productionUrl + '/projects/' + self.deleteProjectId, {data: self.deleteProjectId})
          .then((response) => {
            setTimeout(() => {
              self.loading = false;
              localStorage.setItem("snackbarMessage", self.trans("Project deleted"));
              window.location.reload();
            }, 500);
          })
          .catch((error) => {
            self.loading = false;
            self.showSnackbarMessage(error.response?.data || "Error deleting project");
          });
    },
    closeDeleteProjectModal() {
      this.showDeleteProjectModal = false;
      this.deleteProjectId = null;
      this.deleteProjectName = "";
    },
  },
};
</script>
