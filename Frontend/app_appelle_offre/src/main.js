// Pas besoin de router du tout
import { createApp } from 'vue'
import App from './App.vue'
import store from './store'
import router from './router'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap/dist/css/bootstrap.min.css'
import api from './Http/api';


const token = localStorage.getItem('token');

if (token) {
  api.get('/user')
    .then(res => {
      store.commit('auth/setUser', res.data);
    })
    .catch(() => {
      localStorage.removeItem('token');
    });
}

// main.js



const app = createApp(App)
app.use(store)
app.use(router)
app.mount('#app')
