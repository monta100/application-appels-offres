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
import Assistant from '@/components/chatboot/Assistant.vue'
import Profile from '@/components/Users/Profile.vue'
 const routes = [
  { path: '/', name: 'Home', component: Home },
  { path :'/appelles', name:'Appelle', component: list_appelle_offre,meta: { role: 'representant' }},
  { path :'/offreCl', name:'OffreCl', component: AppelOffresList_Client,meta: { role: 'participant' }},
  { path: '/offre/:id',name: 'DetailOffre', component: DetailOffre,meta: { role: 'participant' } },


{path: '/soumettre/:idAppel',name: 'SoumettreProposition',component: soumettreProposition,meta: { role: 'participant' }},
 
{ path :'/mes_soumission',name:'MesSoumissions',component : MesSoumissions,meta: { role: 'participant' }},

{ path:'/appels/:idAppel/soumissions',name :'SoumissionsAppel',component : SoumissionsAppel,meta: { role: 'representant' }},

{ path:'/Soumission_chosi' ,name :'ListSoumissionChoisie.vue',component :ListSoumissionChoisie,meta: { role: 'representant' }},
{ path:'/chat',name:'Chat',component :Chat},
{ path:'/Assistant', name:'Assistant',component :Assistant},
{ path :'/Profil',name:'Profil',component:Profile}
]




const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem('user'));
  const userRole = user?.role;

  console.log(`🔒 Accès tentative vers : ${to.path} | Rôle requis : ${to.meta.role} | Rôle user :`, userRole);

  if (to.meta?.role) {
    if (userRole === to.meta.role) {
      next();
    } else {
      alert("⛔️ Accès non autorisé pour ce rôle.");
      next('/');
    }
  } else {
    next();
  }
});



export default router
