// main.js (ou main.ts si tu utilises TypeScript)

import { createApp } from "vue";

// 🔄 Composant racine
import App from "../Backoffice/App.vue";

// ✅ Store + Router
import store from "../Backoffice/store/index.js";
import router from "../Backoffice/router/index.js";

// ✅ Styles CSS nécessaires
import "../Backoffice/assets/css/nucleo-icons.css";
import "../Backoffice/assets/css/nucleo-svg.css";

// ✅ Plugin UI
import SoftUIDashboard from "../Backoffice/soft-ui-dashboard";
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap/dist/css/bootstrap.min.css';

// ✅ FontAwesome Configuration
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

// ✅ Icônes à utiliser
import {
  faClipboardList,
  faUser,
  faPaperPlane,
  faFileContract,
  faFileSignature,
  faUsers,
  faHandPointer,
  faCreditCard
} from "@fortawesome/free-solid-svg-icons";

// Ajoute les icônes dans la librairie
library.add(
  faClipboardList,
  faUser,
  faPaperPlane,
  faFileContract,
  faFileSignature,
  faUsers,
  faHandPointer,
  faCreditCard
);

// ⚙️ Créer instance Vue + plugins
const appInstance = createApp(App);

// 🔌 Utilise les plugins
appInstance.use(store);
appInstance.use(router);
appInstance.use(SoftUIDashboard);

// ✅ Déclare le composant global pour <font-awesome-icon>
appInstance.component("font-awesome-icon", FontAwesomeIcon);

// 🚀 Monte l'application
appInstance.mount("#app");
