// Pas besoin de router du tout
import { createApp } from 'vue'
import App from './App.vue'
import store from './store'
import router from './router'

// main.js



const app = createApp(App)
app.use(store)
app.use(router)
app.mount('#app')
