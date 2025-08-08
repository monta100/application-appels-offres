<template>
  <Navbar />


  
  <div class="container py-5 min-vh-100">

<!-- Titre cliquable -->
<h5
  class="mb-3 fw-bold d-flex align-items-center"
  style="cursor: pointer; color: #f97316;"
  @click="showIAHelp = !showIAHelp"
>
  <i class="fas fa-brain me-2"></i> <!-- Icône cerveau -->
  Comprendre les analyses IA
  <i :class="showIAHelp ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="ms-2 text-dark"></i>
</h5>

<!-- Bloc explicatif affiché/masqué dynamiquement -->
<div
  class="alert alert-info d-flex align-items-start gap-3 rounded-3 shadow-sm mb-4"
  v-if="showIAHelp"
>
  <div>
    <p class="mb-2">
      💡 <strong>Score IA</strong> : C’est une <strong>note globale</strong> (sur 100) calculée à partir de trois dimensions pondérées : la similarité avec l’appel d’offre, l’adéquation du prix, et le délai estimé. Il s’agit d’un indicateur de <strong>qualité générale</strong>, utile pour comparer les soumissions entre elles.
    </p>
    <p class="mb-2">
      🛡️ <strong>Détection d'anomalie IA</strong> : Il s'agit d'une <strong>analyse complémentaire</strong>, focalisée sur l’identification d’<strong>écarts inhabituels</strong> ou incohérences. Même une soumission avec un bon score IA peut être jugée « suspecte » si un seul critère est fortement hors norme (par exemple : un texte totalement hors sujet, ou un délai irréaliste).
    </p>
    <p class="mb-0 fst-italic text-muted">
      ✨ Ces deux analyses sont donc <strong>distinctes</strong> et <strong>complémentaires</strong> : l’une donne un score de qualité, l’autre déclenche une alerte de vigilance.
    </p>
  </div>
</div>

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
      <th><i class="fas fa-shield-alt"></i> Anomalie IA</th>
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
  <div v-if="s.verdict_ia_anomalie">
    <span
      class="badge text-white"
      :class="s.verdict_ia_anomalie === 'Soumission suspecte' ? 'bg-danger' : 'bg-success'"
    >
      {{ s.verdict_ia_anomalie }}
    </span>
    <br />
    <button class="btn btn-link p-0 mt-1" @click="showDetails(s)">
      🔍 Détails
    </button>
  </div>
  <div v-else>
    <button class="btn btn-outline-dark btn-sm" @click="detecterAnomalie(s)">
      🧠 Vérifier
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




<!-- Modal -->
<div class="modal fade" id="anomalieModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">
          <i class="fas fa-robot me-2"></i> Détails de l’analyse IA
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info d-flex align-items-start gap-2 p-3">
          <i class="fas fa-info-circle fa-lg mt-1 text-primary"></i>
          <div>
            <strong>Explication IA :</strong><br />
            <span>{{ modalData.explication }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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
const showIAHelp = ref(true); // ou false par défaut

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

const detecterAnomalie = async (soumission) => {
  try {
    const res = await api.post(`/soumissions/${soumission.idSoumission}/detecter-anomalie`);
    const data = res.data.data;

    // Mise à jour locale
    soumission.verdict_ia_anomalie = data.verdict;
    soumission.explication_anomalie = data.explication;
    soumission.score_ia_anomalie = data.score_total;

    alert('✅ Analyse IA effectuée avec succès.');
  } catch (err) {
    console.error('Erreur IA :', err);
    alert('❌ Erreur lors de l’analyse IA.');
  }



};

const modalData = ref({
  similarite: 0,
  prix: 0,
  delai: 0,
  explication: ''
});

function showDetails(s) {
  // Assure-toi que ces propriétés existent dans l'objet s

  modalData.value.explication = s.explication_anomalie || 'Pas d’explication';

  const modal = new bootstrap.Modal(document.getElementById('anomalieModal'));
  modal.show();
}

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

.badge.bg-danger {
  background-color: #dc3545;
  color: white;
  font-weight: 600;
  padding: 5px 10px;
  border-radius: 10px;
}


</style>
