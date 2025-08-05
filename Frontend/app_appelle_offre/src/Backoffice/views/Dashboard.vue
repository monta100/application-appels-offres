<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <mini-statistics-card
          title="Appels d'offres publiés"
          value="128"
          :percentage="{ value: '+12%', color: 'text-success' }"
          :icon="{ component: faClipboardList, background: iconBackground }"
          direction-reverse
        />
      </div>
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <mini-statistics-card
          title="Utilisateurs inscrits"
          value="326"
          :percentage="{ value: '+5%', color: 'text-success' }"
          :icon="{ component: faUser, background: iconBackground }"
          direction-reverse
        />
      </div>
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <mini-statistics-card
          title="Soumissions"
          value="742"
          :percentage="{ value: '+18%', color: 'text-success' }"
          :icon="{ component: faPaperPlane, background: iconBackground }"
          direction-reverse
        />
      </div>
      <div class="col-xl-3 col-sm-6 mb-xl-0">
        <mini-statistics-card
          title="Contrats générés"
          value="51"
          :percentage="{ value: '+7%', color: 'text-success' }"
          :icon="{ component: faFileContract, background: iconBackground }"
          direction-reverse
        />
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-lg-8">
        <reports-bar-chart
          id="chart-bar"
          title="Activités de la plateforme"
          description="(<strong>+23%</strong>) que la semaine dernière"
          :chart="{
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: {
              label: 'Soumissions',
              data: [120, 150, 180, 220, 310, 400],
            },
          }"
          :items="[
            {
              icon: { color: 'primary', component: faUsers },
              label: 'Utilisateurs',
              progress: { content: '326', percentage: 60 },
            },
            {
              icon: { color: 'info', component: faPaperPlane },
              label: 'Soumissions',
              progress: { content: '742', percentage: 80 },
            },
            {
              icon: { color: 'warning', component: faFileContract },
              label: 'Contrats',
              progress: { content: '51', percentage: 45 },
            },
          ]"
        />
      </div>

      <div class="col-lg-4">
        <timeline-list
          class="h-100"
          title="Historique récent"
          description="📈 <span class='font-weight-bold'>Activités</span> du mois"
        >
          <timeline-item
            color="success"
            :icon="faFileSignature"
            title="Contrat signé avec TechVision"
            date-time="03 Août 10:00"
          />
          <timeline-item
            color="primary"
            :icon="faPaperPlane"
            title="Soumission par DevCorp"
            date-time="02 Août 15:30"
          />
          <timeline-item
            color="info"
            :icon="faClipboardList"
            title="Nouvel appel d'offre publié"
            date-time="01 Août 08:15"
          />
        </timeline-list>
      </div>
    </div>
  </div>
</template>

<script>
import MiniStatisticsCard from '@/Backoffice/examples/Cards/MiniStatisticsCard.vue';
import ReportsBarChart from '@/Backoffice/examples/Charts/ReportsBarChart.vue';
import TimelineList from './components/TimelineList.vue';
import TimelineItem from './components/TimelineItem.vue';

import {
  faClipboardList,
  faUser,
  faPaperPlane,
  faFileContract,
  faFileSignature,
  faUsers
} from '@fortawesome/free-solid-svg-icons';

export default {
  name: 'Dashboard',
  components: {
    MiniStatisticsCard,
    ReportsBarChart,
    TimelineList,
    TimelineItem
  },
  data() {
    return {
      iconBackground: 'bg-gradient-orange',
      faClipboardList,
      faUser,
      faPaperPlane,
      faFileContract,
      faFileSignature,
      faUsers
    };
  }
};
</script>
