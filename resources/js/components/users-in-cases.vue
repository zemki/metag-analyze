<template>
    <div class="w-full">
        <div>
            <table class="table-auto w-full">
                <thead>
                <tr class="bg-gray-200 text-center">
                    <th class="px-4 py-2 text-center w-auto">{{trans('Case')}}</th>
                    <th class="px-4 py-2 text-center">{{trans('Email')}}</th>
                    <th class="px-4 py-2 text-center">{{trans('Last seen')}}</th>
                    <th class="px-4 py-2 text-center">{{trans('Status')}}</th>
                    <th class="px-4 py-2 text-center">{{trans('Actions')}}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="cas in cases" class="w-full">
                    <td class="border px-4 py-2 w-auto">{{cas.name}}</td>
                    <td class="border px-4 py-2">{{cas.user.email}}</td>
                    <td class="border px-4 py-2">{{cas.user.last_login_date}}</td>
                    <td class="border px-4 py-2">{{calculateStatus(cas.user)}}</td>
                    <td class="border px-4 py-2">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                @click="confirmsendResetPassword(cas.user)"
                        >
                            Reset Password
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        name: "users-in-cases",
        props: ['users', 'cases'],
        computed: {
            isEmpty: function () {
                return _.isEmpty(this.users);
            }
        },
        data() {
            return {}
        },
        methods: {
            calculateStatus(user) {
                if(!_.isEmpty(user.password_token)) return this.trans('user needs to check email and set the password');

                if (_.isEmpty(user.api_token)) return this.trans("user is able to send the data.");
                else if (!_.isEmpty(user.api_token)) return this.trans("user logged in the mobile app.");

                if(_.isEmpty(user.email_verified)) return this.trans('user needs to verify the email.');


            },
            confirmsendResetPassword(user) {
            let self = this;

                this.$buefy.dialog.confirm(
                    {
                        title: 'Confirm reset password',
                        message: '<strong class="bg-red-600 text-yellow-400 p-2">'+this.trans('Do you want send an email to this user to set the password?')+'</strong>',
                        cancelText: this.trans('No'),
                        confirmText: this.trans('Yes'),
                        hasIcon: true,
                        type: 'is-danger',
                        onConfirm: () => this.sendResetPassword(user)
                    }
                );

            },
            sendResetPassword(user) {
            let self = this;

                window.axios.post(this.productionUrl+'/users/password/reset',
                    {email: user.email}).then(response => {
                    // confirm??
                    self.$buefy.snackbar.open(response.data);


                }).catch(error => {
                    console.log(error);
                });
            }
        }
    }
</script>

<style scoped>

</style>
