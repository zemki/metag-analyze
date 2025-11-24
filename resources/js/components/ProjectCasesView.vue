<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    <!-- Top Header Bar -->
    <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Project Info -->
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ localProject.name }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ localProject.description }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-3">
          <a :href="urlToCreateCase + '/cases/new'"
             class="inline-flex items-center px-2 py-1.5 text-xs font-medium text-white bg-blue-600 border border-transparent rounded hover:bg-blue-700 focus:outline-hidden focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 whitespace-nowrap">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ trans('Create Case') }}
          </a>

          <!-- Project Settings Button -->
          <button @click="openProjectSettings"
                  class="inline-flex items-center px-2 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:outline-hidden focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 whitespace-nowrap">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ trans('Project Settings') }}
          </button>

          <!-- More Actions Dropdown -->
          <div class="relative">
            <button @click="actionsDropdownOpen = !actionsDropdownOpen" type="button"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
              </svg>
              Actions
            </button>

            <!-- Dropdown Menu -->
            <div v-if="actionsDropdownOpen"
                 @click.stop
                 class="absolute right-0 z-10 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
              <div class="py-1">
                <a :href="urlToCreateCase + '/notifications'"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 32 32">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16,2h0A10,10,0,0,1,26,12v8a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2V12A10,10,0,0,1,16,2Z" />
                    <rect width="32" height="4" rx="2" y="20" fill="currentColor" stroke="none"/>
                    <path d="M16,32h0a4,4,0,0,1-4-4V26h8v2A4,4,0,0,1,16,32Z" fill="currentColor" stroke="none"/>
                  </svg>
                  {{ trans('Notification Center') }}
                </a>
                <a :href="urlToCreateCase + '/export'"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  {{ trans('Download all data') }}
                </a>
                <a :href="urlToCreateCase + '/treemap'"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  {{ trans('Treemap View') }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content: Email Client Layout -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Cases List Panel (Top) -->
      <div class="bg-white border-b border-gray-200" :style="{ height: casesListHeight + 'px' }">
        <!-- Cases Header -->
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">Cases</h2>
          <span class="text-sm text-gray-500">{{ totalCasesCount }} total</span>
        </div>

        <!-- Status Legend -->
        <div class="px-6 py-2 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center gap-1 text-xs">
            <span class="text-gray-600 font-medium mr-2">Status Guide:</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-yellow-100 text-yellow-800">
              Pending
            </span>
            <span class="text-gray-400 mx-0.5">-</span>
            <span class="text-gray-500 text-xs">Case not yet started by user</span>
            <span class="text-gray-300 mx-2">|</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800">
              Active
            </span>
            <span class="text-gray-400 mx-0.5">-</span>
            <span class="text-gray-500 text-xs">Case currently in progress</span>
            <span class="text-gray-300 mx-2">|</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800">
              Completed
            </span>
            <span class="text-gray-400 mx-0.5">-</span>
            <span class="text-gray-500 text-xs">Case has ended</span>
            <span class="text-gray-300 mx-2">|</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-purple-100 text-purple-800">
              Backend
            </span>
            <span class="text-gray-400 mx-0.5">-</span>
            <span class="text-gray-500 text-xs">Backend-only case</span>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="px-6 py-3 border-b border-gray-200 bg-white">
          <div class="flex flex-wrap gap-4 items-center">
            <!-- Search Input -->
            <div class="flex-1 min-w-64">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input
                  v-model="searchQuery"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Search cases..."
                  class="block w-full pl-10 pr-3 py-1.5 text-sm border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-hidden focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <!-- Status Filter -->
            <select v-model="statusFilter" @change="loadCases"
                    class="text-sm border border-gray-300 rounded-md px-3 py-1.5 bg-white focus:outline-hidden focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="backend">Backend</option>
            </select>

            <!-- Sort Options -->
            <select v-model="sortBy" @change="loadCases"
                    class="text-sm border border-gray-300 rounded-md px-3 py-1.5 bg-white focus:outline-hidden focus:ring-blue-500 focus:border-blue-500">
              <option value="created_at">Created Date</option>
              <option value="name">Name</option>
              <option value="user_id">User</option>
              <option value="entries_count">Entries Count</option>
              <option value="status">Status</option>
            </select>

            <button @click="toggleSortOrder"
                    class="p-1.5 border border-gray-300 bg-white rounded-md hover:bg-gray-50 focus:outline-hidden focus:ring-1 focus:ring-blue-500"
                    :title="sortOrder === 'desc' ? 'Sort Ascending' : 'Sort Descending'">
              <svg class="h-4 w-4 text-gray-600" :class="{ 'rotate-180': sortOrder === 'desc' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
              </svg>
            </button>

            <!-- Per Page -->
            <select v-model="perPage" @change="loadCases"
                    class="text-sm border border-gray-300 rounded-md px-3 py-1.5 bg-white focus:outline-hidden focus:ring-blue-500 focus:border-blue-500">
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>

        <!-- Cases List - Compact Email Style -->
        <div class="overflow-y-auto" :style="{ height: (casesListHeight - 200) + 'px' }">
          <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <div v-else-if="cases.length > 0" class="divide-y divide-gray-100">
            <div v-for="caseItem in cases" :key="caseItem.id"
                 @click="handleSelectedCase(caseItem)"
                 :class="[
                   'px-6 py-3 hover:bg-gray-50 cursor-pointer transition-colors duration-150 flex items-center justify-between',
                   selectedCase?.id === caseItem.id ? 'bg-blue-50 border-l-4 border-blue-500' : 'border-l-4 border-transparent'
                 ]">
              <!-- Case Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3">
                  <h3 class="text-sm font-medium text-gray-900 truncate">{{ caseItem.name }}</h3>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                    ID: {{ caseItem.id }}
                  </span>
                  <span v-if="caseItem.backend"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                    Backend
                  </span>
                  <span v-else-if="getStatusBadge(caseItem)"
                        :class="getStatusBadge(caseItem).class"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                    {{ getStatusBadge(caseItem).text }}
                  </span>
                </div>
                <div class="mt-1 flex items-center text-xs text-gray-500 space-x-4">
                  <span class="flex items-center">
                    <svg class="flex-shrink-0 mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ getUserDisplayName(caseItem) }}
                  </span>
                  <span class="flex items-center">
                    <svg class="flex-shrink-0 mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ caseItem.entries ? caseItem.entries.length : 0 }} entries
                  </span>
                  <span class="text-gray-400">{{ formatDate(caseItem.created_at) }}</span>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="flex items-center space-x-2 ml-4 relative z-10">
                <button v-if="caseItem.consultable && caseItem.entries?.length > 0"
                        @click.stop="exportCase(caseItem)"
                        class="p-1 text-gray-400 hover:text-blue-600"
                        title="Export Case">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </button>
                <button v-if="canShowQRCode(caseItem)"
                        @click.stop="showQRCodeModal(caseItem)"
                        :class="caseItem.qr_token_revoked_at ? 'p-1 text-red-500 hover:text-red-700' : 'p-1 text-gray-400 hover:text-green-600'"
                        :title="caseItem.qr_token_revoked_at ? 'QR Code Revoked' : 'QR Code Login'">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" stroke-width="2"/>
                    <rect x="14" y="14" width="7" height="7" stroke-width="2"/>
                    <line v-if="caseItem.qr_token_revoked_at" x1="3" y1="3" x2="21" y2="21" stroke-width="2"/>
                  </svg>
                </button>
                <button v-if="isCreator && !localProject.is_mart_project"
                        @click.stop="showCloseCaseModal(caseItem)"
                        class="p-1 text-gray-400 hover:text-orange-600"
                        title="Close Case Early">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                <button @click.stop="confirmDeleteCase(caseItem)"
                        class="p-1 text-gray-400 hover:text-red-600"
                        title="Delete Case">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-else class="flex items-center justify-center py-8">
            <div class="text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No cases found</h3>
              <p class="mt-1 text-sm text-gray-500 mb-4">
                {{ searchQuery || statusFilter ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new case.' }}
              </p>
              <a v-if="!searchQuery && !statusFilter"
                 :href="urlToCreateCase + '/cases/new'"
                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-xs text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create your first case
              </a>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="px-6 py-3 bg-gray-50 border-t border-gray-200 relative z-10">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} cases
            </div>
            <PaginationControls
              :pagination="pagination"
              @page-changed="changePage"
              size="small"
            />
          </div>
        </div>
      </div>

      <!-- Resize Handle -->
      <div @mousedown="startResize"
           class="h-1 bg-gray-200 hover:bg-blue-300 cursor-row-resize flex-shrink-0 relative group">
        <div class="absolute inset-x-0 -top-1 -bottom-1 group-hover:bg-blue-300 opacity-0 group-hover:opacity-100 transition-opacity"></div>
      </div>

      <!-- Case Details Panel (Bottom) -->
      <div class="flex-1 bg-white overflow-hidden">
        <div v-if="selectedCase" class="h-full flex flex-col">
          <!-- Case Details Header -->
          <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200">
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3">
                  <h1 class="text-xl font-semibold text-gray-900">{{ selectedCase.name }}</h1>
                  <span v-if="selectedCase.backend"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    Backend
                  </span>
                  <span v-else-if="getStatusBadge(selectedCase)"
                        :class="getStatusBadge(selectedCase).class"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getStatusBadge(selectedCase).text }}
                  </span>
                </div>
                <div class="mt-1 flex items-center text-sm text-gray-500">
                  <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ getUserDisplayName(selectedCase) }}
                  <span class="mx-2">•</span>
                  {{ selectedCase.entries ? selectedCase.entries.length : 0 }} entries
                  <span class="mx-2">•</span>
                  Created {{ formatDate(selectedCase.created_at) }}
                </div>
              </div>

              <!-- Case Actions -->
              <div class="flex items-center space-x-2">
                <button v-if="selectedCase.consultable && selectedCase.entries?.length > 0"
                        @click="exportCase(selectedCase)"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Export
                </button>
                <button v-if="canShowQRCode(selectedCase)"
                        @click="showQRCodeModal(selectedCase)"
                        :class="selectedCase.qr_token_revoked_at ? 'inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded hover:bg-red-100' : 'inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded hover:bg-green-100'">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" stroke-width="2"/>
                    <rect x="14" y="14" width="7" height="7" stroke-width="2"/>
                    <line v-if="selectedCase.qr_token_revoked_at" x1="3" y1="3" x2="21" y2="21" stroke-width="2"/>
                  </svg>
                  {{ selectedCase.qr_token_revoked_at ? 'QR Revoked' : 'QR Code' }}
                </button>
                <button v-if="isCreator && !localProject.is_mart_project"
                        @click="showCloseCaseModal(selectedCase)"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-orange-600 bg-orange-50 border border-orange-200 rounded hover:bg-orange-100">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Close Early
                </button>
                <button @click="confirmDeleteCase(selectedCase)"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded hover:bg-red-100">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete
                </button>
              </div>
            </div>
          </div>

          <!-- Case Content -->
          <div class="flex-1 overflow-hidden">
            <SelectedCase
              :project-inputs="parsedProjectInputs"
              :cases="selectedCase"
            />
            <!-- DEBUG: Let's see what projectInputs contains -->
            <div v-if="false" class="debug p-4 bg-red-100 text-xs">
              <strong>ProjectInputs Debug:</strong><br>
              Type: {{ typeof projectInputs }}<br>
              IsArray: {{ Array.isArray(projectInputs) }}<br>
              Length: {{ projectInputs?.length }}<br>
              Content: {{ JSON.stringify(projectInputs) }}
            </div>
          </div>
        </div>

        <!-- Empty State - Only show if there are cases but none selected -->
        <div v-else-if="totalCasesCount > 0" class="h-full flex items-center justify-center bg-gray-50 pb-16">
          <div class="text-center max-w-md mx-auto px-4">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Select a case to view details</h3>
            <p class="text-sm text-gray-500">
              Choose a case from the list above to see its entries and data
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Click outside to close dropdown -->
    <div v-if="actionsDropdownOpen"
         @click="actionsDropdownOpen = false"
         class="fixed inset-0 z-0"></div>

    <!-- Confirmation Dialog -->
    <CustomDialogue v-if="dialog.show"
                    :title="dialog.title"
                    :message="dialog.message"
                    :confirm-text="dialog.confirmText"
                    @confirm="dialog.onConfirm"
                    @cancel="dialog.onCancel"
    />

    <!-- QR Code Modal -->
    <QRCodeModal v-if="qrCodeModalVisible"
                 :show="qrCodeModalVisible"
                 :case-data="qrCodeData"
                 @close="qrCodeModalVisible = false"
                 @regenerate="handleRegenerateQR"
                 @revoke="handleRevokeQR"
                 @unrevoke="handleUnrevokeQR" />

    <!-- Close Case Early Modal -->
    <div v-if="closeCaseModalVisible" class="fixed inset-0 z-50 overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/50 z-40" @click="closeCaseModalVisible = false"></div>

      <!-- Modal Container -->
      <div class="flex min-h-full items-center justify-center p-4 relative z-50">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">{{ trans('Close Case Early') }}</h3>
            <button @click="closeCaseModalVisible = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="p-6 space-y-4">
            <!-- Warning Message -->
            <div class="flex items-start space-x-3 p-4 bg-orange-50 border border-orange-200 rounded-lg">
              <svg class="w-5 h-5 text-orange-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <div class="text-sm text-orange-800">
                <p class="font-medium">{{ trans('Warning: This action cannot be undone') }}</p>
                <p class="mt-1">{{ trans('Closing this case will prevent the user from entering any more data and may disrupt planned data collection.') }}</p>
                <p class="mt-1 font-medium">{{ trans('This feature should be used for testing purposes only.') }}</p>
              </div>
            </div>

            <!-- Math Challenge -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                {{ trans('Please verify by solving this simple math problem:') }}
              </label>
              <div class="flex items-center space-x-3">
                <span class="text-lg font-mono">{{ mathChallenge.num1 }} + {{ mathChallenge.num2 }} =</span>
                <input
                  v-model="mathChallenge.answer"
                  type="number"
                  class="w-20 px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:ring-orange-500 focus:border-orange-500"
                  placeholder="?"
                  @keyup.enter="confirmCloseCase"
                />
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
            <button
              @click="closeCaseModalVisible = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
              {{ trans('Cancel') }}
            </button>
            <button
              @click="confirmCloseCase"
              class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700">
              {{ trans('Close Case') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Project Settings Modal -->
    <div v-if="showProjectSettings" class="fixed inset-0 z-50 overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/50 z-40" @click="showProjectSettings = false"></div>

      <!-- Modal Container -->
      <div class="flex min-h-full items-center justify-center p-4 relative z-50">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
          <!-- Header (Fixed) -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200 shrink-0">
            <h3 class="text-lg font-medium text-gray-900">Project Settings</h3>
            <button @click="showProjectSettings = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Content (Scrollable) -->
          <div class="flex-1 overflow-y-auto">
            <EditProject
              ref="editProject"
              :editable="project.isEditable"
              :project="localProject"
              :config="inputsConfig"
              :projectmedia="projectMedia"
              :show-buttons="false"
              @project-updated="handleProjectUpdate"
            />
          </div>

          <!-- Footer (Fixed) -->
          <div class="flex items-center justify-end p-6 border-t border-gray-200 space-x-3 shrink-0">
            <button @click="showProjectSettings = false"
                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-150">
              Cancel
            </button>
            <button @click="saveProjectChanges(false)"
                    :disabled="isLoading"
                    class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150">
              <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Save
            </button>
            <button @click="saveProjectChanges(true)"
                    :disabled="isLoading"
                    class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150">
              <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Save and Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { debounce } from 'lodash';
import SelectedCase from './selected-case.vue';
import EditProject from './editproject.vue';
import ProjectInvites from './projectsInvites.vue';
import Modal from './global/modal.vue';
import CustomDialogue from './global/CustomDialogue.vue';
import PaginationControls from './PaginationControls.vue';
import QRCodeModal from './QRCodeModal.vue';

export default {
  name: 'ProjectCasesView',
  components: {
    SelectedCase,
    EditProject,
    ProjectInvites,
    Modal,
    CustomDialogue,
    PaginationControls,
    QRCodeModal
  },
  props: {
    project: {
      type: Object,
      required: true
    },
    projectInputs: {
      type: [String, Array, Object],
      required: true
    },
    projectMedia: {
      type: Array,
      default: () => []
    },
    invites: {
      type: Array,
      default: () => []
    },
    inputsConfig: {
      type: Object,
      default: () => ({})
    },
    isCreator: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      selectedCase: null,
      actionsDropdownOpen: false,
      showProjectSettings: false,
      casesListHeight: 400, // Initial height in pixels
      isResizing: false,
      isLoading: false,

      // Local project data (mutable copy of prop)
      localProject: { ...this.project },

      // Cases data
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
      },
      totalCasesCount: 0,

      // Dialog
      dialog: {
        show: false,
        title: '',
        message: '',
        confirmText: '',
        onConfirm: null,
        onCancel: null
      },

      // QR Code Modal
      qrCodeModalVisible: false,
      qrCodeData: null,
      apiV2CutoffDate: null,

      // Close Case Early Modal
      closeCaseModalVisible: false,
      closeCaseTarget: null,
      mathChallenge: {
        num1: 0,
        num2: 0,
        answer: ''
      }
    };
  },
  computed: {
    urlToCreateCase() {
      return `/projects/${this.project.id}`;
    },
    parsedProjectInputs() {
      // Parse projectInputs if it's a string
      if (typeof this.projectInputs === 'string') {
        try {
          return JSON.parse(this.projectInputs);
        } catch (e) {
          console.error('Failed to parse projectInputs:', e);
          return [];
        }
      }
      return this.projectInputs;
    }
  },
  watch: {
    // Remove automatic prop syncing to prevent overriding our updates
  },
  mounted() {
    // Initialize local project copy
    this.localProject = { ...this.project };

    // Fetch API v2 cutoff date for QR code availability check
    this.fetchApiV2CutoffDate();

    this.loadCases();
    this.debouncedSearch = debounce(this.loadCases, 300);

    // Handle window resize and mouse events
    document.addEventListener('mouseup', this.stopResize);
    document.addEventListener('mousemove', this.handleResize);
  },
  beforeUnmount() {
    document.removeEventListener('mouseup', this.stopResize);
    document.removeEventListener('mousemove', this.handleResize);
  },
  methods: {
    openProjectSettings() {
      this.showProjectSettings = true;
      this.actionsDropdownOpen = false; // Close the dropdown
    },

    handleProjectUpdate(updatedProject) {
      // Update the local project object with the new data
      Object.assign(this.localProject, updatedProject);

      // Optionally close the modal after successful save
      // this.showProjectSettings = false;
    },

    async saveProjectChanges(closeAfterSave = false) {
      // Trigger save from the EditProject component
      const editProjectComponent = this.$refs.editProject;
      if (editProjectComponent && typeof editProjectComponent.save === 'function') {
        this.isLoading = true;
        try {
          await editProjectComponent.save(false); // Never redirect from EditProject component

          if (closeAfterSave) {
            // Just close the modal, stay on the same page
            this.showProjectSettings = false;
          }
          // If closeAfterSave is false, do nothing - keep modal open for continued editing

        } catch (error) {
          console.error('Failed to save project changes:', error);
        } finally {
          this.isLoading = false;
        }
      }
    },

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

        const response = await fetch(`/projects/${this.project.id}/cases?${params}`, {
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
        this.totalCasesCount = data.total || 0;

        // Process entries to fix entity display and edit modal compatibility
        this.cases.forEach(caseItem => {
          if (caseItem.entries) {
            caseItem.entries.forEach(entry => {
              // Fix entity display - extract name from media object for display
              if (entry.media && typeof entry.media === 'object') {
                entry.media_name = entry.media.name;
                // Set media field for edit modal compatibility (expects string name)
                entry.media = entry.media.name;
              } else if (entry.media && typeof entry.media === 'string') {
                entry.media_name = entry.media;
              }

              // Fix inputs display - parse JSON strings
              if (entry.inputs && typeof entry.inputs === 'string') {
                try {
                  entry.inputs = JSON.parse(entry.inputs);
                } catch (e) {
                  console.error('Error parsing entry inputs:', e);
                  entry.inputs = {};
                }
              } else if (!entry.inputs) {
                entry.inputs = {};
              }
            });
          }
        });

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
        this.totalCasesCount = 0;
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
    },

    handleSelectedCase(selectedCase) {
      // Add project data to selectedCase for compatibility
      if (selectedCase && !selectedCase.project) {
        selectedCase.project = this.project;
      }
      this.selectedCase = selectedCase;
    },

    getUserDisplayName(caseData) {
      if (caseData.user) {
        if (caseData.user.profile?.name) {
          return caseData.user.profile.name;
        }
        return caseData.user.email;
      }
      return 'No user assigned';
    },

    getStatusBadge(caseData) {
      if (caseData.backend) {
        return null;
      }
      
      // Use backend-provided status if available, otherwise fallback to date parsing
      const status = caseData.status || this.calculateStatusFromDate(caseData);
      
      switch (status) {
        case 'pending':
          return {
            text: 'Pending',
            class: 'bg-yellow-100 text-yellow-800'
          };
        case 'active':
          return {
            text: 'Active',
            class: 'bg-green-100 text-green-800'
          };
        case 'completed':
          return {
            text: 'Completed',
            class: 'bg-gray-100 text-gray-800'
          };
        case 'backend':
          return null; // Backend cases don't show status badge
        default:
          return null;
      }
    },
    
    calculateStatusFromDate(caseData) {
      const now = new Date();
      const lastDay = this.parseDate(caseData.last_day);
      if (!lastDay || caseData.last_day === 'Case not started by the user') {
        return 'pending';
      }
      if (lastDay < now) {
        return 'completed';
      } else {
        return 'active';
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

    formatDate(dateString) {
      if (!dateString) return 'Unknown';
      try {
        return new Date(dateString).toLocaleDateString();
      } catch {
        return dateString;
      }
    },

    exportCase(caseItem) {
      window.open(`/cases/${caseItem.id}/export`, '_blank');
    },

    confirmDeleteCase(caseItem) {
      this.dialog.show = true;
      this.dialog.title = this.trans('Confirm Case deletion');
      this.dialog.message = this.trans('Do you want to delete this case and all the entries?');
      this.dialog.confirmText = this.trans('YES delete case and all the entries');
      this.dialog.onConfirm = () => this.deleteCase(caseItem);
      this.dialog.onCancel = () => {
        this.dialog.show = false;
      };
    },

    async deleteCase(caseItem) {
      try {
        const response = await fetch(`/cases/${caseItem.id}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (response.ok) {
          // Remove from local list
          this.cases = this.cases.filter(c => c.id !== caseItem.id);
          this.totalCasesCount--;

          // Clear selection if deleted case was selected
          if (this.selectedCase?.id === caseItem.id) {
            this.selectedCase = null;
          }

          this.dialog.show = false;

          // Case deleted successfully
        } else {
          throw new Error('Delete failed');
        }
      } catch (error) {
        console.error('Error deleting case:', error);
        alert('Error deleting case. Please try again.');
      }
    },

    // Resize functionality
    startResize(event) {
      this.isResizing = true;
      this.startY = event.clientY;
      this.startHeight = this.casesListHeight;
      document.body.style.cursor = 'row-resize';
      document.body.style.userSelect = 'none';
      event.preventDefault();
    },

    handleResize(event) {
      if (!this.isResizing) return;

      const deltaY = event.clientY - this.startY;
      let newHeight = this.startHeight + deltaY;

      // Constrain height between 200px and 80% of window height
      const maxHeight = window.innerHeight * 0.8;
      newHeight = Math.max(200, Math.min(maxHeight, newHeight));

      this.casesListHeight = newHeight;
    },

    stopResize() {
      if (this.isResizing) {
        this.isResizing = false;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
      }
    },

    // QR Code functions
    canShowQRCode(caseItem) {
      // Don't show for MART projects
      if (this.localProject.is_mart_project) {
        return false;
      }

      // Don't show if case has no user
      if (!caseItem.user_id) {
        return false;
      }

      // Check if project is API v2 (created after cutoff date)
      if (this.apiV2CutoffDate) {
        const projectDate = new Date(this.localProject.created_at);
        const cutoffDate = new Date(this.apiV2CutoffDate);
        if (projectDate < cutoffDate) {
          return false;
        }
      }

      return true;
    },

    async showQRCodeModal(caseItem) {
      try {
        const response = await axios.get(`/cases/${caseItem.id}/qrcode`);
        this.qrCodeData = response.data;
        this.qrCodeModalVisible = true;
      } catch (error) {
        if (error.response?.status === 403) {
          alert(error.response.data.error || 'Cannot generate QR code for this case');
        } else {
          alert('Failed to generate QR code');
          console.error('QR code generation error:', error);
        }
      }
    },

    async handleRegenerateQR(caseId) {
      if (!confirm('Regenerate QR code? This will invalidate the current QR code.')) {
        return;
      }

      try {
        const response = await axios.post(`/cases/${caseId}/qrcode/regenerate`);
        this.qrCodeData = response.data;

        // Update the case in the cases list (regenerate clears revocation)
        const caseIndex = this.cases.findIndex(c => c.id === caseId);
        if (caseIndex !== -1) {
          this.cases[caseIndex].qr_token_revoked_at = null;
        }

        // Update selectedCase if it matches
        if (this.selectedCase && this.selectedCase.id === caseId) {
          this.selectedCase.qr_token_revoked_at = null;
        }
      } catch (error) {
        alert('Failed to regenerate QR code');
        console.error('QR code regeneration error:', error);
      }
    },

    async handleRevokeQR(caseId, reason) {
      try {
        await axios.post(`/cases/${caseId}/qrcode/revoke`, { reason });

        // Update the case in the cases list
        const caseIndex = this.cases.findIndex(c => c.id === caseId);
        if (caseIndex !== -1) {
          this.cases[caseIndex].qr_token_revoked_at = new Date().toISOString();
        }

        // Update selectedCase if it matches
        if (this.selectedCase && this.selectedCase.id === caseId) {
          this.selectedCase.qr_token_revoked_at = new Date().toISOString();
        }

        // Update the modal data to show revoked status
        if (this.qrCodeData && this.qrCodeData.case_id === caseId) {
          this.qrCodeData.is_revoked = true;
          this.qrCodeData.revoked_at = new Date().toISOString();
          this.qrCodeData.revoked_reason = reason;
        }

        this.qrCodeModalVisible = false;
        alert('QR code revoked successfully');
      } catch (error) {
        alert('Failed to revoke QR code');
        console.error('QR code revocation error:', error);
      }
    },

    async handleUnrevokeQR(caseId) {
      try {
        await axios.post(`/cases/${caseId}/qrcode/unrevoke`);

        // Update the case in the cases list
        const caseIndex = this.cases.findIndex(c => c.id === caseId);
        if (caseIndex !== -1) {
          this.cases[caseIndex].qr_token_revoked_at = null;
        }

        // Update selectedCase if it matches
        if (this.selectedCase && this.selectedCase.id === caseId) {
          this.selectedCase.qr_token_revoked_at = null;
        }

        // Update the modal data to show active status
        if (this.qrCodeData && this.qrCodeData.case_id === caseId) {
          this.qrCodeData.is_revoked = false;
          this.qrCodeData.revoked_at = null;
          this.qrCodeData.revoked_reason = null;
        }

        alert('QR code re-enabled successfully');
      } catch (error) {
        alert('Failed to re-enable QR code');
        console.error('QR code un-revocation error:', error);
      }
    },

    // Close Case Early functions
    showCloseCaseModal(caseItem) {
      // Generate random math challenge (1-9 + 1-9)
      this.mathChallenge.num1 = Math.floor(Math.random() * 9) + 1;
      this.mathChallenge.num2 = Math.floor(Math.random() * 9) + 1;
      this.mathChallenge.answer = '';

      this.closeCaseTarget = caseItem;
      this.closeCaseModalVisible = true;
    },

    async confirmCloseCase() {
      const expectedAnswer = this.mathChallenge.num1 + this.mathChallenge.num2;
      const userAnswer = parseInt(this.mathChallenge.answer);

      // Validate math answer
      if (isNaN(userAnswer) || userAnswer !== expectedAnswer) {
        alert('Incorrect math answer. Please try again.');
        return;
      }

      try {
        const response = await axios.post(`/cases/${this.closeCaseTarget.id}/close-early`, {
          math_answer: userAnswer,
          expected_answer: expectedAnswer
        });

        if (response.data.success) {
          // Update the case in the cases list
          const caseIndex = this.cases.findIndex(c => c.id === this.closeCaseTarget.id);
          if (caseIndex !== -1) {
            // Update the duration field to reflect the new lastDay
            const oldDuration = this.cases[caseIndex].duration;
            const newLastDay = response.data.new_last_day;

            // Parse and update duration string
            const durationParts = oldDuration.split('|');
            const updatedParts = durationParts.map(part => {
              if (part.startsWith('lastDay:')) {
                return `lastDay:${newLastDay}`;
              }
              return part;
            });

            this.cases[caseIndex].duration = updatedParts.join('|');
          }

          // Update selectedCase if it matches
          if (this.selectedCase && this.selectedCase.id === this.closeCaseTarget.id) {
            const oldDuration = this.selectedCase.duration;
            const newLastDay = response.data.new_last_day;

            const durationParts = oldDuration.split('|');
            const updatedParts = durationParts.map(part => {
              if (part.startsWith('lastDay:')) {
                return `lastDay:${newLastDay}`;
              }
              return part;
            });

            this.selectedCase.duration = updatedParts.join('|');
          }

          // Close modal
          this.closeCaseModalVisible = false;

          alert('Case closed successfully. The case last day has been set to yesterday.');

          // Refresh the page to update case statuses
          window.location.reload();
        }
      } catch (error) {
        if (error.response?.status === 422) {
          alert('Incorrect math answer. Please try again.');
        } else if (error.response?.data?.error) {
          alert(error.response.data.error);
        } else {
          alert('Failed to close case');
          console.error('Close case error:', error);
        }
      }
    },

    async fetchApiV2CutoffDate() {
      try {
        // Fetch from Laravel config or settings API
        // For now, use the config value from app.php
        const response = await axios.get('/api/settings/api_v2_cutoff_date');
        this.apiV2CutoffDate = response.data.value;
      } catch (error) {
        // If not available via API, use hardcoded default from config
        this.apiV2CutoffDate = '2025-03-21'; // Default from config/app.php
        console.warn('Could not fetch API v2 cutoff date, using default');
      }
    },

    // Translation helper
    trans(key) {
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      } else {
        if (window.trans[key] === "") return key;
        return window.trans[key];
      }
    }
  }
};
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}

/* Custom scrollbars */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
