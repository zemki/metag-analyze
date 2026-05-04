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
             class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ trans('Create Case') }}
          </a>

          <!-- Project Settings Button -->
          <button @click="openProjectSettings"
                  class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <!-- Cases List Panel (Top). Fixed pixel height, but capped in mounted()
           at 55% of viewport so the entries panel below always has room. -->
      <div class="bg-white border-b border-gray-200 flex-shrink-0"
           :style="{ height: casesListHeight + 'px' }">
        <!-- Cases section bar (redesigned) -->
        <SectionBar
          title="Cases"
          :count="totalCasesCount"
          singular="case"
          plural="cases"
        />

        <!-- Status guide (redesigned) -->
        <div :style="statusGuideStyle">
          <span :style="statusGuideLabelStyle">Status guide</span>
          <span v-for="status in capabilities.visibleStatuses" :key="status"
                style="display: inline-flex; align-items: center; gap: 6px;">
            <StatusPill :status="status" />
            <span style="color: #c4cce0;">—</span>
            <span>{{ STATUS_NOTES[status] }}</span>
          </span>
        </div>

        <!-- Search and filters (redesigned) -->
        <FilterRow>
          <!-- Search input with embedded icon -->
          <div :style="searchWrapStyle">
            <span style="color: #6b7795; display: inline-flex; align-items: center;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search cases…"
              :style="searchInputStyle"
            />
          </div>

          <!-- Status filter -->
          <select v-model="statusFilter" @change="loadCases" :style="selectStyle">
            <option value="">All status</option>
            <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option v-if="capabilities.showBackendStatus" value="backend">Backend</option>
          </select>

          <!-- Sort field -->
          <select v-model="sortBy" @change="loadCases" :style="selectStyle">
            <option value="created_at">Sort: Created</option>
            <option value="name">Sort: Name</option>
            <option value="user_id">Sort: User</option>
            <option value="entries_count">Sort: Entries</option>
            <option value="status">Sort: Status</option>
          </select>

          <!-- Sort direction toggle -->
          <button
            type="button"
            @click="toggleSortOrder"
            :title="sortOrder === 'desc' ? 'Sort ascending' : 'Sort descending'"
            :style="sortToggleStyle"
          >
            <span :style="{ transform: sortOrder === 'asc' ? 'scaleY(-1)' : 'none', display: 'inline-flex' }">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21 16-4 4-4-4" />
                <path d="M17 20V4" />
                <path d="m3 8 4-4 4 4" />
                <path d="M7 4v16" />
              </svg>
            </span>
          </button>

          <!-- Per page -->
          <select v-model="perPage" @change="loadCases" :style="selectStyle">
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </FilterRow>

        <!-- Cases list itself (redesigned). overflow-y-scroll forces the
             scrollbar to be visible at all times, so it's clear there's
             more content below the visible rows. -->
        <div class="overflow-y-scroll" :style="{ height: (casesListHeight - 200) + 'px' }">
          <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <div v-else-if="cases.length > 0">
            <CaseRow
              v-for="caseItem in cases"
              :key="caseItem.id"
              :case-item="caseItem"
              :project="localProject"
              :selected="selectedCase?.id === caseItem.id"
              :is-creator="isCreator"
              :status-override="calculateStatusFromDate(caseItem)"
              @select="handleSelectedCase"
              @export="exportCase"
              @qr="showQRCodeModal"
              @close-early="showCloseCaseModal"
              @delete="confirmDeleteCase"
            />
          </div>

          <CasesEmptyState
            v-else
            :filtered="!!(searchQuery || statusFilter)"
            :create-url="urlToCreateCase + '/cases/new'"
          />
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

      <!-- Resize Handle. Always visible, with a clear divider strip and a
           dotted grip in the middle so it reads as a draggable boundary
           rather than just a separator line. -->
      <div @mousedown="startResize"
           class="flex-shrink-0 cursor-row-resize relative group select-none transition-colors"
           style="height: 18px; background: #e2e8f0; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;"
           title="Drag to resize the cases list">
        <!-- Hover highlight overlay (no extra DOM cost) -->
        <div class="absolute inset-0 group-hover:bg-blue-100 transition-colors pointer-events-none"></div>
        <!-- Grip dots — six dots arranged in two rows so the affordance is unmistakable -->
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none flex flex-col gap-[3px]">
          <div class="flex gap-[6px]">
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
          </div>
          <div class="flex gap-[6px]">
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
            <span class="block w-[3px] h-[3px] rounded-full bg-slate-500 group-hover:bg-blue-600 transition-colors"></span>
          </div>
        </div>
      </div>

      <!-- Case Details Panel (Bottom).
           The case-detail header (name, status pill, ID, meta, actions) is now
           rendered inside <SelectedCase> via <CaseDetailStrip>. min-h-0 +
           flex-1 lets it scroll internally; min-h-[260px] guarantees that even
           on a short viewport, the strip + a few entries are always visible. -->
      <div class="flex-1 bg-white overflow-hidden min-h-[260px]">
        <div v-if="selectedCase" class="h-full flex flex-col">
          <SelectedCase
            :project-inputs="parsedProjectInputs"
            :cases="selectedCase"
            :api-v2-cutoff-date="apiV2CutoffDate"
            :production-url="productionUrl"
            :is-creator="isCreator"
            @case-export="exportCase"
            @case-qr="showQRCodeModal"
            @case-close-early="showCloseCaseModal"
            @case-delete="confirmDeleteCase"
            @entries-changed="refreshAfterEntriesChanged"
          />
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
    <Modal v-if="dialog.show"
           :title="dialog.title"
           :visible="dialog.show"
           :confirm-text="dialog.confirmText"
           :danger="dialog.danger"
           @confirm="dialog.onConfirm"
           @cancel="dialog.onCancel">
      <div v-html="dialog.message"></div>
    </Modal>

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
                    :disabled="isLoading || !project.isEditable"
                    class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150">
              <svg v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Save
            </button>
            <button @click="saveProjectChanges(true)"
                    :disabled="isLoading || !project.isEditable"
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
import PaginationControls from './PaginationControls.vue';
import QRCodeModal from './QRCodeModal.vue';
// Redesign primitives (stage 2)
import StatusPill from './global/StatusPill.vue';
import SectionBar from './global/SectionBar.vue';
import FilterRow from './global/FilterRow.vue';
import CaseRow from './global/CaseRow.vue';
import CasesEmptyState from './global/CasesEmptyState.vue';
import { useProjectCapabilities } from '../utils/useProjectCapabilities.js';
import { emitter } from '../app.js';

// Status note copy used by the inline status-guide legend.
const STATUS_NOTES = {
  pending: 'Case not yet started by user',
  active: 'Case currently in progress',
  completed: 'Case has ended',
  backend: 'Backend-only case',
};

// Reusable inline-style fragments for the redesigned filter row.
const STATUS_GUIDE_STYLE = {
  display: 'flex',
  alignItems: 'center',
  flexWrap: 'wrap',
  gap: '14px',
  padding: '10px 20px',
  background: '#fbfcfe',
  borderBottom: '1px solid #e3e8f3',
  fontSize: '11.5px',
  color: '#6b7795',
};
const STATUS_GUIDE_LABEL_STYLE = {
  fontWeight: 600,
  color: '#3b4768',
  marginRight: '2px',
};
// All filter-row controls share the same explicit height (34px) so the
// search input, native selects, and sort-toggle button line up perfectly.
const FILTER_CONTROL_HEIGHT = '34px';
const FILTER_BORDER = '1px solid #e3e8f3';

const SEARCH_WRAP_STYLE = {
  flex: 1,
  display: 'inline-flex',
  alignItems: 'center',
  gap: '8px',
  height: FILTER_CONTROL_HEIGHT,
  padding: '0 11px',
  border: FILTER_BORDER,
  borderRadius: '8px',
  background: '#ffffff',
  boxSizing: 'border-box',
};
const SEARCH_INPUT_STYLE = {
  flex: 1,
  height: '100%',
  border: 'none',
  outline: 'none',
  fontSize: '13px',
  color: '#0f1b3d',
  background: 'transparent',
  fontFamily: 'inherit',
  padding: 0,
};
const SELECT_STYLE = {
  minWidth: '130px',
  height: FILTER_CONTROL_HEIGHT,
  padding: '0 28px 0 10px',
  border: FILTER_BORDER,
  borderRadius: '8px',
  background: '#ffffff',
  fontSize: '13px',
  color: '#0f1b3d',
  cursor: 'pointer',
  fontFamily: 'inherit',
  boxSizing: 'border-box',
};
const SORT_TOGGLE_STYLE = {
  width: '36px',
  height: FILTER_CONTROL_HEIGHT,
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  border: FILTER_BORDER,
  background: '#ffffff',
  color: '#3b4768',
  borderRadius: '8px',
  cursor: 'pointer',
  boxSizing: 'border-box',
  padding: 0,
};

export default {
  name: 'ProjectCasesView',
  components: {
    SelectedCase,
    EditProject,
    ProjectInvites,
    Modal,
    PaginationControls,
    QRCodeModal,
    // Redesign primitives
    StatusPill,
    SectionBar,
    FilterRow,
    CaseRow,
    CasesEmptyState,
  },
  inject: ['productionUrl'],
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
      // Redesign primitives — exposed so the template can use them
      STATUS_NOTES,
      statusGuideStyle: STATUS_GUIDE_STYLE,
      statusGuideLabelStyle: STATUS_GUIDE_LABEL_STYLE,
      searchWrapStyle: SEARCH_WRAP_STYLE,
      searchInputStyle: SEARCH_INPUT_STYLE,
      selectStyle: SELECT_STYLE,
      sortToggleStyle: SORT_TOGGLE_STYLE,

      selectedCase: null,
      actionsDropdownOpen: false,
      showProjectSettings: false,
      // Initial height in pixels for the cases panel. Bumped/capped in
      // mounted() so it never eats more than ~60% of the viewport — the
      // entries panel below needs at least 260px to show the strip + a
      // few entries. User-resizable via the drag handle below.
      casesListHeight: 520,
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
        confirmText: 'Confirm',
        danger: false,
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
      return `${this.productionUrl}/projects/${this.project.id}`;
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
    },
    /**
     * Project-type capabilities. Centralizes regular vs MART rules so the
     * template doesn't re-derive `is_mart_project` checks inline.
     * The composable returns a Vue computed; we unwrap it here to expose
     * the plain object on the Options API instance.
     */
    capabilities() {
      return useProjectCapabilities(() => this.localProject).value;
    },
  },
  watch: {
    // Remove automatic prop syncing to prevent overriding our updates
  },
  mounted() {
    // Initialize local project copy
    this.localProject = { ...this.project };

    // Cap the cases panel at ~60% of the viewport so the entries panel
    // below always has room (roughly 260+ px after the page header).
    // This is a one-time initial fit; the user can override via the drag
    // handle afterwards.
    const cap = Math.max(420, Math.round(window.innerHeight * 0.6));
    if (this.casesListHeight > cap) {
      this.casesListHeight = cap;
    }

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

        const response = await axios.get(`/projects/${this.project.id}/cases`, { params: Object.fromEntries(params) });
        const data = response.data;
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

    /**
     * Refresh the cases list after a child has changed entries (add /
     * edit / delete) and keep the same case selected. Avoids the full
     * page reload the legacy code did, so search / sort / scroll /
     * panel size are preserved.
     */
    async refreshAfterEntriesChanged() {
      const selectedId = this.selectedCase?.id;
      await this.loadCases();
      if (selectedId) {
        const found = this.cases.find((c) => c.id === selectedId);
        if (found) {
          this.handleSelectedCase(found);
        } else {
          // The case is no longer in the current page (e.g. filtered out
          // after delete); clear selection so the right pane shows the
          // empty state instead of stale data.
          this.selectedCase = null;
        }
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

    exportCase(caseItem) {
      window.open(`${this.productionUrl}/cases/${caseItem.id}/export`, '_blank');
    },

    confirmDeleteCase(caseItem) {
      this.dialog.show = true;
      this.dialog.title = this.trans('Confirm Delete');
      this.dialog.message = this.trans('Do you want to delete this case and all the entries?');
      this.dialog.confirmText = this.trans('Delete Case');
      this.dialog.danger = true;
      this.dialog.onConfirm = () => this.deleteCase(caseItem);
      this.dialog.onCancel = () => {
        this.dialog.show = false;
      };
    },

    async deleteCase(caseItem) {
      try {
        const response = await axios.delete(`/cases/${caseItem.id}`);

        if (response.status === 200) {
          // Remove from local list
          this.cases = this.cases.filter(c => c.id !== caseItem.id);
          this.totalCasesCount--;

          // Clear selection if deleted case was selected
          if (this.selectedCase?.id === caseItem.id) {
            this.selectedCase = null;
          }

          this.dialog.show = false;

          // Show success message
          emitter.emit('show-snackbar', this.trans('Case deleted successfully'));
        } else {
          throw new Error('Delete failed');
        }
      } catch (error) {
        console.error('Error deleting case:', error);
        emitter.emit('show-snackbar', this.trans('Error deleting case. Please try again.'));
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
          emitter.emit('show-snackbar', error.response.data.error || 'Cannot generate QR code for this case');
        } else {
          emitter.emit('show-snackbar', 'Failed to generate QR code');
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
        emitter.emit('show-snackbar', 'Failed to regenerate QR code');
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
        emitter.emit('show-snackbar', 'QR code revoked successfully');
      } catch (error) {
        emitter.emit('show-snackbar', 'Failed to revoke QR code');
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

        emitter.emit('show-snackbar', 'QR code re-enabled successfully');
      } catch (error) {
        emitter.emit('show-snackbar', 'Failed to re-enable QR code');
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
        emitter.emit('show-snackbar', this.trans('Incorrect math answer. Please try again.'));
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

          // Store message for display after reload
          localStorage.setItem('snackbarMessage', this.trans('Case closed successfully. The case last day has been set to yesterday.'));

          // Refresh the page to update case statuses
          window.location.reload();
        }
      } catch (error) {
        if (error.response?.status === 422) {
          emitter.emit('show-snackbar', this.trans('Incorrect math answer. Please try again.'));
        } else if (error.response?.data?.error) {
          emitter.emit('show-snackbar', error.response.data.error);
        } else {
          emitter.emit('show-snackbar', this.trans('Failed to close case'));
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
