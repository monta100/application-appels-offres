// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'

// Tes composants de page (à adapter)
import Home from '@/components/Home.vue'
import list_appelle_offre from '@/components/Gestion_appelle_offres/list_appelle_offre.vue'
import AppelOffresList_Client from '@/components/Gestion_appelle_offres/AppelOffresList_Client.vue'
import DetailOffre from '@/components/Gestion_appelle_offres/DetailOffre.vue'
import soumettreProposition from '@/components/Gestion_Soumiison/soumettreProposition.vue'
import MesSoumissions from '@/components/Gestion_Soumiison/MesSoumissions.vue';
import SoumissionsAppel from '@/components/Gestion_Soumiison/SoumissionsAppel.vue'
import ListSoumissionChoisie from '@/components/Gestion_Soumiison/ListSoumissionChoisie.vue'
import Chat from '@/components/chat_enLigne/Chat.vue'
 const routes = [
  { path: '/', name: 'Home', component: Home },
  { path :'/appelles', name:'Appelle', component: list_appelle_offre},
  { path :'/offreCl', name:'OffreCl', component: AppelOffresList_Client},
  { path: '/offre/:id',name: 'DetailOffre', component: DetailOffre },


{path: '/soumettre/:idAppel',name: 'SoumettreProposition',component: soumettreProposition},
 
{ path :'/mes_soumission',name:'MesSoumissions',component : MesSoumissions},

{ path:'/appels/:idAppel/soumissions',name :'SoumissionsAppel',component : SoumissionsAppel},

{ path:'/Soumission_chosi' ,name :'ListSoumissionChoisie.vue',component :ListSoumissionChoisie},
{ path:'/chat',name:'Chat',component :Chat}
]


const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
