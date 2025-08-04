<template>
  <Navbar />

  <div class="container py-5 min-vh-100">
    <div v-if="appel" class="mx-auto card-detail p-5 shadow-lg">
      <!-- Titre + Statut -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="fw-bold text-orange mb-0">{{ appel.titre }}</h1>
        <span class="badge fs-6 py-2 px-3" :class="getStatutClass(appel.statut)">
          {{ appel.statut.toUpperCase() }}
        </span>
      </div>

      <!-- Description -->
      <p class="text-muted mb-4 fs-6">{{ appel.description }}</p>

      <!-- Infos et Progress bar -->
      <div class="row">
        <div class="col-md-8">
          <ul class="list-unstyled fs-6">
            <li class="mb-2">
              <i class="fas fa-calendar-alt me-2 text-orange"></i>
              <strong>Début :</strong> {{ formatDate(appel.date_debut) }}
            </li>
            <li class="mb-2">
              <i class="fas fa-calendar-check me-2 text-orange"></i>
              <strong>Fin :</strong> {{ formatDate(appel.date_fin) }}
            </li>
            <li class="mb-2">
              <i class="fas fa-layer-group me-2 text-orange"></i>
              <strong>Domaine :</strong> {{ appel.domaine?.nom || '—' }}
            </li>
            <li class="mb-2">
              <i class="fas fa-money-bill-wave me-2 text-orange"></i>
              <strong>Budget :</strong> {{ formatBudget(appel.budget) }} TND
            </li>
            <li class="mb-2">
              <i class="fas fa-user me-2 text-orange"></i>
              <strong>Publié par :</strong> {{ appel.user?.nomSociete || '—' }}
            </li>
            <li class="mb-2" v-if="appel.user?.email">
              <i class="fas fa-envelope me-2 text-orange"></i>
              <strong>Email :</strong> {{ appel.user.email }}
            </li>
          </ul>
        </div>
        <div class="col-md-4">
          <p>
            ⏳ <strong>Temps restant avant soumission :</strong>
            <span v-if="timeRemaining > 0">{{ timeRemaining }} jours</span>
            <span v-else class="text-danger">Expiré</span>
          </p>
          <div class="progress" style="height: 20px;">
            <div
              class="progress-bar"
              :class="timeRemaining === 0 ? 'bg-danger' : 'bg-warning'"
              :style="{ width: progressPercentage + '%' }"
            >
              {{ progressPercentage }}%
            </div>
          </div>
        </div>
      </div>

      <!-- Bouton Contact -->
      <div class="mt-4 d-flex gap-3 flex-wrap">
        

        <router-link to="/offreCl" class="btn btn-outline-secondary">
          ⬅️ Retour à la liste
        </router-link>
<router-link
  :to="`/soumettre/${appel.idAppel}`"
  class="btn btn-orange"
  v-if="!hasSubmitted && timeRemaining > 0"
>
  ✍️ Soumettre une proposition
</router-link>

<span class="text-danger fst-italic" v-else-if="timeRemaining === 0">
  ⛔ Le délai de soumission est expiré. Vous ne pouvez plus participer.
</span>

<span class="text-muted fst-italic" v-else>
  ✅ Vous avez déjà soumis une proposition pour cet appel.
</span>

      </div>
    </div>

    <!-- Loading -->
    <div v-else class="text-center py-5">
      <div class="spinner-border text-orange" role="status"></div>
      <p class="mt-3">Chargement de l'offre...</p>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/Http/api';
import Footer from '../Footer.vue';
import Navbar from '../Navbar.vue';

const route = useRoute();
const appel = ref(null);
const hasSubmitted = ref(false);


onMounted(async () => {
  const id = route.params.id;

  try {
    const res = await api.get(`/appels/${id}`);
    appel.value = res.data;

    // Vérifie si user a déjà soumis une proposition
    const check = await api.get(`/soumissions/verifier/${id}`);
    hasSubmitted.value = check.data.exists;
  } catch (err) {
    console.error('Erreur de récupération:', err);
  }
});


const formatDate = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR');
};

const formatBudget = (value) => {
  return parseFloat(value).toLocaleString('fr-TN', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};

const getStatutClass = (statut) => {
  return statut === 'cloturé' ? 'bg-danger text-white' :
         statut === 'brouillon' ? 'bg-secondary text-white' :
         'bg-success text-white';
};

const getOutlookLink = (email, sujet) => {
  const subject = encodeURIComponent(`Demande concernant : ${sujet}`);
  const body = encodeURIComponent(`Bonjour,\n\nJe suis intéressé par votre appel d'offre "${sujet}".\nMerci de me fournir plus de détails.\n\nCordialement.`);
  return `https://outlook.live.com/mail/0/deeplink/compose?to=${email}&subject=${subject}&body=${body}`;
};

const progressPercentage = computed(() => {
  if (!appel.value) return 0;
  const createdAt = new Date(appel.value.created_at);
  const dateDebut = new Date(appel.value.date_debut);
  const deadline = new Date(dateDebut);
  deadline.setDate(deadline.getDate() - 4);

  const total = deadline - createdAt;
  const elapsed = new Date() - createdAt;
  if (new Date() > deadline) return 100;
  return Math.min(100, Math.floor((elapsed / total) * 100));
});

const timeRemaining = computed(() => {
  if (!appel.value) return 0;
  const dateDebut = new Date(appel.value.date_debut);
  const deadline = new Date(dateDebut);
  deadline.setDate(deadline.getDate() - 4);
  const remaining = deadline - new Date();
  return Math.max(0, Math.floor(remaining / (1000 * 60 * 60 * 24)));
});
</script>

<style scoped>
.card-detail {
  max-width: 800px;
  border-radius: 16px;
  background-color: white;
}

.text-orange {
  color: #ff6600;
}

.progress {
  background-color: #eee;
  border-radius: 10px;
}

.progress-bar {
  font-weight: bold;
  transition: width 0.6s ease;
}

.btn-orange {
  background-color: #ff6600;
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 40px;
  padding: 0.6rem 1.2rem;
  transition: background 0.3s ease, transform 0.2s ease;
}
.btn-orange:hover {
  background-color: #e25400;
  transform: translateY(-1px);
}
.btn-orange:disabled {
  background-color: #ddd;
  color: #999;
  cursor: not-allowed;
}

</style>
