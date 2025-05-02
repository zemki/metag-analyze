/* eslint-disable no-undef */
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import { createApp } from "vue";
import Alpine from "alpinejs";
import "./bootstrap";
import { components } from "./components";
import moment from "moment";
import HighchartsMore from "highcharts/highcharts-more";
import store from "./store";
import "altcha";
import Highcharts from "highcharts";
import exporting from "highcharts/modules/exporting";
import gantt from "highcharts/modules/gantt";
import stock from "highcharts/modules/stock";
import mitt from "mitt";

// Create a global event emitter
export const emitter = mitt();

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Load Highcharts modules
exporting(Highcharts);
gantt(Highcharts);
HighchartsMore(Highcharts);
stock(Highcharts);

// Create the Vue application
const app = createApp({
  // Using Options API for now to ensure backward compatibility
  // This will make migration smoother as we convert components to Composition API
  // The root instance needs to provide data and methods for components that expect them
  data() {
    return {
      productionUrl:
        import.meta.env.VITE_ENV_MODE === "production" ? "/metag" : "",
      newemail: {
        valid_email: false,
        email: "",
        message: "",
      },
      moment: moment,
      selectedProjectPage: 0,
      disabledDates: [
        function (date) {
          return new Date(date) <= new Date();
        },
      ],
      editentry: {
        id: 0,
        case_id: 0,
        inputs: {},
        modal: false,
        actuallysave: false,
        data: {
          start: new Date(),
          end: new Date(new Date().setMinutes(new Date().getMinutes() + 1)),
          media_id: "",
          media: "",
          inputs: {},
        },
      },
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
        sendanywayemail: false,
      },
      newproject: {
        name: "",
        ninputs: 0,
        inputs: [],
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
      dialog: {
        show: false,
        title: "",
        message: "",
        confirmText: "",
        onConfirm: null,
        onCancel: null,
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
        valid_email: true,
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
      snackbarMessage: "",
      showSnackbar: false,
    };
  },
  computed: {
    "newproject.formattedinputstring": function () {
      return JSON.stringify(this.newproject.inputs);
    },
    url() {
      return document.URL.split("/").pop();
    },
  },
  mounted() {
    // Store the event handler as a method property so we can remove it later
    this.handleKeyDown = (e) => {
      this.lastPressedKey = e.key || e.keyCode; // Using e.key is more modern
    };
    window.addEventListener("keydown", this.handleKeyDown);

    // Check for stored message on page load
    const storedMessage = localStorage.getItem("snackbarMessage");
    if (storedMessage) {
      this.showSnackbarMessage(storedMessage);
      // Clear the message after showing it
      localStorage.removeItem("snackbarMessage");
    }

    // Listen for snackbar events from components using mitt
    emitter.on("show-snackbar", (message) => {
      this.showSnackbarMessage(message);
    });
  },

  // Vue 3 uses beforeUnmount instead of beforeDestroy
  beforeUnmount() {
    // Clean up event listeners
    window.removeEventListener("keydown", this.handleKeyDown);
    emitter.off("show-snackbar");
  },
  watch: {
    // Consolidated Watchers for newcase.duration
    "newcase.duration.starts_with_login": function (newVal, oldVal) {
      if (newVal) {
        this.handleDurationChange("newcase");
      }
    },
    "newcase.duration.startdate": function (newVal, oldVal) {
      this.handleDurationChange("newcase");
    },
    "newcase.duration.selectedUnit": function (newVal, oldVal) {
      this.handleDurationChange("newcase");
    },
    "newcase.duration.input": function (newVal, oldVal) {
      // Sanitize input to contain only digits
      this.newcase.duration.input = newVal.replace(/\D/g, "");
      this.handleDurationChange("newcase");
    },

    // Consolidated Watchers for newuser.case.duration
    "newuser.case.duration.selectedUnit": function (newVal, oldVal) {
      this.handleDurationChange("newuser");
    },
    "newuser.case.duration.input": function (newVal, oldVal) {
      // Sanitize input to contain only digits
      this.newuser.case.duration.input = newVal.replace(/\D/g, "");
      this.handleDurationChange("newuser");
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
        this.newproject.ninputs = 0;
        return;
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
          this.newproject.inputs.push(
            JSON.parse(JSON.stringify(inputtemplate))
          );
        }
      } else if (newVal === 0) {
        // Special case
        this.newproject.inputs = [];
      } else {
        // Decrease
        for (let i = 0; i < Math.abs(direction); i++) {
          this.newproject.inputs.pop();
        }
      }
    },
  },

  methods: {
    showSnackbarMessage(message) {
      // This will access the snackbar component via ref and call its show method
      if (this.$refs && this.$refs.snackbar) {
        this.$refs.snackbar.message = message;
        this.$refs.snackbar.show();
      } else {
        // Use the emitter as a fallback
        emitter.emit("show-snackbar", message);
      }
    },
    // Reusable method to handle duration changes
    handleDurationChange(caseType) {
      let durationData;
      if (caseType === "newcase") {
        durationData = this.newcase.duration;
      } else if (caseType === "newuser") {
        durationData = this.newuser.case.duration;
      } else {
        return;
      }

      if (
        durationData.input &&
        durationData.input.trim() !== "" &&
        durationData.selectedUnit &&
        durationData.selectedUnit.trim() !== ""
      ) {
        let numberOfDaysToAdd = 0;
        if (durationData.selectedUnit.toLowerCase() === "week") {
          numberOfDaysToAdd = parseInt(durationData.input, 10) * 7;
        } else {
          numberOfDaysToAdd = parseInt(durationData.input, 10);
        }

        if (!isNaN(numberOfDaysToAdd)) {
          const { cdd, cmm, cy } = this.formatDurationMessage(
            numberOfDaysToAdd,
            durationData.startdate
              ? new Date(durationData.startdate)
              : new Date()
          );
          durationData.message = `${cdd}.${cmm}.${cy}`;
          durationData.value = `value:${
            numberOfDaysToAdd * 24
          }|days:${numberOfDaysToAdd}`;

          this.formatdatestartingat();
        } else {
          console.warn(
            `Invalid duration input for ${caseType}:`,
            durationData.input
          );
          durationData.message = "";
          durationData.value = "";
        }
      } else {
        durationData.message = "";
        durationData.value = "";
      }
    },

    sendEmail() {
      let self = this;
      var re =
        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if (re.test(String(this.newemail.email).toLowerCase()))
        this.newemail.valid_email = true;
      else this.newemail.valid_email = false;

      if (!this.newemail.valid_email) {
        // Use refs instead of direct DOM manipulation when possible
        const newEmailElement =
          this.$refs.newemail || document.getElementById("newemail");
        if (
          newEmailElement &&
          !newEmailElement.classList.contains("border-red-500")
        ) {
          newEmailElement.classList.add("border-red-500");
        }
        return;
      }
      axios
        .post("changeemail", { email: self.newemail.email })
        .then((response) => {
          // remove the class border-red-500 from the input with id newemail if it's present
          const newEmailElement = document.getElementById("newemail");
          if (
            newEmailElement &&
            newEmailElement.classList.contains("border-red-500")
          ) {
            newEmailElement.classList.remove("border-red-500");
          }

          self.newemail.message = response.data;
        })
        .catch(function (error) {
          self.newemail.message = error;
        });
    },
    toggleModalChangeEmail() {
      const body = document.querySelector("body");
      const modal = document.querySelector(".modal");
      modal.classList.toggle("opacity-0");
      modal.classList.toggle("pointer-events-none");
      body.classList.toggle("modal-active");

      this.newemail.email = "";
      this.newemail.message = "";
    },
    handleSelectedCase(currentCase) {
      this.selectedCase = currentCase;
    },
    catchOutsideClick(event, dropdown) {
      // When user clicks menu — do nothing
      if (dropdown == event.target) return false;

      // When user clicks outside of the menu — close the menu
      if (!dropdown.classList.contains("hidden") && dropdown != event.target)
        return true;
    },
    showdropdown: function (id) {
      var self = this;

      const closeListener = (e) => {
        if (self.catchOutsideClick(e, self.$refs.usermenu)) {
          window.removeEventListener("click", closeListener);
          const dropdownElement = document.getElementById(id);
          if (dropdownElement) {
            dropdownElement.classList.toggle("hidden");
          }
        }
      };

      window.addEventListener("click", closeListener);
      const dropdownElement = document.getElementById(id);
      if (dropdownElement) {
        dropdownElement.classList.toggle("hidden");
      }
    },
    confirmdelete(case_id, entry_id, lastentry) {
      this.dialog = {
        show: true,
        title: this.trans("Confirm Delete"),
        message: this.trans("You are about to delete this Entry. Continue?"),
        confirmText: this.trans("YES delete Entry"),
        onConfirm: () => this.deleteEntry(case_id, entry_id, lastentry),
        onCancel: () => {
          this.dialog.show = false;
        },
      };
    },
    deleteEntry(case_id, entry_id, lastentry) {
      this.loading = true;
      this.message = "";
      const self = this;

      axios
        .delete(
          `${
            window.location.origin + this.productionUrl
          }/cases/${case_id}/entries/${entry_id}`
        )
        .then((response) => {
          setTimeout(() => {
            self.loading = false;
            self.showSnackbarMessage(self.trans("Entry deleted."));

            if (!lastentry) {
              window.location.reload();
            } else {
              window.location.href = "../";
            }
          }, 500);
        })
        .catch((error) => {
          self.loading = false;
          self.showSnackbarMessage(
            self.trans(
              "There was an error during the request - refresh page and try again"
            )
          );
        });
    },
    entrySaveAndClose() {
      if (this.MandatoryNewEntry()) {
        this.showSnackbarMessage(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      axios
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
          self.showSnackbarMessage(self.trans("Entry successfully sent."));
        })
        .catch((error) => {
          self.showSnackbarMessage(
            self.trans(
              "There was an error during the request - refresh page and try again"
            )
          );
        });

      this.toggleModal();
      this.newentry.data = {};
    },
    entrySaveAndNewEntry() {
      if (this.MandatoryNewEntry()) {
        this.showSnackbarMessage(this.trans("Check your mandatory entries."));

        return;
      }

      const self = this;
      axios
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
          self.showSnackbarMessage(self.trans("Entry successfully sent."));
          self.newentry.data.inputs = {};
          self.newentry.data.media_id = "";
          self.newentry.data.start = new Date();
          self.newentry.data.end = new Date(
            new Date().setMinutes(new Date().getMinutes() + 5)
          );
        })
        .catch((error) => {
          self.showSnackbarMessage(
            self.trans(
              "There was an error during the request - double check your data or contact the support."
            )
          );
        });
    },
    MandatoryNewEntry() {
      return (
        !this.newentry.data.media_id ||
        !this.newentry.data.start ||
        !this.newentry.data.end
      );
    },
    MandatoryEditEntry() {
      return (
        !this.editentry.data.media_id ||
        !this.editentry.data.start ||
        !this.editentry.data.end
      );
    },
    editEntryAndClose() {
      if (this.MandatoryEditEntry()) {
        this.showSnackbarMessage(this.trans("Check your mandatory entries."));
        return;
      }

      const self = this;
      axios
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
          self.showSnackbarMessage(self.trans("Entry successfully updated."));
          setTimeout(() => window.location.reload(), 500);
        })
        .catch((error) => {
          self.showSnackbarMessage(
            self.trans(
              "There was an error during the request - double check your data or contact the support."
            )
          );
        });
    },
    toggleModal(id = "", inputs = {}) {
      this.newentry.case_id = id;
      this.newentry.inputs = inputs;
      this.newentry.modal = !this.newentry.modal;
      this.editentry.inputs = inputs;
      this.editentry.case_id = id;
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
    },
    checkEmail() {
      let self = this;
      var re =
        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if (re.test(String(this.registration.email).toLowerCase()))
        this.registration.valid_email = true;
      else this.registration.valid_email = false;
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
        let numberOfDaysToAdd = 0;
        if (this.newcase.duration.selectedUnit === "week") {
          numberOfDaysToAdd = parseInt(this.newcase.duration.input, 10) * 7;
        } else {
          numberOfDaysToAdd = parseInt(this.newcase.duration.input, 10);
        }

        if (!isNaN(numberOfDaysToAdd)) {
          // Calculate and format end date
          const { cdd, cmm, cy } = this.formatDurationMessage(
            numberOfDaysToAdd,
            new Date(this.newcase.duration.startdate)
          );
          this.newcase.duration.message = `${cdd}.${cmm}.${cy}`;

          // Calculate and format starting date
          const startingDate = new Date(this.newcase.duration.startdate);
          const startingDay = startingDate.getDate();
          const startingMonth = startingDate.getMonth() + 1;
          const startingYear = startingDate.getFullYear();
          const startingDateMessage = `${startingDay}.${startingMonth}.${startingYear}`;

          this.newcase.duration.value = `startDay:${startingDateMessage}|value:${
            numberOfDaysToAdd * 24
          }|days:${numberOfDaysToAdd}|lastDay:${this.newcase.duration.message}`;
        } else {
          console.warn("Invalid duration input:", this.newcase.duration.input);
          this.newcase.duration.message = "";
          this.newcase.duration.value = "";
        }
      }
    },
    validateSubmitCaseForm() {
      this.newproject.response = "";
      if (this.newproject.name === "") {
        this.newproject.response += this.errormessages.namemissing;
      }

      if (this.newproject.inputs.some((input) => input.name === "")) {
        this.newproject.response += this.errormessages.inputnamemissing;
      }
      if (this.newproject.inputs.some((input) => input.type === "")) {
        this.newproject.response += this.errormessages.inputtypemissing;
      }

      // if multiple or onechoice and no answers throw error
      if (
        this.newproject.inputs.some(
          (input) =>
            input.type === "multiple choice" ||
            (input.type === "one choice" &&
              input.numberofanswer !== input.answers.length)
        )
      ) {
        this.newproject.response += this.errormessages.multipleinputnoanswer;
      }

      if (this.newproject.response === "") {
        return true;
      }
      return false;
    },
    formatDurationMessage(numberOfDaysToAdd, startDate = new Date()) {
      const calculatedDate = new Date(startDate);
      // Add days to the start date
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

      if (index !== 0 && mediaName === "" && this.lastPressedKey !== tabKey) {
        this.newproject.media.splice(index, 1);
      }
    },
    showDropdownInputs: function (index) {
      var element = document.getElementById("type" + index);

      if (element) {
        element.classList.toggle("hidden");
      }
      this.$forceUpdate();
    },
    handleAdditionalInputs(questionindex, answerindex, answer) {
      let isLastElement =
        answerindex + 1 ===
        this.newproject.inputs[questionindex].answers.length;

      if (isLastElement) {
        if (answer !== "")
          this.newproject.inputs[questionindex].answers.push("");
      }

      let middleElementRemoved = answerindex !== 0 && answer === "";
      if (middleElementRemoved) {
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
          return (c === "x" ? r : (r & 0x3) | 0x8).toString(length);
        }
      );
      return uuid;
    },
    validateCase(e) {
      const self = this;
      self.newcase.response = "";
      if (this.newcase.name === "") {
        this.newcase.response += "Enter a case name <br>";
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
        this.newproject.response += "Enter a project name <br>";
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

      // Replace _.pickBy with native JavaScript
      const onlyOneAudio = Object.values(
        Object.fromEntries(
          Object.entries(this.newproject.inputs).filter(
            ([_, e]) => e.type === "audio recording"
          )
        )
      );

      if (onlyOneAudio.length > 1)
        this.newproject.response += "Please pick only one audio<br>";

      // Replace _.forEach with native forEach
      this.newproject.inputs.forEach((value) => {
        if (
          value.numberofanswer === 0 &&
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
      this.dialog = {
        show: true,
        title: this.trans("Confirm Leave"),
        message: this.trans("Are you sure you want to leave this project?"),
        confirmText: this.trans("YES remove me"),
        onConfirm: () => this.detachUser(userToDetach, project),
        onCancel: () => {
          this.dialog.show = false;
        },
      };
    },
    detachUser(userToDetach, project) {
      const self = this;
      axios
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
          self.showSnackbarMessage(self.trans(response.data.message));

          setTimeout(() => {
            window.location.reload();
          }, 1000);
        })
        .catch((error) => {
          self.showSnackbarMessage(
            "There was an error during the request - refresh page and try again"
          );
        });
    },

    switchMediaAndInputsOnGraph() {
      this.$store.commit("switchyAxisAttribute", false);
    },
    downloadChart() {
      if (this.$refs.graph) {
        this.$refs.graph.download(this.chart.type);
      }
    },
    switchFormatter() {
      this.$store.commit("switchFormatter", false);
    },
    trans(key) {
      // Ensure window.trans exists and is an object
      if (!window.trans || typeof window.trans !== "object") {
        window.trans = {};
      }

      if (typeof key !== "string") {
        return "";
      }

      // Return the translation if available, otherwise return the key itself
      if (
        typeof window.trans[key] === "undefined" ||
        window.trans[key] === ""
      ) {
        return key;
      }
      return window.trans[key];
    },
  },
  provide() {
    return {
      productionUrl:
        import.meta.env.VITE_ENV_MODE === "production" ? "/metag" : "",
    };
  },
});

// Add global properties for Vue 3
app.config.globalProperties.productionUrl =
  import.meta.env.VITE_ENV_MODE === "production" ? "/metag" : "";
app.config.globalProperties.emitter = emitter;
app.config.globalProperties.trans = function (key) {
  // Ensure window.trans exists and is an object
  if (!window.trans || typeof window.trans !== "object") {
    window.trans = {};
  }

  if (typeof key !== "string") {
    return "";
  }

  // Return the translation if available, otherwise return the key itself
  if (typeof window.trans[key] === "undefined" || window.trans[key] === "") {
    return key;
  }
  return window.trans[key];
};

// Use the store
app.use(store);

// Register all components
Object.entries(components).forEach(([name, component]) => {
  app.component(name, component);
});

// Mount the app when the DOM is ready
window.addEventListener("DOMContentLoaded", () => {
  window.app = app.mount("#app");
});
