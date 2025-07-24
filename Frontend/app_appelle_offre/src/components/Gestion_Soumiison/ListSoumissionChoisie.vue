<template>
  <Navbar />

  <div class="container my-5">
    <h2 class="text-center mb-4 text-orange fw-bold">
      📝 Mes Soumissions Choisies
    </h2>

    <div v-if="soumissions.length === 0" class="alert alert-info text-center">
      Aucune soumission choisie pour le moment.
    </div>

    <table v-else class="table table-bordered table-hover table-striped">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Appel d'offre</th>
          <th>Budget</th>
          <th>Prestataire</th>
          <th>Contrat</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(soumission, index) in soumissions" :key="soumission.idSoumission">
          <td>{{ index + 1 }}</td>
          <td>{{ soumission.appel_offre?.titre }}</td>
          <td>{{ soumission.appel_offre?.budget }} TND</td>
          <td>{{ soumission.user?.nom }} ({{ soumission.user?.email }})</td>
          <td>
            <span v-if="soumission.contrat">✅ Généré</span>
            <span v-else>❌ Non généré</span>
          </td>
          <td>
            <button
              v-if="!soumission.contrat"
              class="btn btn-sm btn-warning"
              @click="genererContrat(soumission.idSoumission)"
            >
              Générer Contrat
            </button>
            <a
              v-else
              class="btn btn-sm btn-success"
              :href="`${baseURL}/contrat/generer/${soumission.idSoumission}`"
              target="_blank"
            >
              📄 Voir Contrat
            </a>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- ✅ Zone d’alerte -->
    <div v-if="alertMessage" class="mt-4">
      <div :class="`alert alert-${alertType}`" role="alert">
        {{ alertMessage }}
      </div>
    </div>
  </div>
    <Footer />

</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Footer from '../Footer.vue';
import Navbar from '../Navbar.vue';
const soumissions = ref([]);
const alertMessage = ref('');
const alertType = ref('info'); // info | success | warning | danger
const baseURL = 'http://localhost:8000/api';

const fetchSoumissions = async () => {
  try {
    const token = localStorage.getItem('token');
    const res = await axios.get(`${baseURL}/soumissions/choisies`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    soumissions.value = res.data;
  } catch (error) {
    alertMessage.value = "Erreur lors du chargement des soumissions choisies.";
    alertType.value = "danger";
  }
};

const genererContrat = async (id) => {
  try {
    const token = localStorage.getItem('token');
    await axios.post(`${baseURL}/soumissions/${id}/generer-contrat`, null, {
      headers: { Authorization: `Bearer ${token}` }
    });
    alertMessage.value = "Contrat généré avec succès !";
    alertType.value = "success";
    await fetchSoumissions(); // Refresh
  } catch (error) {
    if (error.response?.status === 409) {
      alertMessage.value = "Contrat déjà généré pour cette soumission.";
      alertType.value = "warning";
    } else {
      alertMessage.value = "Erreur lors de la génération du contrat.";
      alertType.value = "danger";
    }
  }
};

onMounted(fetchSoumissions);
</script>

<style scoped>
.text-orange {
  color: #ff6600;
}
</style>
