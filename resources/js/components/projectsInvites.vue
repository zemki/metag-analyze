<template>
    <div class="p-2" :key="projectsInvite">
        <label for="invited">
            Enter an email to invite a researcher to work in this project then press enter
        </label>
        <input type="email" name="invited" class="input"
               v-model="toInvite" autocomplete="off"
               @keydown.enter.prevent="invite">

        <div class="w-full mt-6 flex p-2" v-for="user in invitedlist">


            <div class="w-1/2 border-r-8 border-black flex-inline py-1">
                {{user.email}}
            </div>
            <div class="w-1/4 flex-inline">
                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" @click="confirmdelete(user)">
                    <TrashIcon></TrashIcon>
                    Delete
                </button>
            </div>
        </div>

    </div>
</template>

<script>
    import TrashIcon from "vue-material-design-icons/TrashCan.vue";

    export default {
        name: "projectsInvites",
        props: ['invitedlist', 'isowner', 'project'],
        components: {
            TrashIcon
        },
        data() {
            return {
                toInvite: "",
                projectsInvite: 0
            }
        },
        methods: {
            forceRerender() {
                this.projectsInvite += 1;
            },
            invite: function () {
                //validate email
                console.log(this.toInvite);
            let self = this;
                window.axios.post('../projects/invite/', {
                    email: this.toInvite,
                    project: this.project
                }).then(response => {
                    this.$buefy.snackbar.open(response.data.message);

                    if (!_.isNil(response.data.user) && _.find(self.invitedlist,{'email' : response.data.user.email}) === undefined) self.invitedlist.push(response.data.user);

                    this.forceRerender();

                }).catch(error => {
                    console.log(error);
                    if (error.message) this.$buefy.snackbar.open(error.message);
                    else {
                        this.$buefy.snackbar.open(error.response.data);
                    }

                });
            },
            confirmdelete: function (userToDetach) {

                let confirmDelete = this.$buefy.dialog.confirm(
                    {
                        title: 'Confirm Delete',
                        message: 'Are you sure you want to remove the invite for this user?',
                        cancelText: 'Cancel',
                        confirmText: 'YES \n Delete User',
                        hasIcon: true,
                        type: 'is-danger',
                        onConfirm: () => this.detachUser(userToDetach)
                    }
                );
            },
            detachUser: function (userToDetach) {

                let self = this;
                window.axios.post('../projects/invite/'+userToDetach.id, {email: userToDetach.email, project: this.project})
                    .then(response => {

                        self.$buefy.snackbar.open(response.data.message);

                        _.remove(self.invitedlist, function (user) {
                            return user.email === userToDetach.email
                        });
                        this.forceRerender();

                    }).catch(function (error) {

                    self.$buefy.snackbar.open("There it was an error during the request - refresh page and try again");
                });


            }
        }
    }
</script>

<style scoped>

</style>