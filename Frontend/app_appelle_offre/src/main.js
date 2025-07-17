import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import store from './store'; // <-- importer ici

createApp(App).mount('#app')
app.use(store); // <-- utiliser ici
