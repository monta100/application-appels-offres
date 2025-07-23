// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'

// Tes composants de page (à adapter)
import Home from '@/components/Home.vue'
import list_appelle_offre from '@/components/Gestion_appelle_offres/list_appelle_offre.vue'

 const routes = [
  { path: '/', name: 'Home', component: Home },
  { path :'/appelles', name:'Appelle', component: list_appelle_offre},
  // autres routes...
]


const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
