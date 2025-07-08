<template>
  <div
    @click="$emit('select')"
    :class="[
      'p-4 hover:bg-gray-50 cursor-pointer transition-colors duration-150',
      selected ? 'bg-blue-50 border-l-4 border-blue-500' : 'border-l-4 border-transparent'
    ]"
  >
    <div class="flex items-start justify-between">
      <!-- Case Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center space-x-2">
          <h3 class="text-lg font-semibold text-gray-900 truncate">
            {{ caseData.name }}
          </h3>
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
            ID: {{ caseData.id }}
          </span>
          <span
            v-if="caseData.backend"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
          >
            Backend
          </span>
          <span
            v-else-if="statusBadge"
            :class="statusBadge.class"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
          >
            {{ statusBadge.text }}
          </span>
        </div>

        <!-- User Info -->
        <div class="mt-1 flex items-center text-sm text-gray-500">
          <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          {{ userDisplayName || 'No user assigned' }}
        </div>

        <!-- Timeline Info -->
        <div class="mt-2 space-y-1">
          <div class="flex items-center text-sm text-gray-600">
            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ startInfo }}
          </div>
          <div v-if="!caseData.backend" class="flex items-center text-sm text-gray-600">
            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ endInfo }}
          </div>
        </div>

        <!-- Entries Count -->
        <div class="mt-3 flex items-center justify-between">
          <div class="flex items-center text-sm text-gray-500">
            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ entriesCount }} {{ entriesCount === 1 ? 'entry' : 'entries' }}
          </div>
          
          <!-- Consultable Status -->
          <div v-if="caseData.consultable" class="flex items-center">
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
              <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              Consultable
            </span>
          </div>
        </div>
      </div>

      <!-- Selection Indicator -->
      <div v-if="selected" class="flex-shrink-0 ml-4">
        <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CaseCard',
  props: {
    case: {
      type: Object,
      required: true
    },
    selected: {
      type: Boolean,
      default: false
    },
    layout: {
      type: String,
      default: 'default'
    }
  },
  emits: ['select'],
  computed: {
    caseData() {
      return this.case;
    },
    userDisplayName() {
      if (this.caseData.user) {
        if (this.caseData.user.profile?.name) {
          return this.caseData.user.profile.name;
        }
        return this.caseData.user.email;
      }
      return null;
    },
    startInfo() {
      // For MART projects, show simplified info
      if (this.caseData.project && this.caseData.project.is_mart_project) {
        return `Created on: ${this.formatDate(this.caseData.created_at)}`;
      }
      
      if (!this.caseData.start_day && !this.caseData.first_day) {
        const duration = this.getDuration(this.caseData.duration);
        return `Case starts when user logs in and lasts ${duration} days`;
      } else if (this.caseData.first_day) {
        return `Started on: ${this.caseData.first_day}`;
      } else if (this.caseData.start_day) {
        const isPast = this.isPast(this.caseData.start_day);
        return `${isPast ? 'Started on' : 'Will start on'}: ${this.formatDate(this.caseData.start_day)}`;
      } else {
        return `Created on: ${this.formatDate(this.caseData.created_at)}`;
      }
    },
    endInfo() {
      // For MART projects, duration is handled by the mobile app
      if (this.caseData.project && this.caseData.project.is_mart_project) {
        return 'Duration managed by mobile app';
      }
      
      if (this.caseData.backend) {
        return 'No end date';
      }
      return `Last day: ${this.caseData.last_day}`;
    },
    entriesCount() {
      return this.caseData.entries ? this.caseData.entries.length : 0;
    },
    statusBadge() {
      if (this.caseData.backend) {
        return null;
      }

      // For MART projects, show a simplified status
      if (this.caseData.project && this.caseData.project.is_mart_project) {
        return {
          text: 'Active',
          class: 'bg-green-100 text-green-800'
        };
      }

      const now = new Date();
      const lastDay = this.parseDate(this.caseData.last_day);

      if (!lastDay) {
        return {
          text: 'Pending',
          class: 'bg-yellow-100 text-yellow-800'
        };
      }

      if (lastDay < now) {
        return {
          text: 'Completed',
          class: 'bg-gray-100 text-gray-800'
        };
      } else {
        return {
          text: 'Active',
          class: 'bg-green-100 text-green-800'
        };
      }
    }
  },
  methods: {
    getDuration(durationString) {
      if (!durationString) return 'Unknown';
      const match = durationString.match(/duration:(\d+)/);
      return match ? match[1] : 'Unknown';
    },
    formatDate(dateString) {
      if (!dateString) return 'Unknown';
      try {
        return new Date(dateString).toLocaleDateString();
      } catch {
        return dateString;
      }
    },
    parseDate(dateString) {
      if (!dateString || dateString === 'Case not started by the user') return null;
      try {
        // Handle different date formats
        if (dateString.includes('.')) {
          // Format: dd.mm.yyyy
          const parts = dateString.split('.');
          if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]);
          }
        }
        return new Date(dateString);
      } catch {
        return null;
      }
    },
    isPast(dateString) {
      const date = this.parseDate(dateString);
      return date && date < new Date();
    }
  }
};
</script>