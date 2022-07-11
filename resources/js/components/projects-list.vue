<template>
  <div>
    <div
      class="pt-4 pb-4 pl-4 pr-6 border border-gray-200 sm:pl-6 lg:pl-8 xl:pl-6 xl:pt-6 xl:border-t-0"
    >
      <div class="flex items-start">
        <h1 class="flex-1 text-lg font-medium">
          {{ trans("Projects") }}
        </h1>
        <div class="flex justify-center flex-1">
          <div
            v-if="invitesExists"
            class="relative flex items-center w-auto mr-4 align-center"
          >
            <div class="flex items-center h-6">
              <input
                v-model="onlyInvitation"
                id="invites"
                aria-describedby="invites-description"
                name="invites"
                type="checkbox"
                class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
            <div class="ml-3 text-sm">
              <label for="invites" class="font-medium text-gray-700"
                >Only Invitations</label
              >
            </div>
          </div>
          <div class="w-2/3 px-2 lg:px-6">
            <label for="search studies" class="sr-only">{{
              trans("Search Studies")
            }}</label>
            <div class="relative text-indigo-200 focus-within:text-gray-400">
              <div
                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
              >
                <svg
                  class="w-5 h-5"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    fill-rule="evenodd"
                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <input
                type="search"
                id="search"
                name="search projects"
                v-model="search"
                autocomplete="off"
                class="block w-full py-2 pl-10 pr-3 leading-5 text-white placeholder-blue-200 bg-white border border-blue-100 rounded-md focus:outline-none focus:bg-white focus:ring-0 focus:placeholder-gray-400 focus:text-gray-900 sm:text-sm"
                :placeholder="trans('Search Projects')"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <ul
      role="list"
      class="relative border border-gray-200 divide-y divide-gray-200"
    >
      <li
        class="relative py-5 pl-4 pr-6 hover:bg-gray-50 sm:py-6 sm:pl-6 lg:pl-8 xl:pl-6"
        v-for="(Project, index) in filteredList"
        :key="index"
      >
        <div class="flex items-center justify-between space-x-4">
          <div class="min-w-0 space-y-3">
            <div class="flex items-center space-x-3">
              <h2 class="text-xl font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                {{ Project.name }}
              </h2>
            </div>
            <span class="relative group flex items-center space-x-2.5">
              <span
                class="text-sm font-medium text-gray-500 truncate group-hover:text-gray-900"
              >
                here was the sorting lol
              </span>
            </span>
            <span
              class="mt-2 text-sm font-medium text-gray-500 truncate group-hover:text-gray-900"
            >
              number of interviews
              {{ trans("Interviews") }}</span
            >

            <p class="mb-2 text-sm font-bold break-words whitespace-normal">
              {{ trans("Info:") }}
            </p>
            <div
              class="max-w-sm p-2 text-sm text-left text-gray-500 lex bg-zinc-100"
            >
              Cases N°: {{ Project.casescount }}<br />
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
                <circle cx="4" cy="4" r="3" />
              </svg>
              {{ trans("Invited By") }} {{ Project.owner }}
            </span>
          </div>
          <div class="flex-col items-end space-y-3 flex-shrink-1 sm:flex">
            <p class="flex max-w-sm space-x-2 text-sm text-gray-500">
              {{ Project.description }}
            </p>
          </div>
          <div class="z-20 flex-col items-end space-y-3 flex-shrink-1 sm:flex">
            <a
              title="manage Project"
              :href="productionUrl + '/projects/' + Project.id"
              class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md shadow-sm cursor-pointer hover:bg-blue-700 hover:text-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 xl:w-full"
            >
              {{ trans("Manage Project") }}
            </a>

            <a
              v-if="Project.authiscreator"
              href="#"
              @click="confirmdelete(Project.id, Project.name)"
              class="inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium text-white bg-red-500 border border-gray-300 rounded-md shadow-sm hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full"
              >{{ trans("Delete Project") }}</a
            >
            <a
              v-if="Project.authiscreator"
              href="#"
              @click="confirmduplicate(Project.id, Project.name)"
              class="inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium text-white bg-blue-500 border border-gray-300 rounded-md shadow-sm hover:bg-blue-700 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full"
              >{{ trans("Duplicate Project") }}</a
            >
            <a
              v-if="!Project.authiscreator"
              href="#"
              @click="confirmLeaveProject(loggedUser, Project.id)"
              class="inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium text-white bg-red-500 border border-gray-300 rounded-md shadow-sm hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full"
              >{{ trans("Leave Project") }}</a
            >
            <a
              v-if="Project.authiscreator"
              :aria-disabled="!Project.editable"
              :href="productionUrl + '/projects/' + Project.id + '/edit'"
              title="edit Project"
              :class="
                !Project.editable
                  ? 'pointer-events-none select-none cursor-not-allowed opacity-50 inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium text-white bg-blue-500 border border-gray-300 rounded-md shadow-sm hover:bg-blue-700 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full'
                  : 'inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium hover:text-gray-200 text-white bg-blue-500 border border-gray-300 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full'
              "
              >{{ trans("Edit Project") }}</a
            >
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
<script>
export default {
  props: ["projects", "user"],
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
      return (
        _.filter(this.filteredList, (s) => {
          return !s.authiscreator;
        }).length > 0
      );
    },
  },
  data() {
    return {
      search: "",
      loggedUser: JSON.parse(this.user),
      onlyInvitation: false,
      interviewee: "",
    };
  },
  created() {},
  methods: {
    confirmLeaveProject: function (userToDetach, study) {
      let confirmDelete = this.$buefy.dialog.confirm({
        title: this.trans("Confirm Leave"),
        message: this.trans("Are you sure you want to leave this study?"),
        cancelText: this.trans("No"),
        confirmText: this.trans("YES remove me"),
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.detachUser(userToDetach, study),
      });
    },
    detachUser: function (userToDetach, study) {
      let self = this;
      window.axios
        .post(
          window.location.origin +
            self.productionUrl +
            "/studies/invite/" +
            userToDetach.id,
          {
            email: userToDetach.email,
            study: study,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(response.data.message);

          setTimeout(function () {
            window.location.reload();
          }, 1000);
        })
        .catch(function (error) {
          self.$buefy.snackbar.open(
            "There it was an error during the request - refresh page and try again"
          );
        });
    },
    confirmduplicate: function (id, name) {
      let self = this;
      let confirmDelete = this.$buefy.dialog.confirm({
        title: self.trans("Confirm Duplicate"),
        message:
          self.trans('Do you want to duplicate the study "') + name + '" ?',
        cancelText: self.trans("Cancel"),
        confirmText: self.trans("Yes Duplicate Study"),
        hasIcon: true,
        type: "is-primary",
        onConfirm: () => this.duplicatestudy(id),
      });
    },
    duplicatestudy: function (id) {
      this.loading = true;
      this.message = "";
      let self = this;
      axios
        .get("studies/" + id + "/duplicate")
        .then((response) => {
          setTimeout(function () {
            self.loading = false;
            self.$buefy.snackbar.open(self.trans("Study duplicated"));

            window.location.reload();
          }, 500);
        })
        .catch(function (error) {
          console.log(error);
          self.loading = false;
          self.$buefy.snackbar.open(
            "There it was an error during the request - refresh page and try again"
          );
        });
    },
    confirmdelete: function (id, name) {
      let self = this;
      let confirmDelete = this.$buefy.dialog.confirm({
        title: "Confirm Delete",
        message:
          `<div class="p-2 text-center text-white bg-red-600">` +
          self.trans("You are about to delete the study") +
          `<br><span class="uppercase">` +
          name +
          `</span><br>` +
          self.trans("and all its content?") +
          `<br><span class="has-text-weight-bold">` +
          self.trans("Continue?") +
          `</span></div>`,
        cancelText: self.trans("Cancel"),
        confirmText: self.trans("YES \n Delete Study"),
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.deletestudy(id),
      });
    },
    deletestudy: function (id) {
      this.loading = true;
      this.message = "";
      let self = this;
      axios
        .delete("studies/" + id, { data: id })
        .then((response) => {
          setTimeout(function () {
            self.loading = false;
            self.$buefy.snackbar.open(self.trans("Study deleted"));

            window.location.reload();
          }, 500);
        })
        .catch(function (error) {
          self.loading = false;
          self.$buefy.snackbar.open(error.response.data);
        });
    },
  },
};
</script>
