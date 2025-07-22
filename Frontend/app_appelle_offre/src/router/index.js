// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'

// Tes composants de page (à adapter)
import Home from '@/components/Home.vue'

 const routes = [
  { path: '/', name: 'Home', component: Home },
  // autres routes...
]


const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
