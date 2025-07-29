<template>
  <Navbar />

  <div class="container py-5 min-vh-100">
    <h2 class="mb-4 fw-bold text-orange">📋 Soumissions pour : {{ appelTitre }}</h2>

    <div v-if="soumissions.length > 0">
      <div class="table-responsive shadow rounded">
 <table class="table table-bordered align-middle mb-0">
  <thead class="table-light text-center">
    <tr>
      <th>#</th>
      <th><i class="fas fa-user"></i> Soumissionnaire</th>
      <th><i class="fas fa-money-bill-wave"></i> Prix</th>
      <th><i class="fas fa-clock"></i> Délai</th>
      <th><i class="fas fa-align-left"></i> Description</th>
      <th><i class="fas fa-paperclip"></i> Fichier</th>
      <th><i class="fas fa-brain"></i> Score IA</th>
      <th><i class="fas fa-check-circle"></i> Choix</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(s, index) in soumissions" :key="s.idSoumission">
      <td class="text-center fw-semibold">{{ index + 1 }}</td>
      <td>{{ s.user?.nom || '—' }}</td>
      <td>{{ s.prixPropose }} TND</td>
      <td>{{ s.temps_realisation }} j</td>
      <td>{{ s.description }}</td>
      <td class="text-center">
        <a
          v-if="s.fichier_joint"
          :href="`http://localhost:8000/storage/${s.fichier_joint}`"
          target="_blank"
          class="btn btn-sm btn-orange"
        >
          📎 Voir
        </a>
      </td>
      <td class="text-center">
        <div v-if="s.score_ia">
          <span class="badge bg-success">{{ s.score_ia.toFixed(1) }} / 100</span>
        </div>
        <div v-else>
          <button
            class="btn btn-warning btn-sm"
            @click="evaluerSoumission(s)"
          >
            🎯 Évaluer
          </button>
        </div>
      </td>
      <td class="text-center">
        <button
          class="btn btn-success btn-sm"
          @click="choisirSoumission(s.idSoumission)"
          :disabled="soumissionChoisie !== null"
        >
          ✅ Choisir
        </button>
      </td>
    </tr>
  </tbody>
</table>

      </div>
    </div>

    <div v-else class="text-muted mt-4">Aucune soumission pour cet appel.</div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/Http/api';
import Navbar from '../Navbar.vue';
import Footer from '../Footer.vue';

const route = useRoute();
const soumissions = ref([]);
const appelTitre = ref('');
const soumissionChoisie = ref(null); // pour empêcher plusieurs choix

onMounted(async () => {
  const idAppel = route.params.idAppel;

  const res = await api.get(`/appels/${idAppel}`);
  appelTitre.value = res.data.titre;

  const soumRes = await api.get(`/appels/${idAppel}/soumissions`);
  soumissions.value = soumRes.data;
});

const choisirSoumission = async (idSoumission) => {
  try {
    await api.post(`/soumissions/${idSoumission}/choisir`);
    alert('Soumission choisie avec succès ! Un email a été envoyé.');

    soumissionChoisie.value = idSoumission;
  } catch (err) {
    console.error('Erreur lors de la sélection :', err);
    alert('Une erreur est survenue.');
  }
};



const evaluerSoumission = async (soumission) => {
  try {
    const response = await api.post(`/soumissions/${soumission.idSoumission}/scoring`);

    // Mettre à jour localement le score
    soumission.score_ia = response.data.score_ia;

    console.log('✅ Score IA mis à jour :', response.data.score_ia);
  } catch (err) {
    console.error('❌ Erreur lors de l’évaluation IA :', err.response?.data || err.message);
    alert('Erreur lors de l’évaluation IA.');
  }
};

</script>

<style scoped>
.text-orange {
  color: #ff6600;
}

.btn-orange {
  background-color: #ff6600;
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 40px;
  padding: 4px 14px;
  transition: background 0.3s ease, transform 0.2s ease;
}
.btn-orange:hover {
  background-color: #e25400;
  transform: translateY(-1px);
}

</style>
