<template>
  <Modal
    v-if="visible"
    :title="'Enter the new email'"
    :visible="visible"
    @confirm="handleSubmit"
    @cancel="handleCancel"
    @update:visible="updateVisible"
  >
    <template #default>
      <div class="mb-4">
        <input
          v-model="email"
          type="email"
          class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-hidden focus:ring focus:border-blue-500"
          :class="{ 'border-red-500': !isValidEmail && email.length > 0 }"
          :aria-label="'New email address'"
          :placeholder="'Enter new email address'"
          @keydown.enter="handleSubmit"
          ref="emailInput"
        />
        <p v-if="!isValidEmail && email.length > 0" class="mt-1 text-sm text-red-600">
          Please enter a valid email address
        </p>
      </div>
      
      <!-- Success message -->
      <div v-if="successMessage" class="p-4 mb-4 bg-green-100 rounded-md">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">Email Changed</h3>
            <div class="mt-2 text-sm text-green-700">
              <p>{{ successMessage }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template #extra-buttons>
      <!-- Override default buttons since Modal component provides Confirm/Cancel -->
    </template>
  </Modal>
</template>

<script>
import Modal from './global/modal.vue';
import axios from 'axios';

export default {
  components: {
    Modal,
  },
  
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      email: '',
      successMessage: '',
      isSubmitting: false,
    };
  },

  computed: {
    isValidEmail() {
      const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(this.email).toLowerCase());
    },
  },

  emits: ['update:visible'],

  methods: {
    updateVisible(value) {
      this.$emit('update:visible', value);
    },

    handleCancel() {
      this.resetForm();
      this.$emit('update:visible', false);
    },

    handleSubmit() {
      if (!this.isValidEmail || this.isSubmitting) {
        return;
      }

      this.isSubmitting = true;
      
      axios.post('/changeemail', { email: this.email })
        .then((response) => {
          this.successMessage = response.data;
          // Keep modal open to show success message, but reset email field
          this.email = '';
          this.isSubmitting = false;
        })
        .catch((error) => {
          this.successMessage = error.response?.data?.message || 'An error occurred';
          this.isSubmitting = false;
        });
    },

    resetForm() {
      this.email = '';
      this.successMessage = '';
      this.isSubmitting = false;
    },
  },

  watch: {
    visible(newValue) {
      if (newValue) {
        this.resetForm();
        // Focus the email input when modal opens
        this.$nextTick(() => {
          if (this.$refs.emailInput) {
            this.$refs.emailInput.focus();
          }
        });
      }
    },
  },
};
</script>