<template>
  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
    <!-- Previous Page -->
    <button
      @click="changePage(pagination.current_page - 1)"
      :disabled="pagination.current_page <= 1"
      :class="[
        'relative inline-flex items-center px-2 py-2 rounded-l-md border text-sm font-medium',
        pagination.current_page <= 1
          ? 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed'
          : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500'
      ]"
    >
      <span class="sr-only">Previous</span>
      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Page Numbers -->
    <template v-for="page in visiblePages" :key="page">
      <button
        v-if="page !== '...'"
        @click="changePage(page)"
        :class="[
          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
          page === pagination.current_page
            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500'
        ]"
      >
        {{ page }}
      </button>
      <span
        v-else
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
      >
        ...
      </span>
    </template>

    <!-- Next Page -->
    <button
      @click="changePage(pagination.current_page + 1)"
      :disabled="pagination.current_page >= pagination.last_page"
      :class="[
        'relative inline-flex items-center px-2 py-2 rounded-r-md border text-sm font-medium',
        pagination.current_page >= pagination.last_page
          ? 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed'
          : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500'
      ]"
    >
      <span class="sr-only">Next</span>
      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
      </svg>
    </button>
  </nav>
</template>

<script>
export default {
  name: 'PaginationControls',
  props: {
    pagination: {
      type: Object,
      required: true
    }
  },
  emits: ['page-changed'],
  computed: {
    visiblePages() {
      const current = this.pagination.current_page;
      const last = this.pagination.last_page;
      const delta = 2; // Number of pages to show on each side of current page

      if (last <= 7) {
        // If total pages is 7 or less, show all pages
        return Array.from({ length: last }, (_, i) => i + 1);
      }

      const pages = [];
      
      // Always show first page
      pages.push(1);

      // Calculate start and end of the middle section
      let start = Math.max(2, current - delta);
      let end = Math.min(last - 1, current + delta);

      // Add ellipsis after first page if needed
      if (start > 2) {
        pages.push('...');
      }

      // Add middle pages
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      // Add ellipsis before last page if needed
      if (end < last - 1) {
        pages.push('...');
      }

      // Always show last page (if it's not already included)
      if (last > 1) {
        pages.push(last);
      }

      return pages;
    }
  },
  methods: {
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page && page !== this.pagination.current_page) {
        this.$emit('page-changed', page);
      }
    }
  }
};
</script>