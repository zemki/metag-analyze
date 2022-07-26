/* eslint-disable no-undef */
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import "./bootstrap";
import Buefy from "buefy";
import moment from "moment";
import HighchartsMore from "highcharts/highcharts-more";
import Vuex from "vuex";
import store from "./store";
import Vue from "vue";
import _ from "lodash";

window.Vue = Vue;

window.Vue.use(Vuex);

const Highcharts = require("highcharts");

// Load module after Highcharts is loaded
require("highcharts/modules/exporting")(Highcharts);
require("highcharts/modules/gantt")(Highcharts);

HighchartsMore(Highcharts);
require("./components");

window.Vue.config.devtools = true;
Vue.config.debug = true;
Vue.config.silent = false;
Vue.use(Buefy);

Vue.prototype.trans = (key) =>
  _.isUndefined(window.trans[key]) ? key : window.trans[key];

Vue.mixin({
  data() {
    return {
      productionUrl: process.env.MIX_ENV_MODE === "production" ? "/metag" : "",
    };
  },
});

window.app = new Vue({
  el: "#app",
  store,
  computed: {
    "newproject.formattedinputstring": function () {
      return JSON.stringify(this.newproject.inputs);
    },
    url() {
      return document.URL.split("/").pop();
    },
  },
  mounted() {
    window.addEventListener("keydown", function (e) {
      this.lastPressedKey = e.keyCode;
    });
    const replaceUndefinedOrNull = function (key, value) {
      if (value === null || value === undefined || value === "") {
        return undefined;
      }

      return value;
    };
  },
  watch: {
    "newcase.duration.starts_with_login": function (newVal, OldVal) {
      if (newVal) {
        if (
          !_.isEmpty(this.newcase.duration.selectedUnit) &&
          !_.isEmpty(this.newcase.duration.input)
        ) {
          if (this.newcase.duration.selectedUnit === "week") {
            let numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
          } else {
            let numberOfDaysToAdd = parseInt(this.newcase.duration.input);
          }

          const { cdd, cmm, cy } =
            this.formatDurationMessage(numberOfDaysToAdd);

          // duration in days and change to this after first login
          this.newcase.duration.message = `${cdd}.${cmm}.${cy}`;
          this.newcase.duration.value = `value:${
            numberOfDaysToAdd * 24
          }|days:${numberOfDaysToAdd}`;
        } else {
          this.newcase.duration.message = "";
          this.newcase.duration.value = "";
        }
      }
    },
    "newcase.duration.startdate": function (newVal, OldVal) {
      if (
        !_.isEmpty(this.newcase.duration.input) &&
        !_.isEmpty(this.newcase.duration.selectedUnit)
      ) {
        if (this.newcase.duration.selectedUnit === "week") {
          let numberOfDaysToAdd = parseInt(this.newcase.duration.input, 10) * 7;
        } else {
          let numberOfDaysToAdd = parseInt(this.newcase.duration.input, 10);
        }

        this.formatdatestartingat();
      }
    },
    "newcase.duration.selectedUnit": function (newVal, OldVal) {
      if (!_.isEmpty(this.newcase.duration.input)) {
        if (newVal === "week") {
          let numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
        } else {
          let numberOfDaysToAdd = parseInt(this.newcase.duration.input);
        }

        const { cdd, cmm, cy } = this.formatDurationMessage(numberOfDaysToAdd);

        this.newcase.duration.message = `${cdd}.${cmm}.${cy}`;
        this.newcase.duration.value = `value:${
          numberOfDaysToAdd * 24
        }|days:${numberOfDaysToAdd}`;

        this.formatdatestartingat();
      } else {
        this.newcase.duration.message = "";
        this.newcase.duration.value = "";
      }
    },
    "newcase.duration.input": function (newVal, OldVal) {
      this.newcase.duration.input = newVal.replace(/\D/g, "");
      let numberOfDaysToAdd;
      if (!_.isEmpty(this.newcase.duration.selectedUnit)) {
        if (this.newcase.duration.selectedUnit === "week") {
          numberOfDaysToAdd = parseInt(newVal) * 7;
        } else {
          numberOfDaysToAdd = parseInt(newVal);
        }

        const { cdd, cmm, cy } = this.formatDurationMessage(numberOfDaysToAdd);

        // duration in days and change to this after first login
        this.newcase.duration.message = `${cdd}.${cmm}.${cy}`;
        this.newcase.duration.value = `value:${
          numberOfDaysToAdd * 24
        }|days:${numberOfDaysToAdd}`;
        this.formatdatestartingat();
      } else {
        this.newcase.duration.message = "";
        this.newcase.duration.value = "";
      }
    },
    "newuser.case.duration.input": function (newVal, OldVal) {
      this.newuser.case.duration.input = newVal.replace(/\D/g, "");

      if (!_.isEmpty(this.newuser.case.duration.selectedUnit)) {
        if (this.newuser.case.duration.selectedUnit === "week") {
          let numberOfDaysToAdd = parseInt(newVal) * 7;
        } else {
          let numberOfDaysToAdd = parseInt(newVal);
        }

        const { cdd, cmm, cy } = this.formatDurationMessage(numberOfDaysToAdd);

        // duration in days and change to this after first login
        this.newuser.case.duration.message = `${cdd}.${cmm}.${cy}`;
        this.newuser.case.duration.value = `value:${
          numberOfDaysToAdd * 24
        }|days:${numberOfDaysToAdd}`;

        this.formatdatestartingat();
      } else {
        this.newuser.case.duration.message = "";
        this.newuser.case.duration.value = "";
      }
    },
    "newuser.case.duration.selectedUnit": function (newVal, OldVal) {
      if (!_.isEmpty(this.newuser.case.duration.input)) {
        if (newVal === "week") {
          let numberOfDaysToAdd =
            parseInt(this.newuser.case.duration.input) * 7;
        } else {
          let numberOfDaysToAdd = parseInt(this.newuser.case.duration.input);
        }

        const { cdd, cmm, cy } = this.formatDurationMessage(numberOfDaysToAdd);

        this.newuser.case.duration.message = `${cdd}.${cmm}.${cy}`;
        this.newuser.case.duration.value = `value:${
          numberOfDaysToAdd * 24
        }|days:${numberOfDaysToAdd}`;

        this.formatdatestartingat();
      } else {
        this.newuser.case.duration.message = "";
        this.newuser.case.duration.value = "";
      }
    },
    "newuser.email": function (newVal, oldVal) {
      window.axios
        .post("/users/exist", { email: newVal })
        .then((response) => {
          this.newuser.emailexist = response.data;
          if (response.data) {
            this.newuser.emailexistmessage = "This user will be invited.";
          } else {
            this.newuser.emailexistmessage =
              "This user is not registered, an invitation email will be sent.";
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    "newproject.ninputs": function (newVal, oldVal) {
      if (newVal < 0 || oldVal < 0) {
        newVal = 0;
        oldVal = 0;
      }

      const direction = newVal - oldVal;

      if (direction > 0) {
        const inputtemplate = {
          name: "",
          type: "",
          mandatory: true,
          numberofanswer: 0,
          answers: [""],
        };

        for (let i = 0; i < direction; i++) {
          this.newproject.inputs.push(inputtemplate);
        }
      } else if (newVal == 0) {
        // special case
        this.newproject.inputs = [];
      } else {
        // decrease
        for (let i = 0; i < Math.abs(direction); i++) {
          this.newproject.inputs.pop();
        }
      }
    },
  },
  data: {
    selectedCase: {},
    mainNotification: true,
    lastPressedKey: "",
    selectedEntriesData: [],
    showentriestable: false,
    errormessages: {
      namemissing: "name is required. <br>",
      inputnamemissing: "input name is required. <br>",
      inputtypemissing: "input type is required. <br>",
      multipleinputnoanswer: "provide a valid number of answers. <br>",
    },
    newcase: {
      name: "",
      duration: {
        input: "",
        starts_with_login: true,
        selectedUnit: "days",
        allowedUnits: ["day(s)", "week(s)"],
        message: "",
        value: "",
      },
      minDate: new Date(),
      backendcase: false,
      inputLength: {
        name: 200,
      },
      response: "",
    },
    newproject: {
      name: "",
      ninputs: 0,
      inputs: [],
      config: window.inputs,
      response: "",
      description: "",
      media: [""],
      inputLength: {
        name: 200,
        description: 255,
      },
    },
    newentry: {
      case_id: 0,
      inputs: {},
      modal: false,
      data: {
        start: new Date(),
        end: new Date(new Date().setMinutes(new Date().getMinutes() + 5)),
        media_id: "",
        inputs: {},
      },
    },
    editentry: {
      id: 0,
      case_id: 0,
      inputs: {},
      modal: false,
      data: {
        start: new Date(),
        end: new Date(new Date().setMinutes(new Date().getMinutes() + 1)),
        media_id: "",
        inputs: {},
      },
    },
    registration: {
      password: null,
      password_length: 0,
      contains_six_characters: false,
      contains_number: false,
      contains_letters: false,
      contains_special_character: false,
      valid_password: false,
      email: "",
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
        caseexist: false,
      },
      project: 0,
      tooltipActive: false,
    },
    chart: {
      typeSelect: {
        pdf: "application/pdf",
        png: "image/png",
        svg: "image/svg+xml",
      },
      type: "application/pdf",
    },
  },
  methods: {
    newentrydateselected(edit = "") {
      if (edit === "") {
        this.newentry.data.end = new Date(
          new Date(this.newentry.data.start).setMinutes(
            new Date(this.newentry.data.start).getMinutes() + 5
          )
        );
      } else {
        this.editentry.data.end = new Date(
          new Date(this.editentry.data.start).setMinutes(
            new Date(this.editentry.data.start).getMinutes() + 5
          )
        );
      }
    },
    iWantNewsletter(will) {
      const subscribed = will === "true";
      const self = this;
      axios
        .post("users/subscribe", { subscribed })
        .then((response) => {
          console.log(response);
          self.$buefy.snackbar.open(response.data.message);

          const newsDiv = document.querySelector(".newsletter");

          newsDiv.classList.remove("opacity-100");
          newsDiv.classList.add("opacity-0");
          setTimeout(() => {
            newsDiv.classList.add("hidden");
          }, 500);

          self.$forceUpdate();
        })
        .catch((error) => {
          console.log(error);

          self.$buefy.snackbar.open(
            "There it was an error during the request - refresh page and try again"
          );
        });
    },
    confirmdelete(case_id, entry_id, lastentry) {
      // show a toast message using vuejs and tailwindcss
      const self = this;

      const confirmDelete = this.$buefy.dialog.confirm({
        title: "Confirm Delete",
        message:
          '<div class="bg-red-600 p-2 text-white text-center">You re about to delete this Entry.<br><span class="has-text-weight-bold">Continue?</span></div>',
        cancelText: "Cancel",
        confirmText: "YES delete Entry",
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.deleteEntry(case_id, entry_id, lastentry),
      });
    },
    deleteEntry(case_id, entry_id, lastentry) {
      this.loading = true;
      this.message = "";
      const self = this;

      window.axios
        .delete(
          `${
            window.location.origin + this.productionUrl
          }/cases/${case_id}/entries/${entry_id}`
        )
        .then((response) => {
          setTimeout(() => {
            self.loading = false;
            self.$buefy.snackbar.open("Entry deleted");

            if (!lastentry) {
              window.location.reload();
            } else {
              window.location.href = "../";
            }
          }, 500);
        })
        .catch((error) => {
          self.loading = false;
          self.$buefy.snackbar.open(
            "There it was an error during the request - refresh page and try again"
          );
        });
    },
    entrySaveAndClose() {
      if (this.MandatoryNewEntry()) {
        this.$buefy.snackbar.open(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      window.axios
        .post(
          `${window.location.origin + this.productionUrl}/cases/${
            this.newentry.case_id
          }/entries`,
          {
            case_id: this.newentry.case_id,
            inputs: this.newentry.data.inputs,
            begin: moment(this.newentry.data.start).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            end: moment(this.newentry.data.end).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            media_id: this.newentry.data.media_id,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(self.trans("Entry successfully sent."));
        })
        .catch((error) => {
          self.$buefy.snackbar.open(
            self.trans(
              "There it was an error during the request - refresh page and try again"
            )
          );
        });

      this.toggleModal();
      this.newentry.data = {};
    },
    entrySaveAndNewEntry() {
      if (this.MandatoryNewEntry()) {
        this.$buefy.snackbar.open(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      window.axios
        .post(
          `${window.location.origin + this.productionUrl}/cases/${
            this.newentry.case_id
          }/entries`,
          {
            case_id: this.newentry.case_id,
            inputs: this.newentry.data.inputs,
            begin: moment(this.newentry.data.start).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            end: moment(this.newentry.data.end).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            media_id: this.newentry.data.media_id,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(self.trans("Entry successfully sent."));
          self.newentry.data.inputs = {};
          self.newentry.data.media_id = "";
          self.newentry.data.start = new Date();
          self.newentry.data.end = new Date(
            new Date().setMinutes(new Date().getMinutes() + 5)
          );
        })
        .catch((error) => {
          self.$buefy.snackbar.open(
            self.trans(
              "There it was an error during the request - double check your data or contact the support."
            )
          );
        });
    },
    MandatoryNewEntry() {
      const self = this;
      return (
        _.isEmpty(self.newentry.data.media_id) ||
        self.newentry.data.start === "" ||
        self.newentry.data.end === ""
      );
    },
    MandatoryEditEntry() {
      const self = this;
      return (
        _.isEmpty(self.editentry.data.media_id) ||
        self.editentry.data.start === "" ||
        self.editentry.data.end === ""
      );
    },
    editEntryAndClose() {
      if (this.MandatoryEditEntry()) {
        this.$buefy.snackbar.open(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      window.axios
        .patch(
          `${window.location.origin + this.productionUrl}/cases/${
            this.editentry.case_id
          }/entries/${this.editentry.id}`,
          {
            case_id: this.editentry.case_id,
            inputs: this.editentry.data.inputs,
            begin: moment(this.editentry.data.start).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            end: moment(this.editentry.data.end).format(
              "YYYY-MM-DD HH:mm:ss.SSSSSS"
            ),
            media_id: this.editentry.data.media_id,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(self.trans("Entry successfully updated."));
          setTimeout(() => window.location.reload(), 500);
        })
        .catch((error) => {
          self.$buefy.snackbar.open(
            self.trans(
              "There it was an error during the request - double check your data or contact the support."
            )
          );
        });
    },
    toggleModal(id = "", inputs = {}) {
      this.newentry.case_id = id;
      this.newentry.inputs = inputs;
      this.newentry.modal = !this.newentry.modal;
      const body = document.querySelector("body");
      const modal = document.querySelector(".modal");
      modal.classList.toggle("opacity-0");
      modal.classList.toggle("pointer-events-none");
      body.classList.toggle("modal-active");
    },
    toggleEntryModal(
      entry = {
        id: null,
        case_id: null,
        inputs: {},
        data: {},
        begin: null,
        end: null,
      },
      inputs
    ) {
      this.editentry.id = entry.id;
      this.editentry.case_id = entry.case_id;
      this.editentry.inputs = inputs;
      this.editentry.data.inputs = entry.inputs;
      this.editentry.data.media_id = entry.media_id;
      this.editentry.data.start = moment(
        entry.begin,
        "YYYY-MM-DD HH:mm"
      ).toDate();
      this.editentry.data.end = moment(entry.end, "YYYY-MM-DD HH:mm").toDate();
      this.editentry.modal = !this.editentry.modal;
      const body = document.querySelector("body");
      const modal = document.querySelector(".modal");
      modal.classList.toggle("opacity-0");
      modal.classList.toggle("pointer-events-none");
      body.classList.toggle("modal-active");
    },
    checkPassword() {
      this.registration.password_length = this.registration.password.length;
      const special_chars = /[ !@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;

      if (this.registration.password_length > 5) {
        this.registration.contains_six_characters = true;
      } else {
        this.registration.contains_six_characters = false;
      }

      this.registration.contains_number = /\d/.test(this.registration.password);
      this.registration.contains_letters = /[a-z]/.test(
        this.registration.password
      );
      this.registration.contains_special_character = special_chars.test(
        this.registration.password
      );

      if (
        this.registration.contains_six_characters === true &&
        this.registration.contains_letters === true &&
        this.registration.contains_number === true
      ) {
        this.registration.valid_password = true;
      } else {
        this.registration.valid_password = false;
      }

      if (this.registration.password === this.registration.email) {
        this.registration.valid_password = false;
      }
    },
    formatdatestartingat() {
      if (!this.newcase.duration.starts_with_login) {
        var numberOfDaysToAdd;
        if (this.newcase.duration.selectedUnit === "week") {
          numberOfDaysToAdd = parseInt(this.newcase.duration.input) * 7;
        } else {
          numberOfDaysToAdd = parseInt(this.newcase.duration.input);
        }

        // calculate and format end date
        const { cdd, cmm, cy } = this.formatDurationMessage(
          numberOfDaysToAdd,
          new Date(this.newcase.duration.startdate)
        );
        this.newcase.duration.message = `${cdd}.${cmm}.${cy}`;

        // calculate and format starting date
        const startingDate = new Date(this.newcase.duration.startdate);
        const startingDay = startingDate.getDate();
        const startingMonth = startingDate.getMonth() + 1;
        const startingYear = startingDate.getFullYear();
        const startingDateMessage = `${startingDay}.${startingMonth}.${startingYear}`;

        this.newcase.duration.value = `startDay:${startingDateMessage}|${this.newcase.duration.value}`;
        this.newcase.duration.value += `|lastDay:${this.newcase.duration.message}`;
      }
    },
    confirmdeletecase(url) {
      this.$buefy.dialog.confirm({
        title: "Confirm Case deletion",
        message:
          '<strong class="bg-red-600 text-yellow-400 p-2">Do you want to delete this case and all the entries?</strong>',
        cancelText: "No",
        confirmText: "Yes DELETE",
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.deleteCase(url),
      });
    },
    deleteCase(url) {
      const self = this;
      axios
        .delete(url)
        .then((response) => {
          setTimeout(() => {
            self.loading = false;
            self.$buefy.snackbar.open("Case deleted");

            window.location.reload();
          }, 500);
        })
        .catch((error) => {
          let message = "A problem occurred";
          if (error.response.data.message) {
            message = error.response.data.message;
          }
          self.loading = false;
          self.$buefy.snackbar.open(message);
        });
    },
    validateSubmitCaseForm() {
      this.newproject.response = "";
      if (this.newproject.name == "") {
        this.newproject.response += this.errormessages.namemissing;
      }

      if (this.newproject.ninputs > 0) {
        if (_.find(this.newproject.inputs, { name: "" })) {
          this.newproject.response += this.errormessages.inputnamemissing;
        }
        if (_.find(this.newproject.inputs, { type: "" })) {
          this.newproject.response += this.errormessages.inputtypemissing;
        }

        // if multiple or onechoice and no answers throw error
        if (
          _.find(this.newproject.inputs, (o) => {
            if (
              o.type == "multiple choice" ||
              (o.type == "one choice" && o.numberofanswer != o.answers.length)
            ) {
              return true;
            }
          })
        ) {
          this.newproject.response += this.errormessages.multipleinputnoanswer;
        }
      }

      if (this.newproject.response == "") {
        return true;
      }
      return false;
    },
    formatDurationMessage(numberOfDaysToAdd, startDate = new Date()) {
      const calculatedDate = startDate;
      // get today date
      const dd = calculatedDate.getDate();
      const mm = calculatedDate.getMonth() + 1;
      const y = calculatedDate.getFullYear();

      calculatedDate.setDate(calculatedDate.getDate() + numberOfDaysToAdd);
      const cdd = calculatedDate.getDate();
      const cmm = calculatedDate.getMonth() + 1;
      const cy = calculatedDate.getFullYear();
      return {
        cdd,
        cmm,
        cy,
      };
    },
    handleMediaInputs(index, mediaName) {
      const tabKey = 9;
      const isLastElement = index + 1 === this.newproject.media.length;

      if (isLastElement) {
        if (mediaName !== "") {
          this.newproject.media.push("");
        }
      }

      if (index != 0 && mediaName === "" && lastPressedKey !== tabKey) {
        this.newproject.media.splice(index, 1);
      }
    },
    handleAdditionalInputs(questionindex, answerindex, answer) {
      const isLastElement =
        answerindex + 1 ===
        this.newproject.inputs[questionindex].answers.length;
      const shiftKey = 16;

      if (lastPressedKey === shiftKey) return;
      if (isLastElement) {
        if (answer !== "") {
          this.newproject.inputs[questionindex].answers.push("");
        }
      }
      // this.newproject.inputs[questionindex].id = this.createUUID(16);
      const tabKey = 9;
      const middleElementRemoved = answerindex !== 0 && answer == "";
      if (middleElementRemoved && lastPressedKey !== tabKey) {
        this.newproject.inputs[questionindex].answers.splice(answerindex, 1);
      }

      this.newproject.inputs[questionindex].numberofanswer =
        this.newproject.inputs[questionindex].answers.length - 1;
    },
    createUUID(length) {
      let dt = new Date().getTime();
      const uuid = "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
        /[xy]/g,
        (c) => {
          const r = (dt + Math.random() * 16) % 16 | 0;
          dt = Math.floor(dt / 16);
          return (c == "x" ? r : (r & 0x3) | 0x8).toString(length);
        }
      );
      return uuid;
    },
    validateCase(e) {
      const self = this;
      self.newcase.response = "";
      if (this.newcase.name === "") {
        this.newcase.response = "Enter a case name <br>";
      }
      if (this.newcase.name.length > 200) {
        this.newcase.response += "Case name is too long <br>";
      }
      if (this.newcase.response !== "") {
        e.preventDefault();
      }
    },
    validateProject(e) {
      const self = this;
      self.newproject.response = "";
      if (this.newproject.name === "") {
        this.newproject.response = "Enter a project name <br>";
      }

      if (this.newproject.name.length > 200) {
        this.newproject.response += "Project name is too long <br>";
      }

      if (this.newproject.description === "") {
        this.newproject.response += "Enter a project description <br>";
      }

      if (this.newproject.description.length > 255) {
        this.newproject.response += "Description is too long <br>";
      }

      // if(this.newproject.media.length === 0 || this.newproject.media[0] === "")this.newproject.response +="Enter the list of media<br>";

      let onlyOneAudio = Object.values(
        _.pickBy(this.newproject.inputs, (e) => e.type === "audio recording")
      );
      if (onlyOneAudio.length > 1)
        this.newproject.response += "please pick only one audio<br>";

      _.forEach(this.newproject.inputs, (value) => {
        if (
          value.numberofanswer == 0 &&
          value.type !== "text" &&
          value.type !== "scale" &&
          value.type !== "audio recording"
        ) {
          self.newproject.response += "Enter answers for each input<br>";
        }

        if (value.name === "") {
          self.newproject.response += "Enter a name for each input. <br>";
        }
      });

      if (this.newproject.response !== "") {
        e.preventDefault();
      }
    },
    confirmLeaveProject(userToDetach, project) {
      const confirmDelete = this.$buefy.dialog.confirm({
        title: "Confirm Leave",
        message: "Are you sure you want to leave this project?",
        cancelText: "No",
        confirmText: "YES remove me",
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.detachUser(userToDetach, project),
      });
    },
    detachUser(userToDetach, project) {
      const self = this;
      window.axios
        .post(
          `${window.location.origin + self.productionUrl}/projects/invite/${
            userToDetach.id
          }`,
          {
            email: userToDetach.email,
            project,
          }
        )
        .then((response) => {
          self.$buefy.snackbar.open(response.data.message);

          setTimeout(() => {
            window.location.reload();
          }, 1000);
        })
        .catch((error) => {
          self.$buefy.snackbar.open(
            "There it was an error during the request - refresh page and try again"
          );
        });
    },
    confirmDeleteProject(project, url) {
      const confirmDelete = this.$buefy.dialog.confirm({
        title: "Confirm Delete",
        message:
          '<strong class="bg-red-600 text-yellow-400 p-2">Are you sure you want to delete this project and all the data included with it?</strong>',
        cancelText: "NO",
        confirmText: "YES",
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.deleteProject(project, url),
      });
    },
    deleteProject(project, url) {
      const self = this;
      window.axios
        .delete(url, { project })
        .then((response) => {
          self.$buefy.snackbar.open(response.data.message);

          setTimeout(() => {
            window.location = window.location.href;
          }, 700);
        })
        .catch((error, message) => {
          self.$buefy.snackbar.open(error.response.data.message);
        });
    },
    switchMediaAndInputsOnGraph() {
      this.$store.commit("switchyAxisAttribute", false);
    },
    downloadChart() {
      this.$refs.graph.download(this.chart.type);
    },
    switchFormatter() {
      this.$store.commit("switchFormatter", false);
    },
  },
});
