import Gravatar from "vue-gravatar";
import EditProject from "./components/editproject.vue";
import ProjectInvites from "./components/projectsInvites.vue";
import Graph from "./components/graph.vue";
import MedTagGraph from "./components/groupedentries.vue";
import NotificationCenter from "./components/notificationcenter.vue";
import AudioPlayer from "./components/audioplayer.vue";
import ProjectsList from "./components/projects-list.vue";
import Toast from "./components/toast.vue";
import ModalEditEntry from "./components/modal_edit_entry.vue";
import SelectedCase from "./components/selected-case.vue";
import createproject from "./components/createproject.vue";
import Modal from "./components/global/modal.vue";
import Snackbar from "./components/global/snackbar.vue";
import CustomDialogue from "./components/global/CustomDialogue.vue";
import Caseslist from "./components/caseslist.vue";
import DebugPanel from "./components/debug-panel.vue";
import Breadcrumb from "./components/breadcrumb.vue";
import Treemap from "./components/treemap.vue";
import CasesListWithPagination from "./components/CasesListWithPagination.vue";
import CaseCard from "./components/CaseCard.vue";
import PaginationControls from "./components/PaginationControls.vue";
import ProjectCasesView from "./components/ProjectCasesView.vue";

// Create a named export for all components to be used with app.component() in app.js
export const components = {
  "edit-project": EditProject,
  "project-invites": ProjectInvites,
  "graph": Graph,
  "medtaggraph": MedTagGraph,
  "notification-center": NotificationCenter,
  "audio-player": AudioPlayer,
  "v-gravatar": Gravatar,
  "projects-list": ProjectsList,
  "toast": Toast,
  "modal_edit_entry": ModalEditEntry,
  "selected-case": SelectedCase,
  "create-project": createproject,
  "modal": Modal,
  "snackbar": Snackbar,
  "custom-dialogue": CustomDialogue,
  "cases-list": Caseslist,
  "debug-panel": DebugPanel,
  "breadcrumb": Breadcrumb,
  "treemap": Treemap,
  "cases-list-with-pagination": CasesListWithPagination,
  "case-card": CaseCard,
  "pagination-controls": PaginationControls,
  "project-cases-view": ProjectCasesView
};
