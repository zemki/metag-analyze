import Gravatar from "vue-gravatar";
import EditProject from "./components/editproject.vue";
import ProjectInvites from "./components/projectsInvites.vue";
import Graph from "./components/graph.vue";
import MedTagGraph from "./components/groupedentries.vue";
import NotificationCenter from "./components/notificationcenter.vue";
import AudioPlayer from "./components/audioplayer.vue";
import ProjectsList from "./components/projects-list.vue";
import SelectedCase from "./components/selected-case.vue";
import createproject from "./components/createproject.vue";
import Modal from "./components/global/modal.vue";
import Snackbar from "./components/global/snackbar.vue";
import DebugPanel from "./components/debug-panel.vue";
import Breadcrumb from "./components/breadcrumb.vue";
import Treemap from "./components/treemap.vue";
import PaginationControls from "./components/PaginationControls.vue";
import ProjectCasesView from "./components/ProjectCasesView.vue";
import EmailChangeModal from "./components/EmailChangeModal.vue";

// Redesign primitives (stages 1+2 of the MeTag Analyze redesign).
// See design_handoff_metag_redesign/README.md and the
// useProjectCapabilities composable in ./utils/useProjectCapabilities.js.
import StatusPill from "./components/global/StatusPill.vue";
import IDPill from "./components/global/IDPill.vue";
import QRRevokedBadge from "./components/global/QRRevokedBadge.vue";
import SectionBar from "./components/global/SectionBar.vue";
import FilterRow from "./components/global/FilterRow.vue";
import CaseAction from "./components/global/CaseAction.vue";
import CaseRow from "./components/global/CaseRow.vue";
import CasesEmptyState from "./components/global/CasesEmptyState.vue";
// Stage 3a additions
import CaseDetailStrip from "./components/global/CaseDetailStrip.vue";
import EntriesEmptyState from "./components/global/EntriesEmptyState.vue";
import NotConsultableState from "./components/global/NotConsultableState.vue";
import EntryTable from "./components/global/EntryTable.vue";

// Create a named export for all components to be used with app.component() in app.js
export const components = {
  "edit-project": EditProject,
  "project-invites": ProjectInvites,
  "graph": Graph,
  "medtaggraph": MedTagGraph,
  "notification-center": NotificationCenter,
  "audio-player": AudioPlayer,
  "projects-list": ProjectsList,
  "selected-case": SelectedCase,
  "create-project": createproject,
  "modal": Modal,
  "snackbar": Snackbar,
  "debug-panel": DebugPanel,
  "breadcrumb": Breadcrumb,
  "treemap": Treemap,
  "pagination-controls": PaginationControls,
  "project-cases-view": ProjectCasesView,
  "email-change-modal": EmailChangeModal,

  // Redesign primitives
  "status-pill": StatusPill,
  "id-pill": IDPill,
  "qr-revoked-badge": QRRevokedBadge,
  "section-bar": SectionBar,
  "filter-row": FilterRow,
  "case-action": CaseAction,
  "case-row": CaseRow,
  "cases-empty-state": CasesEmptyState,
  // Stage 3a
  "case-detail-strip": CaseDetailStrip,
  "entries-empty-state": EntriesEmptyState,
  "not-consultable-state": NotConsultableState,
  "entry-table": EntryTable,
};
