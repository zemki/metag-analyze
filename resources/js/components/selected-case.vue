<template>
  <section
    aria-labelledby="message-heading"
    class="flex flex-col flex-1 h-full min-w-0 overflow-hidden xl:order-last"
  >
    <!-- Edit / Add Entry modal — extracted to its own component (Stage 4). -->
    <EditEntryModal
      v-if="cases && cases.id"
      :visible="editModalVisible"
      :entry="editEntry"
      :case-id="cases.id"
      :project-inputs="parsedProjectInputs"
      :is-mart-project="isMartProject"
      :entity-label="entityLabel"
      @close="editModalVisible = false"
      @saved="onEntrySaved"
    />

    <!-- Case-detail strip — always shown when a case is selected, regardless of consultable state. -->
    <CaseDetailStrip
      v-if="selectedCase"
      :case-item="selectedCase"
      :project="cases.project"
      :is-creator="isCreator"
      :distinct-graph-href="isMartProject ? '' : distinctPath()"
      :grouped-graph-href="isMartProject ? '' : groupedCasesPath()"
      :can-add-entry="!!selectedCase.backend"
      @add-entry="openEditModal()"
      @export="$emit('case-export', $event)"
      @qr="$emit('case-qr', $event)"
      @close-early="$emit('case-close-early', $event)"
      @delete="$emit('case-delete', $event)"
    />

    <!-- Entries area: shown whenever a case is selected. Empty / not-consultable
         states live INSIDE this wrapper so the flex layout always sizes properly. -->
    <div class="flex-1 min-h-0 overflow-y-auto" v-if="selectedCase">
      <!-- Mid-stream cases (regular: still active; MART: not yet completed) -->
      <NotConsultableState
        v-if="!selectedCase.consultable"
        :reason="isMartProject ? 'mart-active' : 'active'"
      />

      <!-- Consultable: show the table or the empty state -->
      <template v-else>
        <SectionBar
          v-if="selectedCase.entries && selectedCase.entries.length > 0"
          title="Entries"
          :count="selectedCase.entries.length"
          singular="entry"
          plural="entries"
        />
        <EntryTable
          v-if="selectedCase.entries && selectedCase.entries.length > 0"
          :entries="selectedCase.entries"
          :project-inputs="parsedProjectInputs"
          :project="cases.project"
          :entity-label="entityLabel"
          @edit="openEditModal"
          @delete="confirmDeleteEntry"
        />
        <EntriesEmptyState v-else />
      </template>
    </div>
    <Snackbar v-if="showSnackbar" :message="snackbarMessage" ref="snackbar" />
  </section>
</template>

<script>
import { ref, computed } from "vue";
import moment from "moment";
import Snackbar from "./global/snackbar.vue";
// Redesign primitives — explicit local imports so the components don't rely
// on the global registry being picked up correctly across SFC styles.
import CaseDetailStrip from "./global/CaseDetailStrip.vue";
import EditEntryModal from "./global/EditEntryModal.vue";
import EntriesEmptyState from "./global/EntriesEmptyState.vue";
import NotConsultableState from "./global/NotConsultableState.vue";
import EntryTable from "./global/EntryTable.vue";
import SectionBar from "./global/SectionBar.vue";

export default {
  name: "SelectedCase",
  components: {
    Snackbar,
    CaseDetailStrip,
    EditEntryModal,
    EntriesEmptyState,
    NotConsultableState,
    EntryTable,
    SectionBar,
  },
  props: {
    cases: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    projectInputs: {
      type: Array,
      required: true,
      default: () => [],
    },
    productionUrl: {
      type: String,
      default: "",
    },
    apiV2CutoffDate: {
      type: String,
      default: "2025-03-21",
    },
    isCreator: {
      type: Boolean,
      default: false,
    },
  },
  emits: [
    "update:selectedCase",
    "case-export",
    "case-qr",
    "case-close-early",
    "case-delete",
    "entries-changed",
  ],
  setup(props, { emit }) {
    const snackbarMessage = ref("");
    const showSnackbar = ref(false);

    // Helper to sanitize malformed dates (e.g., "2025-11-26 15:20:34.273357.000000")
    const sanitizeDate = (dateValue) => {
      if (!dateValue) return dateValue;
      if (typeof dateValue === 'string') {
        // Fix double decimal format: "2025-11-26 15:20:34.273357.000000" -> "2025-11-26 15:20:34.273357"
        const doubleDotMatch = dateValue.match(/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)\.\d+$/);
        if (doubleDotMatch) {
          return doubleDotMatch[1];
        }
      }
      return dateValue;
    };

    // Check if project is legacy (created before API v2 cutoff date)
    const isLegacyProject = computed(() => {
      if (!props.cases?.project?.created_at) return false;
      const projectDate = new Date(props.cases.project.created_at);
      const cutoffDate = new Date(props.apiV2CutoffDate);
      return projectDate < cutoffDate;
    });

    // Get the entity/media label based on project type
    const entityLabel = computed(() => {
      if (isLegacyProject.value) return 'Media';
      return props.cases?.project?.entity_name || 'Entity';
    });

    /**
     * Defensively parsed projectInputs — accepts either a JSON string or
     * an already-parsed array. Used by the redesigned EntryTable.
     */
    const parsedProjectInputs = computed(() => {
      const raw = props.projectInputs;
      if (typeof raw === 'string') {
        try {
          return JSON.parse(raw || '[]');
        } catch {
          return [];
        }
      }
      return Array.isArray(raw) ? raw : [];
    });

    // True when the project is MART. The graph routes only render
    // legacy / non-MART entries, so the buttons are hidden for MART.
    const isMartProject = computed(() => {
      const inputs = parsedProjectInputs.value;
      return inputs.length > 0 && inputs[0]?.type === 'mart';
    });

    // Edit modal state — the parent owns visibility + which entry is being
    // edited; the modal component (EditEntryModal) owns the form internals.
    const editModalVisible = ref(false);
    const editEntry = ref(null);

    const selectedCase = computed(() => {
      if (props.cases && props.cases.name) {
        let processedCases = { ...props.cases };
        processedCases.entries = processEntries(props.cases.entries);
        return processedCases;
      }
      return null;
    });

    const trans = (key) => {
      if (
        typeof window.trans === "undefined" ||
        typeof window.trans[key] === "undefined"
      ) {
        return key;
      } else {
        if (window.trans[key] === "") return key;
        return window.trans[key];
      }
    };

    const showSnackbarMessage = (message) => {
      snackbarMessage.value = message;
      showSnackbar.value = true;
      setTimeout(() => {
        showSnackbar.value = false;
      }, 3000); // Snackbar duration
    };

    /**
     * Pre-process entries for the EntryTable. Two responsibilities:
     *   1. Normalize `entry.inputs` (server may send a JSON string).
     *   2. Add `*_readable` fields the previous-version sub-row reads:
     *      - `entry.created_at_readable`         (when the row was submitted)
     *      - `entry.inputs.firstValue.begin_readable` / `.end_readable`
     *
     * Date formatting in the table proper (Start/End columns) is handled
     * by EntryTable itself via `parseEntryDate`/`formatTime`/`formatDate`,
     * so we don't pre-compute `entry.begin_readable` / `entry.end_readable`
     * here anymore — those were vestigial fields read only by the legacy
     * vertical entries list (now removed).
     */
    const processEntries = (entries = []) => {
      return entries.map((entry) => {
        entry.created_at_readable = moment(entry.created_at).format(
          "DD.MM.YYYY H:m:ss"
        );

        // entry.inputs may arrive as a JSON string from the server.
        if (typeof entry.inputs !== "object") {
          try {
            entry.inputs = JSON.parse(entry.inputs);
          } catch (e) {
            entry.inputs = {};
          }
        }

        // Previous-version sub-row reads .firstValue.{begin,end}_readable.
        if (entry.inputs && entry.inputs.firstValue) {
          entry.inputs.firstValue.begin_readable = moment(
            sanitizeDate(entry.inputs.firstValue.begin)
          ).format("DD.MM.YYYY HH:mm");
          entry.inputs.firstValue.end_readable = moment(
            sanitizeDate(entry.inputs.firstValue.end)
          ).format("DD.MM.YYYY HH:mm");

          if (typeof entry.inputs.firstValue.inputs !== "object") {
            try {
              entry.inputs.firstValue.inputs = JSON.parse(
                entry.inputs.firstValue.inputs
              );
            } catch (e) {
              entry.inputs.firstValue.inputs = {};
            }
          }
        }

        return entry;
      });
    };

    /**
     * Open the edit modal. Without an entry => "add new" mode; with one =>
     * edit. The save flow lives entirely inside EditEntryModal; this
     * component only reacts to the `saved` event.
     */
    const openEditModal = (entry = null) => {
      editEntry.value = entry || null;
      editModalVisible.value = true;
    };

    /**
     * Save handler — closes the modal, shows a snackbar, and tells the
     * parent to refetch so the new entry appears in the table without a
     * full page reload (preserves search / sort / scroll / modal state).
     */
    const onEntrySaved = (payload = {}) => {
      editModalVisible.value = false;
      showSnackbarMessage(
        trans(payload.isAdding ? 'Entry added.' : 'Entry updated.')
      );
      emit('entries-changed');
    };

    const distinctPath = () => {
      return props.productionUrl + "/projects/" + props.cases.project.id + "/distinctcases/" + props.cases.id;
    };

    const groupedCasesPath = () => {
      return props.productionUrl + "/projects/" + props.cases.project.id + "/groupedcases/" + props.cases.id;
    };

    /**
     * Confirm + delete a single entry. Wired to EntryTable's @delete event.
     * Mirrors the existing global app.js confirmdelete/deleteEntry pattern
     * but scoped to this component so the redesigned table is self-contained.
     */
    const confirmDeleteEntry = (entry) => {
      if (!entry || !entry.id) return;
      const ok = window.confirm(
        trans('You are about to delete this entry. Continue?')
      );
      if (!ok) return;
      window.axios
        .delete(`/cases/${props.cases.id}/entries/${entry.id}`)
        .then(() => {
          showSnackbarMessage(trans('Entry deleted.'));
          emit('entries-changed');
        })
        .catch(() => {
          showSnackbarMessage(
            trans('There was an error during the request - refresh page and try again')
          );
        });
    };

    return {
      editModalVisible,
      editEntry,
      openEditModal,
      onEntrySaved,
      selectedCase,
      snackbarMessage,
      showSnackbar,
      trans,
      distinctPath,
      groupedCasesPath,
      entityLabel,
      isMartProject,
      parsedProjectInputs,
      confirmDeleteEntry,
    };
  },
};
</script>

<style></style>
