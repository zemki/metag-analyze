<template>
  <section class="max-w-3xl mx-auto lg:max-w-7xl" :key="projectsInvite">
    <div
      v-if="loading"
      class="fixed top-0 left-0 z-50 flex items-center justify-center w-screen h-screen bg-white opacity-75"
    >
      <!-- Tailwind CSS spinner -->
      <svg class="w-12 h-12 text-gray-500 animate-spin" viewBox="0 0 24 24">
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
    </div>

    <div class="p-2 bg-white sm:rounded-lg sm:overflow-hidden">
      <div class="py-5">
        <h2 id="notes-title" class="text-lg font-medium text-gray-900">
          {{
            trans(
              "Enter an email to invite a researcher to work with in this study, then press enter"
            )
          }}
        </h2>
      </div>

      <div class="flex-1 min-w-0">
        <label for="invitee" class="sr-only">{{
          trans("Type email and press Enter")
        }}</label>
        <input
          type="email"
          name="invited"
          class="input"
          v-model="toInvite"
          autocomplete="off"
          @keydown.enter.prevent="invite"
        />
      </div>
      <div class="py-6">
        <ul role="list" class="space-y-8">
          <li v-for="(user, index) in invitedlist" :key="index">
            <div class="flex space-x-3">
              <div class="flex-shrink-0">
                <v-gravatar class="w-8 h-8 rounded-full" :email="user.email" />
              </div>
              <div>
                <div class="text-sm">
                  <a href="#" class="font-medium text-gray-900">
                    {{ user.email }}</a
                  >
                </div>
                <div class="mt-1 text-sm text-gray-700">
                  <button
                    class="px-4 py-2 font-bold text-white bg-red-500 rounded hover:bg-red-700"
                    @click="confirmdelete(user)"
                  >
                    {{ trans("Delete Invite") }}
                  </button>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </section>
</template>

<script>
import Gravatar from "vue-gravatar";

export default {
  name: "projectsInvites",
  props: ["invitedlist", "isowner", "project"],
  components: {
    Gravatar,
  },
  data() {
    return {
      toInvite: "",
      projectsInvite: 0,
      loading: false,
    };
  },
  methods: {
    forceRerender() {
      this.projectsInvite += 1;
    },
    invite() {
      this.loading = true;
      // validate email
      const self = this;
      window.axios
        .post("../projects/invite", {
          email: this.toInvite,
          project: this.project,
        })
        .then((response) => {
          this.$buefy.snackbar.open(response.data.message);

          if (
            !_.isNil(response.data.user) &&
            _.find(self.invitedlist, { email: response.data.user.email }) ===
              undefined
          )
            self.invitedlist.push(response.data.user);

          this.forceRerender();
        })
        .catch((error) => {
          console.log(error);
          if (error.message) this.$buefy.snackbar.open(error.message);
          else {
            this.$buefy.snackbar.open(error.response.data);
          }
        })
        .finally(() => {
          this.loading = false;
        });
    },
    confirmdelete(userToDetach) {
      this.loading = true;
      let confirmDelete = this.$buefy.dialog.confirm({
        title: this.trans("Confirm Leave"),
        message: this.trans("Are you sure you want to leave this study?"),
        cancelText: this.trans("No"),
        confirmText: this.trans("YES remove me"),
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.detachUser(userToDetach),
      });
    },
    detachUser(userToDetach) {
      console.log("detach user", userToDetach);
      let self = this;
      console.log("project", self.project);
      window.axios
        .post(
          window.location.origin +
            self.productionUrl +
            "/projects/invite/" +
            userToDetach.id,
          {
            email: userToDetach.email,
            project: self.project,
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
  },
};
</script>

<style scoped></style>
