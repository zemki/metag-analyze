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
import CustomDialogue from "./components/global/CustomDialogue.vue";
import DebugPanel from "./components/debug-panel.vue";
import Breadcrumb from "./components/breadcrumb.vue";
import Treemap from "./components/treemap.vue";
import PaginationControls from "./components/PaginationControls.vue";
import ProjectCasesView from "./components/ProjectCasesView.vue";
import EmailChangeModal from "./components/EmailChangeModal.vue";

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
  "custom-dialogue": CustomDialogue,
  "debug-panel": DebugPanel,
  "breadcrumb": Breadcrumb,
  "treemap": Treemap,
  "pagination-controls": PaginationControls,
  "project-cases-view": ProjectCasesView,
  "email-change-modal": EmailChangeModal
};
