import Vue from "vue";
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

Vue.component("edit-project", EditProject);
Vue.component("project-invites", ProjectInvites);
Vue.component("graph", Graph);
Vue.component("medtaggraph", MedTagGraph);
Vue.component("notification-center", NotificationCenter);
Vue.component("audio-player", AudioPlayer);
Vue.component("v-gravatar", Gravatar);
Vue.component("projects-list", ProjectsList);
Vue.component("toast", Toast);
Vue.component("modal_edit_entry", ModalEditEntry);
Vue.component("selected-case", SelectedCase);
Vue.component("create-project", createproject);
Vue.component("modal", Modal);
Vue.component("snackbar", Snackbar);
Vue.component("custom-dialogue", CustomDialogue);
Vue.component("cases-list", Caseslist);
