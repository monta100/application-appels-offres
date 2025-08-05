<template>
  <div class="card shadow-sm p-4 mb-4">
    <h4 class="mb-3 text-center text-primary">👥 Statistiques des Utilisateurs</h4>

    <div class="row">
      <!-- Prestataires actifs -->
      <div class="col-md-4">
        <h6 class="text-success">🥇 Prestataires les plus actifs</h6>
        <ul class="list-group small">
          <li v-for="user in stats.prestatairesActifs" :key="user.idUser" class="list-group-item d-flex justify-content-between">
            <span>{{ user.nom }} {{ user.prenom }}</span>
            <span class="badge bg-success">{{ user.total_soumissions }}</span>
          </li>
        </ul>
      </div>

      <!-- Représentants les plus actifs -->
      <div class="col-md-4">
        <h6 class="text-warning">📢 Représentants les plus actifs</h6>
        <ul class="list-group small">
          <li v-for="user in stats.representantsActifs" :key="user.idUser" class="list-group-item d-flex justify-content-between">
            <span>{{ user.nom }} {{ user.prenom }}</span>
            <span class="badge bg-warning text-dark">{{ user.total_appels }}</span>
          </li>
        </ul>
      </div>

      <!-- Prestataires choisis -->
      <div class="col-md-4">
        <h6 class="text-danger">✅ Prestataires les plus choisis</h6>
        <ul class="list-group small">
          <li v-for="user in stats.prestatairesChoisis" :key="user.idUser" class="list-group-item d-flex justify-content-between">
            <span>{{ user.nom }} {{ user.prenom }}</span>
            <span class="badge bg-danger">{{ user.total_choix }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '@/Http/api';

const stats = ref({
  prestatairesActifs: [],
  representantsActifs: [],
  prestatairesChoisis: []
});

const fetchStats = async () => {
  try {
    const res = await api.get('/dashboard/top-users');
    stats.value = res.data;
  } catch (error) {
    console.error('Erreur lors du chargement des stats :', error);
  }
};

onMounted(fetchStats);
</script>
