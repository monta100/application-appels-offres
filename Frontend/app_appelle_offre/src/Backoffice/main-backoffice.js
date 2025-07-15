import { createApp } from "vue";

// 🔄 Corriger le chemin vers App.vue (dans Backoffice)
import App from "../Backoffice/App.vue"

// ✅ Importer le store et router de Backoffice
import store from "../Backoffice/store/index.js";
import router from "../Backoffice/router/index.js";

// ✅ Importer les assets CSS du dashboard
import "../Backoffice/assets/css/nucleo-icons.css";
import "../Backoffice/assets/css/nucleo-svg.css";

// ✅ Importer le plugin Soft UI Dashboard
import SoftUIDashboard from "../Backoffice/soft-ui-dashboard";

const appInstance = createApp(App);

appInstance.use(store);
appInstance.use(router);
appInstance.use(SoftUIDashboard);

appInstance.mount("#app");
