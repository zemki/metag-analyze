<template>
    <div class="p-2" :key="projectsInvite">
        <label for="invited">
					{{ trans('Enter an email to invite a researcher to work with in this project, then press enter') }}
        </label>
        <input type="email" name="invited" class="input"
               v-model="toInvite" autocomplete="off"
               @keydown.enter.prevent="invite">

        <div class="flex w-full p-2 mt-6" v-for="user in invitedlist">

            <div class="w-1/2 py-1 border-r-8 border-black flex-inline">
                {{user.email}}
            </div>
            <div class="w-1/4 flex-inline">
                <button class="px-4 py-2 font-bold text-white bg-red-500 rounded hover:bg-red-700" @click="confirmdelete(user)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 6v18h18v-18h-18zm5 14c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm4-18v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.315c0 .901.73 2 1.631 2h5.712z"/></svg>
                    Delete
                </button>
            </div>
        </div>

    </div>
</template>

<script>
export default {
  name: 'projectsInvites',
  props: ['invitedlist', 'isowner', 'project'],
  data() {
    return {
      toInvite: '',
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
      window.axios.post('../projects/invite', {
        email: this.toInvite,
        project: this.project,
      }).then((response) => {
        this.$buefy.snackbar.open(response.data.message);

        if (!_.isNil(response.data.user) && _.find(self.invitedlist, { email: response.data.user.email }) === undefined) self.invitedlist.push(response.data.user);

        this.forceRerender();
      }).catch((error) => {
        console.log(error);
        if (error.message) this.$buefy.snackbar.open(error.message);
        else {
          this.$buefy.snackbar.open(error.response.data);
        }
      });
    },
    confirmdelete(userToDetach) {
      const confirmDelete = this.$buefy.dialog.confirm(
        {
          title: 'Confirm Delete',
          message: 'Are you sure you want to remove the invite for this user?',
          cancelText: 'Cancel',
          confirmText: 'YES \n Delete User',
          hasIcon: true,
          type: 'is-danger',
          onConfirm: () => this.detachUser(userToDetach),
        },
      );
    },
    detachUser(userToDetach) {
      const self = this;
      window.axios.post(`../projects/invite/${userToDetach.id}`, { email: userToDetach.email, project: this.project })
        .then((response) => {
          self.$buefy.snackbar.open(response.data.message);

          _.remove(self.invitedlist, (user) => user.email === userToDetach.email);
          this.forceRerender();
        }).catch((error) => {
          self.$buefy.snackbar.open('There it was an error during the request - refresh page and try again');
        });
    },
  },
};
</script>

<style scoped>

</style>
