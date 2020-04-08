<template>
    <section>
        <b-table
                :data="isEmpty ? [] : users"
                bordered
                narrowed
                :default-sort-direction="defaultSortDirection"
                :sort-icon="sortIcon"
                :sort-icon-size="sortIconSize"
                :row-class="(row, index) => 'bg-white text-black-300 hover:text-blue-200'"
        >

            <template slot-scope="props">

                <b-table-column field="id" label="ID" width="40" numeric sortable>
                    {{ props.row.id }}
                </b-table-column>

                <b-table-column field="email" label="Email" sortable email class="">
                    <div class="inline-block w-auto">{{ props.row.email }}</div>
                    <div v-if="Date.parse(props.row.latest_activity) > Date.parse(currentDateTime)" class="rounded-full h-3 w-3 bg-green-300 text-green-200 inline-block mr-2 blink_me ">

                    </div>
                </b-table-column>

                <b-table-column field="device_id" label="Devices ID" sortable>
                    {{ props.row.deviceID }}
                </b-table-column>

                <b-table-column field="api_token" label="Api Token" sortable class="whitespace-no-wrap text-xs w-1/12">
                    {{ props.row.api_token ? "YES" : "NO" }}
                </b-table-column>

                <b-table-column field="case" label="# of Cases" sortable>
                    {{ props.row.case.length }}
                </b-table-column>

                <b-table-column field="projects" label="# of Projects" sortable>
                    {{ props.row.projects.length }}
                </b-table-column>

                <b-table-column field="date" label="Last logged in" centered sortable>
                    <span class="tag is-success">
                         {{ props.row.last_login_date ? new Date(props.row.last_login_date).toLocaleString() : '' }}
                    </span>
                </b-table-column>

                <b-table-column field="actions" label="Actions" centered sortable class="text-xs w-auto">
                    <span class="block m-0 mb-2"><a href="#" @click="confirmcleandeviceid(props.row.id)"
                             class="bg-red-300 hover:bg-red-400 text-black-800 font-bold py-2 px-4 rounded inline-flex items-center text-xs">
                        <b-icon
                                class="fill-current w-4 h-4 mr-2"
                                icon="delete"
                        >
                    </b-icon>Delete all device(s) id</a>
                        </span>
                    <span class="block m-0"><a href="#" @click="confirmresettoken(props.row.id)" title="(request to login again on mobile device)"
                             class="bg-red-300 hover:bg-red-400 text-black-800 font-bold py-2 px-4 rounded inline-flex items-center text-xs">
                        <b-icon
                                class="fill-current w-4 h-4 mr-2"
                                icon="delete"
                        >
                    </b-icon>Reset API token</a>
                        </span>
                </b-table-column>

            </template>

            <template slot="empty">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>No Users.</p>
                    </div>
                </section>
            </template>
        </b-table>
    </section>
</template>

<script>
    export default {
        name: "usertable",
        props: ['users'],
        computed: {
            isEmpty: function () {
                return _.isEmpty(this.users);
            },
            currentDateTime: function()
            {
                var d = new Date()
                var milliseconds = Date.parse(d)
                milliseconds = milliseconds - (5 * 60 * 1000)
                return new Date(milliseconds)

            }
        },
        data() {
            return {
                sortIcon: 'arrow-up',
                sortIconSize: 'is-small',
                defaultSortDirection: 'asc'
            }
        },
        methods: {
            confirmresettoken: function (id) {

                let confirmDelete = this.$buefy.dialog.confirm(
                    {
                        title: 'Confirm Delete Device ID',
                        message: '<div class="bg-green-600 p-2 text-white text-center">When you delete the API token the user has to login again on the mobile app. Do you want to delete it?</div>',
                        cancelText: 'No',
                        confirmText: 'Yes',
                        hasIcon: true,
                        type: 'is-danger',
                        onConfirm: () => this.resetapitoken(id)
                    }
                );
            },
            resetapitoken: function(id) {

                this.loading = true;
                this.message = "";
                let self = this;
                axios.get('resetapitoken/' + id)
                    .then(response => {
                        setTimeout(function () {
                            self.loading = false;
                            self.$buefy.snackbar.open("Api token deleted");
                        }, 500);

                    }).catch(function (error) {

                    self.loading = false;
                    self.$buefy.snackbar.open("There it was an error during the request - refresh page and try again");
                });

            },
            confirmcleandeviceid: function (id) {

                let confirmDelete = this.$buefy.dialog.confirm(
                    {
                        title: 'Confirm Delete Device ID',
                        message: '<div class="bg-red-600 p-2 text-white text-center">Do you want to delete all the device id for this user?</div>',
                        cancelText: 'No',
                        confirmText: 'Yes',
                        hasIcon: true,
                        type: 'is-info',
                        onConfirm: () => this.deletedeviceid(id)
                    }
                );
            },
            deletedeviceid: function (id) {

                this.loading = true;
                this.message = "";
                let self = this;
                axios.get('deletedeviceid/' + id)
                    .then(response => {
                        setTimeout(function () {
                            self.loading = false;
                            self.$buefy.snackbar.open("Device(s) id deleted");
                        }, 500);

                    }).catch(function (error) {

                    self.loading = false;
                    self.$buefy.snackbar.open("There it was an error during the request - refresh page and try again");
                });


            },
            confirmresendconfirmation: function (id) {

                let confirmDelete = this.$buefy.dialog.confirm(
                    {
                        title: 'Confirm Delete Device ID',
                        message: '<div class="bg-red-600 p-2 text-white text-center">Do you want to make the user confirm the email again?</div>',
                        cancelText: 'No',
                        confirmText: 'Yes',
                        hasIcon: true,
                        type: 'is-info',
                        onConfirm: () => this.resendconfirmation(id)
                    }
                );
            },
            resendconfirmation: function (id) {

                this.loading = true;
                this.message = "";
                let self = this;
                axios.get(' email/resend/' + id)
                    .then(response => {
                        setTimeout(function () {
                            self.loading = false;
                            self.$buefy.snackbar.open("He/She need now to confirm.");
                        }, 500);

                    }).catch(function (error) {

                    self.loading = false;
                    self.$buefy.snackbar.open("There it was an error during the request - refresh page and try again");
                });


            }
        }
    }
</script>

<style scoped>
    @keyframes blinker {
        50% {
            opacity: 0;
        }
    }
    .blink_me {
        animation: blinker 2s linear infinite;
    }
</style>
