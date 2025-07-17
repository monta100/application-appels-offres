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
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // ⚠️ ce fichier est essentiel !


import 'bootstrap/dist/css/bootstrap.min.css';
// ⚙️ Créer instance Vue + plugins
const appInstance = createApp(App);

appInstance.use(store);
appInstance.use(router);
appInstance.use(SoftUIDashboard);

appInstance.mount("#app");
