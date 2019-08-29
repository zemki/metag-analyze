/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import "vue-material-design-icons/styles.css"
import Buefy from 'buefy'
window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('edit-project', require('./components/editproject.vue').default);
Vue.component('consult-entries', require('./components/consultentries.vue').default);
Vue.component('project-invites', require('./components/projectsInvites.vue').default);

Vue.use(Buefy);
import {GoogleCharts} from 'google-charts';
import VueChartkick from 'vue-chartkick'

Vue.use(VueChartkick)
Vue.use(GoogleCharts)
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    computed: {
        'newproject.formattedinputstring': function () {
            console.log("WOWOWOa --> ");


            return JSON.stringify(this.newproject.inputs);
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
        'newcase.duration.selectedUnit': function (newVal, OldVal) {
            if (!_.isEmpty(this.newcase.duration.input)) {

                if (newVal === 'week') var numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
                else var numberOfDaysToAdd = parseInt(this.newcase.duration.input);

                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

                this.newcase.duration.message = cdd + '.' + cmm + '.' + cy;
                this.newcase.duration.value = "value:" + numberOfDaysToAdd * 24 + "|days:" + numberOfDaysToAdd;

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
        'newuser.case.name': function (newVal, oldVal) {
            if (this.newuser.case.project !== 0) {

                window.axios.post('/cases/exist', {name: newVal, project: this.newuser.case.project}).then(response => {
                    this.newuser.case.caseexist = response.data;
                    if (response.data) {
                        this.newuser.case.caseexistmessage = "This case exist!";
                    } else {
                        this.newuser.case.caseexistmessage = "This case will be created.";
                    }

                }).catch(error => {
                    console.log(error);
                });
            }

        },
        'newuser.case.project': function (newVal, oldVal) {
            if (this.newuser.case.name.length > 0) {
                window.axios.post('/cases/exist', {name: newVal, project: this.newuser.case.project}).then(response => {
                    this.newuser.case.caseexist = response.data;
                    if (response.data) {
                        this.newuser.case.caseexistmessage = "This case exist!";
                    } else {
                        this.newuser.case.caseexistmessage = "This case will be created.";
                    }

                }).catch(error => {
                    console.log(error);
                });
            }
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
                selectedUnit: "days",
                allowedUnits: ["day(s)", "week(s)"],
                message: "",
                value: "",
            }
        },
        newproject: {
            name: "",
            ninputs: 0,
            inputs: [],
            config: window.inputs,
            response: "",
            media: [""]
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

        }
    },
    methods: {
        replaceUndefinedOrNull() {

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
        formatDurationMessage(numberOfDaysToAdd) {
            var calculatedDate = new Date();
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

            let tabKey = 9;
            let middleElementRemoved = answerindex != 0 && answer == "";
            if (middleElementRemoved && lastPressedKey != tabKey) {
                this.newproject.inputs[questionindex].answers.splice(answerindex, 1);
            }

            this.newproject.inputs[questionindex].numberofanswer = this.newproject.inputs[questionindex].answers.length - 1;
        },
        validateProject(e) {
            var self = this;
            self.newproject.response = "";
            if (this.newproject.name === "") this.newproject.response = "Enter a project name <br>";
            if (this.newproject.description === "") this.newproject.response = "Enter a project description <br>";
            // if(this.newproject.media.length === 0 || this.newproject.media[0] === "")this.newproject.response +="Enter the list of media<br>";

            _.forEach(this.newproject.inputs, function (value) {
                console.log(value);
                if (value.numberofanswer == 0 && (value.type !== "text" && value.type !== "scale")) self.newproject.response += "Enter answers for each input<br>";
                if (value.name === "") self.newproject.response += "Enter a name for each input. <br>";

            });

            if (this.newproject.response !== "") e.preventDefault();
        },
        confirmLeaveProject: function (userToDetach,project) {

            let confirmDelete = this.$buefy.dialog.confirm(
                {
                    title: 'Confirm Delete',
                    message: 'Are you sure you want to leave this project?',
                    cancelText: 'Cancel',
                    confirmText: 'YES remove me',
                    hasIcon: true,
                    type: 'is-danger',
                    onConfirm: () => this.detachUser(userToDetach,project)
                }
            );
        },
        detachUser: function (userToDetach,project) {

            let self = this;
            window.axios.post('/projects/invite/'+userToDetach.id, {email: userToDetach.email, project: project})
                .then(response => {

                    self.$buefy.snackbar.open(response.data.message);

                    setTimeout(function(){
                        window.location.reload();
                    },1000)


                }).catch(function (error) {

                self.$buefy.snackbar.open("There it was an error during the request - refresh page and try again");
            });
        },
        confirmDeleteProject: function (project) {

            let confirmDelete = this.$buefy.dialog.confirm(
                {
                    title: 'Confirm Delete',
                    message: 'Are you sure you want to delete this project?',
                    cancelText: 'Cancel',
                    confirmText: 'YES',
                    hasIcon: true,
                    type: 'is-danger',
                    onConfirm: () => this.deleteProject(project)
                }
            );
        },
        deleteProject: function (project) {

            let self = this;
            window.axios.delete('/projects/'+project, {project: project})
                .then(response => {

                    self.$buefy.snackbar.open(response.data.message);

                    setTimeout(function(){
                        window.location.reload();
                    },1000)


                }).catch(function (error) {

                self.$buefy.snackbar.open(response.data.message);
            });
        }
    }
});
