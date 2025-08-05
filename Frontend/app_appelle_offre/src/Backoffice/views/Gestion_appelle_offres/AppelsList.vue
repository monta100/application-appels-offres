<template>
  <div class="container mt-4">
    <h3 class="text-center text-primary mb-4">Liste des Appels d’Offres</h3>

    <!-- Filtres -->
    <div class="row g-2 align-items-end mb-3">
      <div class="col-md-3">
        <label class="form-label fw-semibold"><i class="bi bi-folder"></i> Domaine</label>
        <select class="form-select rounded-pill" v-model="filters.domaine">
          <option value="">Tous les domaines</option>
          <option v-for="dom in domaines" :key="dom.id" :value="dom.nom">{{ dom.nom }}</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold"><i class="bi bi-calendar2"></i> Date début min</label>
        <input type="date" class="form-control rounded-pill" v-model="filters.date_debut_min" />
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold"><i class="bi bi-calendar2"></i> Date fin max</label>
        <input type="date" class="form-control rounded-pill" v-model="filters.date_fin_max" />
      </div>
      <div class="col-md-3 d-flex justify-content-end gap-2">
        <button class="btn btn-outline-secondary rounded-pill btn-sm" @click="resetFilters">
          <i class="bi bi-arrow-clockwise"></i> Réinitialiser
        </button>
        <button class="btn btn-primary rounded-pill btn-sm" @click="toggleSort">
          <i class="bi bi-arrow-down-up"></i> Trier par date de début
        </button>
      </div>
    </div>

    <!-- Tableau -->
    <table class="table table-striped table-bordered shadow-sm">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Titre</th>
          <th>Description</th>
          <th>Date Début</th>
          <th>Date Fin</th>
          <th>Domaine</th>
          <th>Création</th>
          <th>Publié Par</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(appel, index) in paginatedAppels" :key="appel.id">
          <td>{{ (page - 1) * perPage + index + 1 }}</td>
          <td class="fw-semibold">{{ appel.titre }}</td>
          <td>{{ appel.description }}</td>
          <td>{{ formatDate(appel.date_debut) }}</td>
          <td>{{ formatDate(appel.date_fin) }}</td>
          <td><span class="badge bg-info text-dark rounded-pill">{{ appel.domaine.nom }}</span></td>
          <td>{{ formatDate(appel.created_at) || '---' }}</td>
          <td>{{ appel.user.nom }} {{ appel.user.prenom }}</td>
          <td>
            <span class="badge" :class="appel.statut === 'cloturé' ? 'bg-danger' : 'bg-success'">
              {{ appel.statut.toUpperCase() }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center">
      <span class="text-muted">Page {{ page }} / {{ totalPages }}</span>
      <div class="btn-group">
        <button class="btn btn-outline-primary btn-sm" :disabled="page === 1" @click="page--">
          ⬅️ Précédent
        </button>
        <button class="btn btn-outline-primary btn-sm" :disabled="page === totalPages" @click="page++">
          Suivant ➡️
        </button>
      </div>
    </div>
  </div>

  <appels-bar-chart :data="appelsChartData" />
  <appels-pie-chart :data="repartitionData" />

</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/Http/api';
import AppelsBarChart from './AppelsBarChart.vue';
import AppelsPieChart from './AppelsPieChart.vue';
const appels = ref([]);
const domaines = ref([]);
const appelsChartData = ref([0, 0, 0, 0, 0, 0]);

const filters = ref({ domaine: '', date_debut_min: '', date_fin_max: '' });
const page = ref(1);
const perPage = 5;
const sortAsc = ref(true);
const repartitionData = ref([]);

onMounted(async () => {
  try {
    // Appels d'offres
    const resAppels = await api.get('/appels');
    appels.value = resAppels.data;
    domaines.value = [...new Set(resAppels.data.map(a => a.domaine))].map(d => d);

    // Données pour le graphique
    const resChart = await api.get('/dashboard/appels-semaine');
    appelsChartData.value = resChart.data.appels_par_semaine;

    console.log('✅ données graphiques :', appelsChartData.value);
  } catch (error) {
    console.error('Erreur lors du chargement des données :', error);
  }

  const res = await api.get('/dashboard/appels-par-domaine');
  repartitionData.value = res.data;
});

function resetFilters() {
  filters.value = { domaine: '', date_debut_min: '', date_fin_max: '' };
  page.value = 1;
}

function toggleSort() {
  sortAsc.value = !sortAsc.value;
}

function formatDate(date) {
  if (!date) return '';
  return new Date(date).toLocaleDateString('fr-FR');
}

const filteredAppels = computed(() => {
  return appels.value
    .filter(appel => {
      const matchDomaine = !filters.value.domaine || appel.domaine.nom === filters.value.domaine;
      const matchDateDebut = !filters.value.date_debut_min || appel.date_debut >= filters.value.date_debut_min;
      const matchDateFin = !filters.value.date_fin_max || appel.date_fin <= filters.value.date_fin_max;
      return matchDomaine && matchDateDebut && matchDateFin;
    })
    .sort((a, b) => {
      const da = new Date(a.date_debut), db = new Date(b.date_debut);
      return sortAsc.value ? da - db : db - da;
    });
});

const totalPages = computed(() => Math.ceil(filteredAppels.value.length / perPage));
const paginatedAppels = computed(() => filteredAppels.value.slice((page.value - 1) * perPage, page.value * perPage));
</script>

<style scoped>
.badge {
  font-size: 0.8rem;
  padding: 0.45em 0.8em;
}
</style>
