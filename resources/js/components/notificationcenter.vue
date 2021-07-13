<template>
  <div>
    <div class="w-full mx-auto mt-4">
      <!-- Tabs -->
      <ul id="tabs" class="inline-flex w-full px-1 pt-2">
        <li
          :class="
            activeTab === 1
              ? 'border-b-4 border-solid cursor-pointer px-4 py-2 -mb-px font-semibold text-blue-800 border-b-2 border-blue-400 -t opacity-100'
              : 'cursor-pointer px-4 py-2 font-semibold text-gray-800 -t opacity-50'
          "
          @click="activeTab = 1"
        >
          {{ trans("Send Notification") }}
        </li>
        <li
          :class="
            activeTab === 2
              ? 'border-b-4 border-solid cursor-pointer px-4 py-2 -mb-px font-semibold text-blue-800 border-b-2 border-blue-400 -t opacity-100'
              : 'cursor-pointer px-4 py-2 font-semibold text-gray-800 -t opacity-50'
          "
          @click="activeTab = 2"
        >
          {{ trans("List of sent Notifications") }}
        </li>
        <li
          :class="
            activeTab === 3
              ? 'border-b-4 border-solid cursor-pointer px-4 py-2 -mb-px font-semibold text-blue-800 border-b-2 border-blue-400 -t opacity-100'
              : 'cursor-pointer px-4 py-2 font-semibold text-gray-800 -t opacity-50'
          "
          @click="activeTab = 3"
        >
            {{ trans('List of planned Notifications') }}
        </li>
      </ul>

      <!-- Tab Contents -->
      <div id="tab-contents w-full">
        <div
          id="first"
          :class="activeTab === 1 ? 'p-4' : 'p-4'"
          v-show="activeTab === 1"
        >
          <div class="my-4" v-for="oneCase in arrayOfCases">
            <h2 class="break-words text-2xl font-serif">
              {{ trans("Case Name: ")
              }}<span class="font-bold">{{ oneCase.name }}</span>
            </h2>
            <h2 class="break-words my-2 text-2xl font-serif">
              {{ trans("Last day: ")
              }}<span class="font-bold">{{
                oneCase.real_duration_readable
              }}</span>
            </h2>
            <p class="break-words font-serif">
              {{ trans("Send notification to user: ") }}
              <span class="font-bold">{{ oneCase.user.email }}</span>
            </p>
            <div class="block">
              <label
                for="name"
                class="uppercase tracking-wide text-gray-700 text-base font-bold"
              >
                {{ trans("Notification Title") }} *
              </label>
              <input
                v-model="oneCase.title"
                :maxlength="inputLength.title"
                type="text"
                class="mb-0 bg-white border border-gray-300 py-2 px-4 block w-full appearance-none leading-normal w-full mb-4"
                name="name"
              />
              <span
                style="margin-top: -20px"
                :class="
                  oneCase.title && inputLength.title <= oneCase.title.length
                    ? 'text-red-600 text-xs w-auto inline-flex float-right'
                    : 'text-xs text-gray-500 w-auto inline-flex float-right'
                "
                >{{
                  inputLength.title -
                  (oneCase.title ? oneCase.title.length : 0)
                }}/{{ inputLength.title }}
              </span>
              <label
                for="name"
                class="uppercase tracking-wide text-gray-700 text-base font-bold"
              >
                {{ trans("Notification Text") }} *
              </label>
              <textarea
                :maxlength="inputLength.message"
                v-model="oneCase.message"
                class="block resize border w-full py-2 px-4 focus:border-blue-700"
              ></textarea>
              <span
                style="margin-top: -20px"
                :class="
                  oneCase.message &&
                  inputLength.message <= oneCase.message.length
                    ? 'text-red-600 text-xs w-auto inline-flex float-right'
                    : 'text-xs text-gray-500 w-auto inline-flex float-right'
                "
                >{{
                  inputLength.message -
                  (oneCase.message ? oneCase.message.length : 0)
                }}/{{ inputLength.message }}
              </span>
            </div>

            <p
              class="break-words font-bold"
              v-if="oneCase.user.deviceID.length !== 0"
            >
              {{ trans("Send notification to") }}
              {{ oneCase.user.deviceID.length }} {{ trans("device(s)") }}.
            </p>

            <p
              class="break-words font-bold bg-red-500 p-1 text-white"
              v-if="oneCase.user.deviceID.length === 0"
            >
              {{ trans("No device registered") }}
            </p>
            <p
              class="font-bold bg-red-500 p-1 text-white"
              v-if="
                oneCase.user.profile &&
                oneCase.user.profile.last_notification_at > yesterday
              "
            >
              {{
                trans(
                  "Notification already sent in the last 24h - " +
                    oneCase.user.profile.last_notification_at_readable
                )
              }}
            </p>
            <button
              @click="sendNotification(oneCase)"
              class="mt-2 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black py-2 px-4 border border-blue-500 hover:border-transparent"
            >
              {{ trans("Send Notification") }}
            </button>

            <label
              for="notification frequency"
              class="block mt-6 tracking-wide text-gray-700 text-base font-bold"
            >
              {{ trans("Select Frequency and time") }} *
            </label>
            <div class="w-full flex">
              <select
                v-model="oneCase.selectedFrequency"
                class="w-1/3 flex-none bg-white border border-gray-300 py-1 px-2 pr-4 leading-tight focus:bg-white focus:border-gray-500"
                id="grid-state"
                name="notification frequency"
              >
                <option v-for="f in frequency" :value="f">{{ f }}</option>
              </select>
              <div
                class="py-2 px-4 mx-2 w-40 bg-white border border-solid border-gray-300"
              >
                <div class="flex-auto text-center">
                  <select
                    v-model="oneCase.selectedHour"
                    name="hours"
                    class="bg-white text-xl appearance-none outline-none py-1 px-2 pr-4"
                  >
                    <option v-for="hour in hours" :value="hour">
                      {{ hour }}
                    </option>
                  </select>
                  <span class="text-xl mr-3">:</span>
                  <select
                    v-model="oneCase.selectedMinutes"
                    name="minutes"
                    class="bg-white text-xl appearance-none outline-none py-1 px-2 pr-4"
                  >
                    <option v-for="minute in minutes" :value="minute">
                      {{ minute }}
                    </option>
                  </select>
                </div>
              </div>
              <button
                class="mt-2 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black py-2 px-4 border border-blue-500 hover:border-transparent"
                @click="planNotification(oneCase)"
              >
                {{ trans("Plan Notification") }}
              </button>
            </div>
            <div
              class="border-b-2 mt-4 border-blue-800 border-solid w-full"
            ></div>
          </div>
        </div>
        <div id="second" class="p-4" v-show="activeTab === 2">
          <table class="table-auto w-full border-solid bg-blue-100 border-2">
            <thead>
              <tr class="bg-blue-500 uppercase">
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle w-64"
                >
                  {{ trans("Notification Id") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Case") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Title") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Message") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Sent At") }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="notification in notifications">
                <td class="border pl-2 py-2 text-sm">
                  {{ notification["id"] }}
                </td>
                <td class="border px-4 py-2">
                  {{ notification["case"]["name"] }}
                </td>
                <td class="border px-4 py-2 w-64">
                  {{ notification["data"]["title"] }}
                </td>
                <td class="border px-4 py-2">
                  {{ notification["data"]["message"] }}
                </td>
                <td class="border pr-2 py-2 w-64">
                  {{ notification["created_at_readable"] }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
          <div id="third" class="p-4" v-show="activeTab === 3">
          <table class="table-auto w-full border-solid bg-blue-100 border-2">
            <thead>
              <tr class="bg-blue-500 uppercase">
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle w-64"
                >
                  {{ trans("Notification Id") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Case") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Title") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Message") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("When to send") }}
                </th>
                <th
                  class="border-b-2 border-black border-solid px-4 py-2 font-bold text-white align-middle"
                >
                  {{ trans("Actions") }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="notification in plannednotifications">
                <td class="border pl-2 py-2 text-sm">
                  {{ notification["id"] }}
                </td>
                <td class="border px-4 py-2">
                  {{ notification["case"]["name"] }}
                </td>
                <td class="border px-4 py-2 w-64">
                  {{ notification["data"]["title"] }}
                </td>
                <td class="border px-4 py-2">
                  {{ notification["data"]["message"] }}
                </td>
                <td class="border pr-2 py-2 w-64">
                  {{ notification["data"]["planning"] }}
                </td>
                <td class="border pr-2 py-2 w-64">
do something here
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  props: ["cases", "notifications","plannednotifications"],
  data() {
    return {
      inputLength: { title: 100, message: 900 },
      activeTab: 1,
      arrayOfCases: {},
      notification: "",
      yesterday: moment().subtract(1, "days"),
      now: moment(),
      hours: [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        19,
        20,
        21,
        22,
        23,
        24,
      ],
      minutes: [0, 30, 45],
      frequency: ["Every day", "Every two days", "Every three days"],
    };
  },
  mounted() {
    this.arrayOfCases = this.cases;

    this.arrayOfCases.forEach((cases) => {
      let duration = cases.duration;
      cases.real_duration = duration.split("|").pop();
      cases.real_duration = cases.real_duration
        .substring(
          cases.real_duration.indexOf(":") + 1,
          cases.real_duration.length
        )
        .split(".");
      cases.real_duration = moment(
        cases.real_duration[2] +
          "-" +
          cases.real_duration[1] +
          "-" +
          cases.real_duration[0]
      );
      cases.real_duration_readable = moment(cases.real_duration).format(
        "DD.MM.YYYY"
      );

      if (cases.user.profile != null) {
        if (cases.user.profile.last_notification_at !== null) {
          let d = moment(cases.user.profile.last_notification_at);
          cases.user.profile.last_notification_at = d;
          cases.user.profile.last_notification_at_readable = moment(d).format(
            "DD.MM.YYYY H:m:s"
          );
        }
      }
    });
    if (this.notifications != null) {
      this.notifications.forEach((notification) => {
        let d = moment(notification.created_at);
        notification.created_at_readable = moment(d).format("DD.MM.YYYY H:m:s");
        notification.case = _.find(this.arrayOfCases, (cases) => {
          return notification.data.case === cases.id;
        });
      });
    }
    if (this.plannednotifications != null) {
      this.plannednotifications.forEach((notification) => {
        notification.data = JSON.parse(notification.data);
        notification.case = _.find(this.arrayOfCases, (cases) => {
          return notification.data.case === cases.id;
        });
      });
    }
  },
  methods: {
    setAsNotificationSent: function (oneCase, response) {
      this.arrayOfCases.forEach(function (el) {
        if (el.id === oneCase.id) {
          let d = moment(response.data.notified);
          oneCase.user.profile.last_notification_at = d;
          oneCase.user.profile.last_notification_at_readable = moment(d).format(
            "DD.MM.YYYY H:m:s"
          );
        }
      });
    },
    sendNotification(oneCase) {
      if (!this.validData(oneCase)) {
        this.$buefy.snackbar.open(this.trans("Your data are not valid."));
      } else {
        let data = {
          user: oneCase.user,
          title: oneCase.title,
          message: oneCase.message,
          cases: oneCase,
        };
        window.axios
          .post(this.productionUrl + "/users/notify/", data)
          .then((response) => {
            if (response.message) this.response = response.message;
            else {
              this.$buefy.snackbar.open(response.data);
            }
            this.setAsNotificationSent(oneCase, response);
          })
          .catch((error) => {
            if (error.message) this.$buefy.snackbar.open(error.message);
            else {
              this.$buefy.snackbar.open(error.response.data);
            }
          });
      }
    },
    planNotification(oneCase) {
      if (!this.validPlanning(oneCase)) {
        this.$buefy.snackbar.open(this.trans("Your data are not valid."));
      } else {
        let data = {
          user: oneCase.user,
          title: oneCase.title,
          message: oneCase.message,
          cases: oneCase,
          planning:
            oneCase.selectedFrequency +
            " at " +
            oneCase.selectedHour +
            ":" +
            oneCase.selectedMinutes,
        };

        window.axios
          .post(this.productionUrl + "/users/plannotification/", data)
          .then((response) => {
            if (response.message) this.response = response.message;
            else {
              this.$buefy.snackbar.open(response.data);
            }
          })
          .catch((error) => {
            if (error.message) this.$buefy.snackbar.open(error.message);
            else {
              this.$buefy.snackbar.open(error.response.data);
            }
          });
      }
    },
    validData(oneCase) {
      if (_.isEmpty(oneCase.title) || _.isEmpty(oneCase.message)) return false;
      else return true;
    },
    validPlanning(oneCase) {
      if (
        _.isEmpty(oneCase.selectedFrequency) ||
        _.isNil(oneCase.selectedHour) ||
        _.isNil(oneCase.selectedMinutes)
      )
        return false;
      else return true;
    },
  },
};
</script>
