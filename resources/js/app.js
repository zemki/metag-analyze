/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import "vue-material-design-icons/styles.css"
import Buefy from 'buefy'

window.Vue = require('vue');

var Highcharts = require('highcharts');

// Load module after Highcharts is loaded
require('highcharts/modules/exporting')(Highcharts);
require('highcharts/modules/gantt')(Highcharts);
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));



Vue.config.devtools = true;
Vue.config.debug = true;
Vue.config.silent = false;


Vue.component('edit-project', require('./components/editproject.vue').default);
Vue.component('consult-entries', require('./components/consultentries.vue').default);
Vue.component('project-invites', require('./components/projectsInvites.vue').default);
Vue.component('user-table', require('./components/usertable.vue').default);
Vue.component('graph', require('./components/graph.vue').default);
Vue.component('users-in-cases', require('./components/users-in-cases.vue').default);


Vue.use(Buefy);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.prototype.trans = (key) => {
    return _.isUndefined(window.trans[key]) ? key : window.trans[key];
};

window.app = new Vue({
    el: '#app',
    computed: {
        'newproject.formattedinputstring': function () {
            return JSON.stringify(this.newproject.inputs);
        },
        url: function () {
            return document.URL.split('/').pop();
        }
    },
    mounted() {
        window.addEventListener("keydown", function (e) {
            this.lastPressedKey = e.keyCode;
        });
        let replaceUndefinedOrNull = function (key, value) {
            if (value === null || value === undefined || value === "") {
                return undefined;
            }

            return value;
        };


    },
    watch: {
        'newcase.duration.starts_with_login': function (newVal, OldVal) {
            if (newVal) {
                if (!_.isEmpty(this.newcase.duration.selectedUnit) && !_.isEmpty(this.newcase.duration.input)) {

                    if (this.newcase.duration.selectedUnit === 'week') var numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
                    else var numberOfDaysToAdd = parseInt(this.newcase.duration.input);

                    let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

                    // duration in days and change to this after first login
                    this.newcase.duration.message = cdd + '.' + cmm + '.' + cy;
                    this.newcase.duration.value = "value:" + numberOfDaysToAdd * 24 + "|days:" + numberOfDaysToAdd;

                } else {
                    this.newcase.duration.message = "";
                    this.newcase.duration.value = "";

                }
            }
        },
        'newcase.duration.startdate': function (newVal, OldVal) {
            if (!_.isEmpty(this.newcase.duration.input) && !_.isEmpty(this.newcase.duration.selectedUnit)) {

                if (this.newcase.duration.selectedUnit === 'week') var numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
                else var numberOfDaysToAdd = parseInt(this.newcase.duration.input);

                this.formatdatestartingat();
            }

        },
        'newcase.duration.selectedUnit': function (newVal, OldVal) {
            if (!_.isEmpty(this.newcase.duration.input)) {

                if (newVal === 'week') var numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
                else var numberOfDaysToAdd = parseInt(this.newcase.duration.input);

                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);


                this.newcase.duration.message = cdd + '.' + cmm + '.' + cy;
                this.newcase.duration.value = "value:" + numberOfDaysToAdd * 24 + "|days:" + numberOfDaysToAdd;

                this.formatdatestartingat();


            } else {
                this.newcase.duration.message = "";
                this.newcase.duration.value = "";

            }
        },
        'newcase.duration.input': function (newVal, OldVal) {

            this.newcase.duration.input = newVal.replace(/\D/g, '');

            if (!_.isEmpty(this.newcase.duration.selectedUnit)) {

                if (this.newcase.duration.selectedUnit === 'week') var numberOfDaysToAdd = parseInt(newVal) * 7;
                else var numberOfDaysToAdd = parseInt(newVal);

                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

                // duration in days and change to this after first login
                this.newcase.duration.message = cdd + '.' + cmm + '.' + cy;
                this.newcase.duration.value = "value:" + numberOfDaysToAdd * 24 + "|days:" + numberOfDaysToAdd;
                this.formatdatestartingat();

            } else {
                this.newcase.duration.message = "";
                this.newcase.duration.value = "";

            }
        },
        'newuser.case.duration.input': function (newVal, OldVal) {

            this.newuser.case.duration.input = newVal.replace(/\D/g, '');

            if (!_.isEmpty(this.newuser.case.duration.selectedUnit)) {

                if (this.newuser.case.duration.selectedUnit === 'week') var numberOfDaysToAdd = parseInt(newVal) * 7;
                else var numberOfDaysToAdd = parseInt(newVal);

                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

                // duration in days and change to this after first login
                this.newuser.case.duration.message = cdd + '.' + cmm + '.' + cy;
                this.newuser.case.duration.value = "value:" + numberOfDaysToAdd * 24 + "|days:" + numberOfDaysToAdd;

                this.formatdatestartingat();


            } else {
                this.newuser.case.duration.message = "";
                this.newuser.case.duration.value = "";

            }
        },
        'newuser.case.duration.selectedUnit': function (newVal, OldVal) {
            if (!_.isEmpty(this.newuser.case.duration.input)) {

                if (newVal === 'week') var numberOfDaysToAdd = parseInt(this.newuser.case.duration.input) * 7;
                else var numberOfDaysToAdd = parseInt(this.newuser.case.duration.input);

                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

                this.newuser.case.duration.message = cdd + '.' + cmm + '.' + cy;
                this.newuser.case.duration.value = "value:" + numberOfDaysToAdd * 24 + "|days:" + numberOfDaysToAdd;

                this.formatdatestartingat();


            } else {
                this.newuser.case.duration.message = "";
                this.newuser.case.duration.value = "";

            }
        },
        'newuser.email': function (newVal, oldVal) {

            window.axios.post('/users/exist', {email: newVal}).then(response => {
                this.newuser.emailexist = response.data;
                if (response.data) {
                    this.newuser.emailexistmessage = "This user will be invited.";
                } else {
                    this.newuser.emailexistmessage = "This user is not registered, an invitation email will be sent.";
                }

            }).catch(error => {
                console.log(error);
            });
        },
        'newproject.ninputs': function (newVal, oldVal) {

            if (newVal < 0 || oldVal < 0) {
                newVal = 0;
                oldVal = 0;
            }

            let direction = (newVal - oldVal);

            if (direction > 0) {
                let inputtemplate = {
                    name: "",
                    type: "",
                    mandatory: true,
                    numberofanswer: 0,
                    answers: [""]
                }

                for (var i = 0; i < direction; i++) {
                    this.newproject.inputs.push(inputtemplate);
                }
            } else if (newVal == 0) {
                // special case
                this.newproject.inputs = [];
            } else {
                // decrease
                for (var i = 0; i < Math.abs(direction); i++) {
                    this.newproject.inputs.pop();

                }
            }
        }
    },
    data: {
        mainNotification: true,
        lastPressedKey: "",
        selectedEntriesData: [],
        errormessages: {
            namemissing: "name is required. <br>",
            inputnamemissing: "input name is required. <br>",
            inputtypemissing: "input type is required. <br>",
            multipleinputnoanswer: "provide a valid number of answers. <br>"

        },
        newcase: {
            duration: {
                input: "",
                starts_with_login: true,
                selectedUnit: "days",
                allowedUnits: ["day(s)", "week(s)"],
                message: "",
                value: "",
            },
            minDate: new Date()

        },
        newproject: {
            name: "",
            ninputs: 0,
            inputs: [],
            config: window.inputs,
            response: "",
            description: "",
            media: [""]
        },
        registration: {
            password: null,
            password_length: 0,
            contains_six_characters: false,
            contains_number: false,
            contains_letters: false,
            contains_special_character: false,
            valid_password: false,
            email: ""
        },
        newuser: {
            role: 2,
            email: "",
            emailexist: false,
            emailexistmessage: "",
            assignToCase: false,
            case: {
                duration: {
                    input: "",
                    selectedUnit: "",
                    allowedUnits: ["day(s)", "week(s)"],
                    message: "",
                    value: "",
                },
                name: "",
                caseexistmessage: "",
                caseexist: false
            },
            project: 0,
            tooltipActive: false

        }
    },
    methods: {
        checkPassword() {
            this.registration.password_length = this.registration.password.length;
            const special_chars = /[ !@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;

            if (this.registration.password_length > 5) {
                this.registration.contains_six_characters = true;
            } else {
                this.registration.contains_six_characters = false;
            }

            this.registration.contains_number = /\d/.test(this.registration.password);
            this.registration.contains_letters = /[a-z]/.test(this.registration.password);
            this.registration.contains_special_character = special_chars.test(this.registration.password);

            if (this.registration.contains_six_characters === true &&
                this.registration.contains_letters === true &&
                this.registration.contains_number === true) {
                this.registration.valid_password = true;
            } else {
                this.registration.valid_password = false;
            }

            if (this.registration.password === this.registration.email) this.registration.valid_password = false;


        },
        formatdatestartingat: function () {

            if (!this.newcase.duration.starts_with_login) {

                var numberOfDaysToAdd;
                if (this.newcase.duration.selectedUnit === 'week') numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
                else numberOfDaysToAdd = parseInt(this.newcase.duration.input);

                // calculate and format end date
                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd, new Date(this.newcase.duration.startdate));
                this.newcase.duration.message = cdd + '.' + cmm + '.' + cy;

                // calculate and format starting date
                let startingDate = new Date(this.newcase.duration.startdate);
                var startingDay = startingDate.getDate();
                var startingMonth = startingDate.getMonth() + 1;
                var startingYear = startingDate.getFullYear();
                let startingDateMessage = startingDay + '.' + startingMonth + '.' + startingYear;


                this.newcase.duration.value = "startDay:" + startingDateMessage + "|" + this.newcase.duration.value;
                this.newcase.duration.value += "|lastDay:" + this.newcase.duration.message;
            }

        },
        confirmdeletecase(url) {
            this.$buefy.dialog.confirm(
                {
                    title: 'Confirm Case deletion',
                    message: '<strong class="bg-red-600 text-yellow-400 p-2">Do you want to delete this case and all the entries?</strong>',
                    cancelText: 'No',
                    confirmText: 'Yes DELETE',
                    hasIcon: true,
                    type: 'is-danger',
                    onConfirm: () => this.deleteCase(url)
                }
            );
        },
        deleteCase(url) {
            let self = this;
            axios.delete(url)
                .then(response => {
                    setTimeout(function () {
                        self.loading = false;
                        self.$buefy.snackbar.open("Case deleted");

                        window.location.reload();

                    }, 500);

                }).catch(function (error) {
                let message = "A problem occurred";
                if (error.response.data.message) message = error.response.data.message;
                self.loading = false;
                self.$buefy.snackbar.open(message);
            });
        },
        validateSubmitCaseForm() {
            this.newproject.response = "";
            if (this.newproject.name == "") this.newproject.response += this.errormessages.namemissing;

            if (this.newproject.ninputs > 0) {
                if (_.find(this.newproject.inputs, {name: ''})) this.newproject.response += this.errormessages.inputnamemissing;
                if (_.find(this.newproject.inputs, {type: ''})) this.newproject.response += this.errormessages.inputtypemissing;

                // if multiple or onechoice and no answers throw error
                if (_.find(this.newproject.inputs, function (o) {
                    if (o.type == 'multiple choice' || o.type == 'one choice' && (o.numberofanswer != o.answers.length)) return true;
                })) this.newproject.response += this.errormessages.multipleinputnoanswer;
            }


            if (this.newproject.response == "") return true;
            else return false

        },
        formatDurationMessage(numberOfDaysToAdd, startDate = new Date()) {
            var calculatedDate = startDate;
            //get today date
            var dd = calculatedDate.getDate();
            var mm = calculatedDate.getMonth() + 1;
            var y = calculatedDate.getFullYear();

            calculatedDate.setDate(calculatedDate.getDate() + numberOfDaysToAdd);
            var cdd = calculatedDate.getDate();
            var cmm = calculatedDate.getMonth() + 1;
            var cy = calculatedDate.getFullYear();
            return {cdd, cmm, cy};
        },
        handleMediaInputs(index, mediaName) {
            let tabKey = 9;
            let isLastElement = index + 1 == this.newproject.media.length;

            if (isLastElement) {
                if (mediaName != "") this.newproject.media.push("");

            }
            if (index != 0 && mediaName == "" && lastPressedKey != tabKey) this.newproject.media.splice(index, 1);

        },
        handleAdditionalInputs(questionindex, answerindex, answer) {
            let isLastElement = answerindex + 1 == this.newproject.inputs[questionindex].answers.length;

            if (isLastElement) {
                if (answer != "") this.newproject.inputs[questionindex].answers.push("");

            }
            //this.newproject.inputs[questionindex].id = this.createUUID(16);
            let tabKey = 9;
            let middleElementRemoved = answerindex != 0 && answer == "";
            if (middleElementRemoved && lastPressedKey != tabKey) {
                this.newproject.inputs[questionindex].answers.splice(answerindex, 1);
            }

            this.newproject.inputs[questionindex].numberofanswer = this.newproject.inputs[questionindex].answers.length - 1;
        },
        createUUID(length) {

            var dt = new Date().getTime();
            var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = (dt + Math.random() * 16) % 16 | 0;
                dt = Math.floor(dt / 16);
                return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(length);
            });
            return uuid;

        },
        validateProject(e) {
            var self = this;
            self.newproject.response = "";
            if (this.newproject.name === "") this.newproject.response = "Enter a project name <br>";
            if (this.newproject.description === "") this.newproject.response += "Enter a project description <br>";
            // if(this.newproject.media.length === 0 || this.newproject.media[0] === "")this.newproject.response +="Enter the list of media<br>";

            _.forEach(this.newproject.inputs, function (value) {
                console.log(value);
                if (value.numberofanswer == 0 && (value.type !== "text" && value.type !== "scale")) self.newproject.response += "Enter answers for each input<br>";
                if (value.name === "") self.newproject.response += "Enter a name for each input. <br>";

            });

            if (this.newproject.response !== "") e.preventDefault();
        },
        confirmLeaveProject: function (userToDetach, project) {

            let confirmDelete = this.$buefy.dialog.confirm(
                {
                    title: 'Confirm Delete',
                    message: 'Are you sure you want to leave this project?',
                    cancelText: 'No',
                    confirmText: 'YES remove me',
                    hasIcon: true,
                    type: 'is-danger',
                    onConfirm: () => this.detachUser(userToDetach, project)
                }
            );
        },
        detachUser: function (userToDetach, project) {

            let self = this;
            window.axios.post('/projects/invite/' + userToDetach.id, {email: userToDetach.email, project: project})
                .then(response => {

                    self.$buefy.snackbar.open(response.data.message);

                    setTimeout(function () {
                        window.location.reload();
                    }, 1000)


                }).catch(function (error) {

                self.$buefy.snackbar.open("There it was an error during the request - refresh page and try again");
            });
        },
        confirmDeleteProject: function (project, url) {

            let confirmDelete = this.$buefy.dialog.confirm(
                {
                    title: 'Confirm Delete',
                    message: '<strong class="bg-red-600 text-yellow-400 p-2">Are you sure you want to delete this project and all the data included with it?</strong>',
                    cancelText: 'NO',
                    confirmText: 'YES',
                    hasIcon: true,
                    type: 'is-danger',
                    onConfirm: () => this.deleteProject(project, url)
                }
            );
        },
        deleteProject: function (project, url) {

            let self = this;
            window.axios.delete(url, {project: project})
                .then(response => {

                    self.$buefy.snackbar.open(response.data.message);

                    setTimeout(function () {
                        window.location = window.location.href;
                    }, 700)


                }).catch(function (error, message) {
                self.$buefy.snackbar.open(error.response.data.message);
            });
        }
    }
});
