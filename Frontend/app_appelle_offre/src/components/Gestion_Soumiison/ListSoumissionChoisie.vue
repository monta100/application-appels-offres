<template>
  <Navbar />

  <div class="container my-5">
    <h2 class="text-center mb-4 text-orange fw-bold">
      📝 Mes Soumissions Choisies
    </h2>
<div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
  <input
    v-model="filtreTitre"
    type="text"
    class="form-control"
    placeholder="🔎 Rechercher un appel d'offre"
    style="max-width: 250px;"
  />

</div>

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
<tr v-for="(soumission, index) in soumissionsFiltrees" :key="soumission.idSoumission">
    <td>{{ index + 1 }}</td>
    <td>{{ soumission.appel_offre?.titre }}</td>
    <td>{{ soumission.appel_offre?.budget }} TND</td>
    <td>{{ soumission.user?.nom }} ({{ soumission.user?.email }})</td>

    <!-- ✅ Contrat -->
    <td>
      <span v-if="soumission.contrat" class="badge bg-success text-white d-flex align-items-center gap-1 px-2 py-1 rounded-pill">
        <i class="fas fa-file-signature"></i> Généré
      </span>
      <span v-else class="badge bg-danger text-white d-flex align-items-center gap-1 px-2 py-1 rounded-pill">
        <i class="fas fa-times-circle"></i> Non généré
      </span>
    </td>

    <!-- ✅ Action -->
    <td class="d-flex flex-wrap gap-2">
      <button
        v-if="!soumission.contrat"
        class="btn btn-sm btn-warning"
        @click="genererContrat(soumission.idSoumission)"
      >
        <i class="fas fa-file-alt"></i> Générer Contrat
      </button>

      <a
        v-if="soumission?.contrat?.fichier_pdf"
        class="btn btn-sm"
        :href="`http://localhost:8000/storage/${soumission.contrat.fichier_pdf}`"
        target="_blank"
        style="background-color: #FFA726; color: white; border: none;"
      >
        <i class="fas fa-eye"></i> Consulter le Contrat
      </a>
      
  <a
    v-if="soumission?.contrat?.fichier_pdf"
    class="btn btn-sm"
    :href="`http://localhost:8000/storage/${soumission.contrat.fichier_pdf}`"
    :download="`contrat_${soumission.idSoumission}.pdf`"
    style="background-color: #4CAF50; color: white; border: none;"
  >
    📥 Télécharger
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
import { ref, onMounted,computed } from 'vue';
import axios from 'axios';
import Footer from '../Footer.vue';
import Navbar from '../Navbar.vue';

const soumissions = ref([]);
const alertMessage = ref('');
const alertType = ref('info'); // info | success | warning | danger
const baseURL = 'http://localhost:8000/api';
const filtreTitre = ref('');
const filtreStatut = ref('');
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

    const response = await axios.post(`${baseURL}/contrats/generer/${id}`, null, {
      headers: { Authorization: `Bearer ${token}` }
    });

    const pdfUrl = response.data.fichier_pdf;

    alertMessage.value = "✅ Contrat généré avec succès !";
    alertType.value = "success";

    // Optionnel : ouvrir le PDF dans un nouvel onglet
    if (pdfUrl) {
      window.open(pdfUrl, '_blank');
    }

    await fetchSoumissions(); // Rafraîchir la liste

  } catch (error) {
    if (error.response?.status === 409) {
      alertMessage.value = "⚠️ Un contrat a déjà été généré pour cette soumission.";
      alertType.value = "warning";
    } else {
      alertMessage.value = "❌ Erreur lors de la génération du contrat.";
      alertType.value = "danger";
    }
  }
};




const soumissionsFiltrees = computed(() => {
  return soumissions.value.filter((s) => {
    const titreOk = s.appel_offre?.titre.toLowerCase().includes(filtreTitre.value.toLowerCase());
    const statutOk =
      !filtreStatut.value ||
      (filtreStatut.value === 'genere' && s.contrat) ||
      (filtreStatut.value === 'non_genere' && !s.contrat);

    return titreOk && statutOk;
  });
});


onMounted(fetchSoumissions);
</script>

<style scoped>
.text-orange {
  color: #ff6600;
}
</style>
