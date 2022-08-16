<template>
  <section
    class="max-w-3xl mx-auto sm:px-6 lg:max-w-7xl lg:px-8"
    :key="projectsInvite"
  >
    <div class="p-2 bg-white sm:rounded-lg sm:overflow-hidden">
      <div class="px-4 py-5 sm:px-6">
        <h2 id="notes-title" class="text-lg font-medium text-gray-900">
          {{
            trans(
              "Enter an email to invite a researcher to work with in this study, then press enter"
            )
          }}
        </h2>
      </div>
      <div class="px-4 py-6 sm:px-6">
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
    };
  },
  methods: {
    forceRerender() {
      this.projectsInvite += 1;
    },
    invite() {
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
        });
    },
    confirmDetach(userToDetach, project) {
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
    detachUser(userToDetach, project) {
      let self = this;
      window.axios
        .post(
          window.location.origin +
            self.productionUrl +
            "/projects/invite/" +
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
  },
};
</script>

<style scoped></style>
