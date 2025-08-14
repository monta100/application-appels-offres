<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <mini-statistics-card
          title="Appels d'offres publiés"
          :value="appelsCount"
          :percentage="{ value: appelsEvolution + '%', color: appelsEvolution > 0 ? 'text-success' : 'text-danger' }"
          :icon="{ component: faClipboardList, background: iconBackground }"
          direction-reverse
        />
      </div>
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <mini-statistics-card
          title="Utilisateurs inscrits"
          :value="usersCount"
          :percentage="{ value: usersEvolution + '%', color: usersEvolution > 0 ? 'text-success' : 'text-danger' }"
          :icon="{ component: faUser, background: iconBackground }"
          direction-reverse
        />
      </div>
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <mini-statistics-card
          title="Soumissions"
          :value="soumissionsCount"
          :percentage="{ value: soumissionsEvolution + '%', color: soumissionsEvolution > 0 ? 'text-success' : 'text-danger' }"
          :icon="{ component: faPaperPlane, background: iconBackground }"
          direction-reverse
        />
      </div>
      <div class="col-xl-3 col-sm-6 mb-xl-0">
        <mini-statistics-card
          title="Contrats générés"
          :value="contratsCount"
          :percentage="{ value: contratsEvolution + '%', color: contratsEvolution > 0 ? 'text-success' : 'text-danger' }"
          :icon="{ component: faFileContract, background: iconBackground }"
          direction-reverse
        />
      </div>
    </div>

    <div class="row mt-1">
      <div class="col-lg-8">
      <div>
  <reports-bar-chart
    v-if="!aucuneSoumission"
    id="chart-bar"
    title="Soumissions par semaine"
    description="📊 Dernières 6 semaines"
    :chart="{
      labels: ['S-5', 'S-4', 'S-3', 'S-2', 'S-1', 'Cette semaine'],
      datasets: {
        label: 'Soumissions',
        data: chartSoumissions,
      },
    }"
:items="[
  {
    icon: { color: 'info', component: faPaperPlane },
    label: 'Soumissions',
    progress: { content: soumissionsCount.toString(), percentage: 80 },
  },
  {
    icon: { color: 'primary', component: faUsers },
    label: 'Utilisateurs',
    progress: { content: usersCount.toString(), percentage: 60 },
  },
  {
    icon: { color: 'warning', component: faFileContract },
    label: 'Contrats',
    progress: { content: contratsCount.toString(), percentage: 45 },
  }
]"
  />

  <div v-else class="text-center text-muted mt-5">
    <h6>Aucune soumission enregistrée ces 6 dernières semaines.</h6>
    <p>📭 Essayez plus tard ou vérifiez les données.</p>
  </div>
</div>


      </div>

      <div class="col-lg-4">
        <timeline-list
          class="h-100"
          title="Historique récent"
          description="📈 <span class='font-weight-bold'>Activités</span> du semaine"
        >
          <timeline-item
            v-for="(activity, index) in recentActivities"
            :key="index"
            :color="getColorForType(activity.type)"
            :icon="getIconForType(activity.type)"
            :title="activity.title"
            :date-time="activity.datetime"
          />
        </timeline-list>
      </div>
    </div>
<div class="row no-margin-top">
  <div class="col-lg-6">
<IndiceActivite v-if="indiceActivite !== null" :score="indiceActivite" />
  </div>
</div>
  </div>



</template>

<script>
import MiniStatisticsCard from '@/Backoffice/examples/Cards/MiniStatisticsCard.vue';
import ReportsBarChart from '@/Backoffice/examples/Charts/ReportsBarChart.vue';
import TimelineList from './components/TimelineList.vue';
import TimelineItem from './components/TimelineItem.vue';
import { computed } from 'vue';
import IndiceActivite from './components/IndiceActivite.vue';
import {
  faClipboardList,
  faUser,
  faPaperPlane,
  faFileContract,
  faFileSignature,
  faUsers
} from '@fortawesome/free-solid-svg-icons';

import api from '@/Http/api';


export default {
  name: 'Dashboard',
  components: {
    MiniStatisticsCard,
    ReportsBarChart,
    TimelineList,
    TimelineItem,
    IndiceActivite,

  },
  data() {
    return {
      iconBackground: 'bg-gradient-orange',
      faClipboardList,
      faUser,
      faPaperPlane,
      faFileContract,
      faFileSignature,
      faUsers,
      appelsCount: 0,
      usersCount: 0,
      soumissionsCount: 0,
      contratsCount: 0,
      appelsEvolution: 0,
      usersEvolution: 0,
      soumissionsEvolution: 0,
      contratsEvolution: 0,
      chartSoumissions: [0, 0, 0, 0, 0, 0],
      recentActivities: [],
      chartLabels: [],
indiceActivite: null, // au lieu de 0

    };
  },



  computed: {
  aucuneSoumission() {
    return this.chartSoumissions.every(val => val === 0);
  }
},
  async created() {
    // ✅ Bloc 1 : stats principales
    try {
      const response = await api.get('/dashboard-stats');
      const data = response.data;

      this.appelsCount = data.appels_count;
      this.usersCount = data.users_count;
      this.soumissionsCount = data.soumissions_count;
      this.contratsCount = data.contrats_count;

      const evolution = (current, previous) => {
        if (previous === 0) return current > 0 ? 100 : 0;
        return Math.round(((current - previous) / previous) * 100);
      };

      this.appelsEvolution = evolution(data.appels_this_week, data.appels_last_week);
      this.usersEvolution = evolution(data.users_this_week, data.users_last_week);
      this.soumissionsEvolution = evolution(data.soumissions_this_week, data.soumissions_last_week);
      this.contratsEvolution = evolution(data.contrats_this_week, data.contrats_last_week);
    } catch (error) {
      console.error('Erreur de chargement des stats :', error);
    }

    // ✅ Bloc 2 : graphique des soumissions par semaine
  try {
  const semainesRes = await api.get('/dashboard/soumissions-semaine');
  console.log('🔍 Données de /soumissions-semaine :', semainesRes.data.soumissions_par_semaine);
  this.chartSoumissions = semainesRes.data.soumissions_par_semaine.slice(-6);
} catch (error) {
  console.error('Erreur de chargement des soumissions par semaine :', error);
}


    // ✅ Bloc 3 : historique des activités
    try {
      const activitiesRes = await api.get('/dashboard-activites');
      this.recentActivities = activitiesRes.data;
    } catch (error) {
      console.error('Erreur de chargement des activités :', error);
    }
try {
  const indiceRes = await api.get('/dashboard/indice-activite');
  this.indiceActivite = Number(indiceRes.data.indice) || 0; // ✅ Correction ici
} catch (error) {
  console.error('Erreur indice activité :', error);
  this.indiceActivite = 0;
}


  },
  methods: {
    getIconForType(type) {
      switch (type) {
        case 'contrat': return this.faFileSignature;
        case 'soumission': return this.faPaperPlane;
        case 'appel': return this.faClipboardList;
        default: return this.faClipboardList;
      }
    },
    getColorForType(type) {
      switch (type) {
        case 'contrat': return 'success';
        case 'soumission': return 'primary';
        case 'appel': return 'info';
        default: return 'secondary';
      }
    }
  }
};
</script>

<style>
/* Réduit l'espace au-dessus de la section IndiceActivite */
.row.no-margin-top {
  margin-top: -10px !important; /* ou -40px selon besoin */
  padding-top: 0 !important;
}
.col-lg-4 {
  margin-bottom: 0 !important;
}


</style>

