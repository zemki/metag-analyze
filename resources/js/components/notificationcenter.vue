<template>
  <div class="flex flex-col h-full bg-gray-50">
    <!-- Navigation Tabs -->
    <div class="bg-white shadow">
      <nav class="flex space-x-1 p-2" aria-label="Tabs">
        <button
            v-for="(tab, index) in [
            'Send Notification',
            'List of sent Notifications',
            'List of planned Notifications'
          ]"
            :key="index"
            @click="activeTab = index"
            :class="[
            'flex-1 px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-150',
            activeTab === index
              ? 'bg-blue-500 text-white shadow-md'
              : 'text-gray-600 hover:bg-blue-50'
          ]"
        >
          {{ trans(tab) }}
        </button>
      </nav>
    </div>

    <!-- Send Notification Tab -->
    <div v-if="activeTab == 0" class="p-6 space-y-6">
      <div
          v-for="(oneCase, index) in arrayOfCases"
          :key="index"
          class="bg-white rounded-lg shadow-sm p-6 space-y-4 border border-gray-200"
      >
        <!-- Case Header -->
        <div class="border-b border-gray-200 pb-4">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ oneCase.name }}
          </h2>
          <p class="text-sm text-gray-500 mt-1">
            {{ trans("Last day: ") }}
            <span class="font-medium">{{ oneCase.real_duration_readable }}</span>
          </p>
        </div>

        <!-- Admin Actions -->
        <div v-if="admin" class="flex justify-end">
          <button
              @click="cleanupNotification(oneCase)"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            {{ trans("Forget Notification") }}
          </button>
        </div>

        <!-- User Info -->
        <div class="bg-blue-50 rounded-md p-4">
          <p class="text-blue-700">
            {{ trans("Send notification to user: ") }}
            <span class="font-semibold">{{ oneCase.user.email }}</span>
          </p>
        </div>

        <!-- Warning Messages -->
        <div v-if="oneCase.expired" class="bg-red-50 text-red-700 p-4 rounded-md font-medium">
          {{ trans("This case is over, it ended " + oneCase.real_duration_readable) }}
        </div>

        <div
            v-if="oneCase.user.deviceID.length === 0"
            class="bg-red-50 text-red-700 p-4 rounded-md font-medium"
        >
          {{ trans("No device registered, you can't send notifications.") }}
        </div>

        <!-- Notification Form -->
        <div
            v-if="!oneCase.expired && oneCase.user.deviceID.length !== 0"
            class="space-y-4"
        >
          <!-- Title Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ trans("Notification Title") }} *
            </label>
            <input
                v-model="oneCase.title"
                :maxlength="inputLength.title"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <p class="mt-1 text-sm text-gray-500 text-right">
              {{ inputLength.title - (oneCase.title ? oneCase.title.length : 0) }}/{{ inputLength.title }}
            </p>
          </div>

          <!-- Message Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ trans("Notification Text") }} *
            </label>
            <textarea
                v-model="oneCase.message"
                :maxlength="inputLength.message"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            ></textarea>
            <p class="mt-1 text-sm text-gray-500 text-right">
              {{ inputLength.message - (oneCase.message ? oneCase.message.length : 0) }}/{{ inputLength.message }}
            </p>
          </div>

          <!-- Device Info -->
          <div v-if="oneCase.user.deviceID.length !== 0" class="text-sm text-gray-600">
            {{ oneCase.user.deviceID.length }} {{ trans(" registered device(s)") }}
          </div>

          <!-- Send/Plan Notification -->
          <div class="space-y-4">
            <div v-if="oneCase.user.profile && oneCase.user.profile.last_notification_at > yesterday"
                 class="bg-yellow-50 text-yellow-700 p-4 rounded-md">
              {{
                trans("Notification already sent in the last 24h - " + oneCase.user.profile.last_notification_at_readable)
              }}
            </div>

            <button
                v-if="oneCase.user.profile && oneCase.user.profile.last_notification_at < yesterday"
                @click="sendNotification(oneCase)"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              {{ trans("Send Notification") }}
            </button>

            <!-- Planning Section -->
            <div v-if="oneCase.planned_notifications.length === 0" class="space-y-4">
              <label class="block text-sm font-medium text-gray-700">
                {{ trans("Select Frequency and time") }} *
              </label>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select
                    v-model="oneCase.selectedFrequency"
                    class="col-span-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option v-for="f in frequency" :value="f">{{ f }}</option>
                </select>
                <div class="flex space-x-2 col-span-2">
                  <select
                      v-model="oneCase.selectedHour"
                      class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option v-for="hour in hours" :value="hour">{{ hour }}</option>
                  </select>
                  <span class="text-xl self-center">:</span>
                  <select
                      v-model="oneCase.selectedMinutes"
                      class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option v-for="minute in minutes" :value="minute">{{ minute }}</option>
                  </select>
                </div>
              </div>
              <button
                  @click="planNotification(oneCase)"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ trans("Plan Notification") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications List Tabs -->
    <div v-if="activeTab == 1 || activeTab == 2" class="p-6">
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-500">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                {{ trans(activeTab == 1 ? "Id" : "Notification Id") }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                {{ trans("Case") }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                {{ trans("Title") }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                {{ trans("Message") }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                {{ trans(activeTab == 1 ? "Sent At" : "When to send") }}
              </th>
              <th v-if="activeTab == 2" scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                {{ trans("Actions") }}
              </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="notification in activeTab == 1 ? sortedNotifications : plannednotifications"
                class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notification["id"] }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ notification["case"]["name"] }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ activeTab == 1 ? notification["data"]["title"] : notification.data.title }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ activeTab == 1 ? notification["data"]["message"] : notification.data.message }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ activeTab == 1 ? notification["created_at_readable"] : notification.data.planning }}
              </td>
              <td v-if="activeTab == 2" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                    @click="deletePlanned(notification)"
                    class="text-red-600 hover:text-red-800 inline-flex items-center"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  {{ trans("Delete") }}
                </button>
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
  props: {
    cases: {
      type: Array,
      default: () => []
    },
    notifications: {
      type: Array,
      default: () => []
    },
    plannednotifications: {
      type: Array,
      default: () => []
    },
    admin: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      inputLength: { title: 100, message: 900 },
      currentSort: "name",
      currentSortDir: "asc",
      activeTab: 0,
      arrayOfCases: {},
      notification: "",
      yesterday: moment().subtract(1, "days"),
      now: moment(),
      hours: [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
        21, 22, 23, 24,
      ],
      minutes: ["00", 15, 30, 45],
      frequency: ["Every day", "Every two days", "Every three days"],
    };
  },
  mounted() {
    this.arrayOfCases = this.cases;

    this.arrayOfCases.forEach((cases) => {
      cases.selectedHour = 1;
      cases.selectedMinutes = "00";
      cases.selectedFrequency = "Every day";
      let duration = cases.duration;

      cases.real_duration = duration.split("|").pop();
      if (!cases.real_duration.includes("days:")) {
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

        if (cases.real_duration_readable === "Invalid date")
          cases.real_duration_readable = "User didn't login yet.";

        cases.expired = cases.real_duration.isBefore(moment());
      } else {
        cases.expired = false;
        cases.real_duration_readable = "User didn't login yet.";
      }

      if (cases.user.profile != null) {
        if (cases.user.profile.last_notification_at !== null) {
          let d = moment(cases.user.profile.last_notification_at);
          cases.user.profile.last_notification_at = d;
          cases.user.profile.last_notification_at_readable =
            moment(d).format("DD.MM.YYYY H:m:s");
        }
      }
    });
    
    if (this.notifications != null) {
      this.notifications.forEach((notification) => {
        let d = moment(notification.created_at);
        notification.created_at_readable = moment(d).format("DD.MM.YYYY H:m:s");
        notification.case = this.arrayOfCases.find((cases) => {
          return notification.data.case === cases.id;
        });
      });
    }
    
    if (this.plannednotifications != null) {
      this.plannednotifications.forEach((notification) => {
        notification.data = JSON.parse(notification.data);
        notification.case = this.arrayOfCases.find((cases) => {
          return notification.data.case === cases.id;
        });
      });
    }
  },
  computed: {
    sortedNotifications() {
      return this.notifications.sort((a, b) => {
        let modifier = 1;
        if (this.currentSortDir === "desc") modifier = -1;
        if (this.currentSort.includes(".")) {
          let multipleKeys = this.currentSort.split(".");
          if (
            a[multipleKeys[0]][multipleKeys[1]] <
            b[multipleKeys[0]][multipleKeys[1]]
          )
            return -1 * modifier;
          if (
            a[multipleKeys[0]][multipleKeys[1]] >
            b[multipleKeys[0]][multipleKeys[1]]
          )
            return 1 * modifier;
        } else {
          if (a[this.currentSort] < b[this.currentSort]) return -1 * modifier;
          if (a[this.currentSort] > b[this.currentSort]) return 1 * modifier;
        }

        return 0;
      });
    },
  },
  methods: {
    trans(key) {
      // Translation helper
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      } else {
        if (window.trans[key] === "") return key;
        return window.trans[key];
      }
    },
    showSnackbar(message) {
      this.$root.showSnackbarMessage(message.message || message);
    },
    sort(s) {
      if (s === this.currentSort) {
        this.currentSortDir = this.currentSortDir === "asc" ? "desc" : "asc";
      }
      this.currentSort = s;
    },
    deletePlanned(notification) {
      let data = {
        notification: notification,
      };
      window.axios
        .post(this.productionUrl + "/users/deletenotification", data)
        .then((response) => {
          if (response.message) this.response = response.message;
          else {
            this.showSnackbar(response.data);
          }

          if (response.status === 200) {
            this.plannednotifications = this.plannednotifications.filter(
              function (o) {
                return o.id !== notification.id;
              }
            );
          }
        })
        .catch((error) => {
          if (error.message) this.showSnackbar(error.message);
          else {
            this.showSnackbar(error.response.data);
          }
        });
    },
    setAsNotificationSent(oneCase, response) {
      this.arrayOfCases.forEach(function (el) {
        if (el.id === oneCase.id) {
          let d = moment(response.data.notified);
          oneCase.user.profile.last_notification_at = d;
          oneCase.user.profile.last_notification_at_readable =
            moment(d).format("DD.MM.YYYY H:m:s");
        }
      });
    },
    sendNotification(oneCase) {
      if (!this.validData(oneCase)) {
        this.showSnackbar(this.trans("Your data are not valid."));
      } else {
        let data = {
          user: oneCase.user,
          title: oneCase.title,
          message: oneCase.message,
          cases: oneCase,
        };
        window.axios
          .post(this.productionUrl + "/users/notify", data)
          .then((response) => {
            if (response.message) this.response = response.message;
            else {
              this.showSnackbar(response.data);
            }
            this.setAsNotificationSent(oneCase, response);
          })
          .catch((error) => {
            if (error.message) this.showSnackbar(error.message);
            else {
              this.showSnackbar(error.response.data);
            }
          });
      }
    },
    planNotification(oneCase) {
      if (!this.validPlanning(oneCase)) {
        this.showSnackbar(this.trans("Your data are not valid."));
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
          .post(this.productionUrl + "/users/plannotification", data)
          .then((response) => {
            if (response.message) this.response = response.message;
            else {
              this.showSnackbar(response.data);
            }
            if (response.status === 200) {
              let newNotification = response.data.notification;
              newNotification.data = JSON.parse(newNotification.data);
              newNotification.case = this.arrayOfCases.find((cases) => {
                return newNotification.data.case === cases.id;
              });
              this.plannednotifications.push(newNotification);
            }
          })
          .catch((error) => {
            if (error.message) this.showSnackbar(error.message);
            else {
              this.showSnackbar(error.response.data);
            }
          });
      }
    },
    validData(oneCase) {
      if (!oneCase.title || oneCase.title.trim() === '' || !oneCase.message || oneCase.message.trim() === '') 
        return false;
      return true;
    },
    validPlanning(oneCase) {
      if (!oneCase.title || oneCase.title.trim() === '' || !oneCase.message || oneCase.message.trim() === '') 
        return false;
      if (
        !oneCase.selectedFrequency ||
        oneCase.selectedHour === undefined ||
        oneCase.selectedMinutes === undefined
      )
        return false;
      return true;
    },
    cleanupNotification(cases) {
      let data = {
        user: cases.user,
        cases: cases,
      };
      window.axios
        .post(this.productionUrl + "/users/cleanuplastnotification", data)
        .then((response) => {
          if (response.message) this.response = response.message;
          else {
            this.showSnackbar(response.data);
          }
        })
        .catch((error) => {
          if (error.message) this.showSnackbar(error.message);
          else {
            this.showSnackbar(error.response.data);
          }
        });
    },
  },
};
</script>
