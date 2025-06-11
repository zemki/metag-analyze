<template>
  <div class="flex flex-col h-full">
    <!-- Search and Filter Toolbar -->
    <div class="flex-shrink-0 p-4 bg-white border-b border-gray-200">
      <div class="flex flex-wrap gap-4 items-center justify-between">
        <!-- Search Input -->
        <div class="flex-1 min-w-64">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search cases by name or user email..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">Status:</label>
          <select
            v-model="statusFilter"
            @change="loadCases"
            class="block px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="backend">Backend</option>
          </select>
        </div>

        <!-- Sort Options -->
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">Sort:</label>
          <select
            v-model="sortBy"
            @change="loadCases"
            class="block px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="created_at">Created Date</option>
            <option value="name">Name</option>
            <option value="user_id">User</option>
          </select>
          <button
            @click="toggleSortOrder"
            class="p-2 border border-gray-300 bg-white rounded-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500"
            :title="sortOrder === 'desc' ? 'Sort Ascending' : 'Sort Descending'"
          >
            <svg class="h-4 w-4 text-gray-600" :class="{ 'rotate-180': sortOrder === 'desc' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
            </svg>
          </button>
        </div>

        <!-- Per Page Selection -->
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">Show:</label>
          <select
            v-model="perPage"
            @change="loadCases"
            class="block px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Pagination Info and Controls (Top) -->
    <div v-if="pagination.total > 0" class="flex-shrink-0 px-4 py-3 bg-gray-50 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} cases
        </div>
        <PaginationControls 
          :pagination="pagination" 
          @page-changed="changePage"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Loading cases...</p>
      </div>
    </div>

    <!-- Cases List -->
    <div v-else-if="cases.length > 0" class="flex-1 overflow-y-auto">
      <div class="divide-y divide-gray-200">
        <CaseCard
          v-for="caseItem in cases"
          :key="caseItem.id"
          :case="caseItem"
          :selected="selectedCase?.id === caseItem.id"
          :layout="layout"
          @select="$emit('case-selected', caseItem)"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v6a2 2 0 002 2h6a2 2 0 002-2v-7m0 0V9a2 2 0 00-2-2H9.5a2 2 0 00-2 2v4.5" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No cases found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ searchQuery || statusFilter ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new case.' }}
        </p>
        <div v-if="!searchQuery && !statusFilter" class="mt-6">
          <a
            :href="urlToCreateCase + '/cases/new'"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Case
          </a>
        </div>
      </div>
    </div>

    <!-- Bottom Pagination (for long lists) -->
    <div v-if="pagination.total > perPage" class="flex-shrink-0 px-4 py-3 bg-gray-50 border-t border-gray-200">
      <div class="flex justify-center">
        <PaginationControls 
          :pagination="pagination" 
          @page-changed="changePage"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { debounce } from 'lodash';
import CaseCard from './CaseCard.vue';
import PaginationControls from './PaginationControls.vue';

export default {
  name: 'CasesListWithPagination',
  components: {
    CaseCard,
    PaginationControls
  },
  props: {
    projectId: {
      type: [Number, String],
      required: true
    },
    urlToCreateCase: {
      type: String,
      required: true
    },
    selectedCase: {
      type: Object,
      default: null
    },
    layout: {
      type: String,
      default: 'default', // 'default' or 'sidebar'
      validator: value => ['default', 'sidebar'].includes(value)
    }
  },
  emits: ['case-selected'],
  data() {
    return {
      cases: [],
      loading: false,
      searchQuery: '',
      statusFilter: '',
      sortBy: 'created_at',
      sortOrder: 'desc',
      perPage: 50,
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      }
    };
  },
  mounted() {
    this.loadCases();
    this.debouncedSearch = debounce(this.loadCases, 300);
  },
  methods: {
    async loadCases() {
      this.loading = true;
      try {
        const params = new URLSearchParams({
          page: this.pagination.current_page,
          per_page: this.perPage,
          sort_by: this.sortBy,
          sort_order: this.sortOrder
        });

        if (this.searchQuery) {
          params.append('search', this.searchQuery);
        }
        if (this.statusFilter) {
          params.append('status', this.statusFilter);
        }

        const response = await fetch(`/projects/${this.projectId}/cases-ajax?${params}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        this.cases = data.data || [];
        this.pagination = {
          current_page: data.current_page,
          last_page: data.last_page,
          from: data.from,
          to: data.to,
          total: data.total
        };
      } catch (error) {
        console.error('Error loading cases:', error);
        this.cases = [];
        this.pagination = {
          current_page: 1,
          last_page: 1,
          from: 0,
          to: 0,
          total: 0
        };
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      this.pagination.current_page = page;
      this.loadCases();
    },
    toggleSortOrder() {
      this.sortOrder = this.sortOrder === 'desc' ? 'asc' : 'desc';
      this.loadCases();
    }
  }
};
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>