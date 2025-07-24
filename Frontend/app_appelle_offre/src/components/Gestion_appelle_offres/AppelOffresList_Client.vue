<template>
  <Navbar />

  <section class="py-5 bg-light min-vh-100 offres-section">
    <div class="container">
<div class="header-orange container mb-5">
  <h1 class="display-5 fw-bold text-dark position-relative">
    Appels d’offres disponibles
    <span class="orange-bar"></span>
  </h1>
  <p class="text-muted mt-2">Consultez les projets publiés par nos entreprises partenaires.</p>
</div>



      <div class="row g-4">
        <div
          v-for="appel in appels"
          :key="appel.idAppelleOffre"
          class="col-md-6 col-lg-4 d-flex"
        >
          <div class="card card-appel w-100 shadow-lg border-0">
            <!-- Statut -->
            <div class="badge-statut" :class="appel.statut === 'cloturé' ? 'bg-danger' : 'bg-success'">
              {{ appel.statut.toUpperCase() }}
            </div>

            <div class="card-body d-flex flex-column">
              <h5 class="fw-bold text-orange mb-2">
                {{ appel.titre }}
              </h5>
              <p class="text-muted small mb-3">{{ appel.description }}</p>

              <ul class="list-unstyled small mb-3 text-dark">
                <li><i class="fas fa-calendar-alt me-2 text-orange"></i><strong>Début :</strong> {{ formatDate(appel.date_debut) }}</li>
                <li><i class="fas fa-calendar-check me-2 text-orange"></i><strong>Fin :</strong> {{ formatDate(appel.date_fin) }}</li>
                <li><i class="fas fa-layer-group me-2 text-orange"></i><strong>Domaine :</strong> {{ appel.domaine?.nom }}</li>
                <li><i class="fas fa-user me-2 text-orange"></i><strong>Publié par :</strong> {{ appel.user?.nomSociete || '—' }}</li>
              </ul>

              <button
                class="btn btn-orange mt-auto"
                @click="participer(appel)"
                :disabled="appel.statut === 'brouillon'"
              >
add to favoris              </button>
              <router-link
  :to="`/offre/${appel.idAppel}`"
  class="btn btn-orange-light me-2"
>
  🔍 Voir les détails
</router-link>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <Footer />
</template>

<script setup>
import { ref, onMounted,computed } from 'vue';
import api from '@/Http/api';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import Navbar from '../Navbar.vue';
import Footer from '../Footer.vue';
const appels = ref([]);
const user = computed(() => store.state.auth.user);
const store = useStore();
const router = useRouter();
const fetchAppels = async () => {
  const res = await api.get('/appels');
  appels.value = res.data;
};

const formatDate = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR');
};

const participer = (appel) => {
  alert(`Participation à l'appel d'offre : ${appel.titre}`);
  // Redirection ou modal de confirmation possible
};

onMounted(async () => {
  // Appels disponibles
  await fetchAppels(); // ← () manquants !

  const token = localStorage.getItem('token');

  // Vérification du token et récupération de l'utilisateur
  if (!user.value && token) {
    try {
      const res = await api.get('/user');
      store.commit('auth/setUser', res.data);
    } catch (err) {
      localStorage.removeItem('token');
      router.push({ name: 'Sign In' });
      return;
    }
  } 
  // Si aucun utilisateur ni token
  else if (!user.value) {
    window.location.assign('/backoffice.html#/sign-in');
    return;
  }
});
</script>

<style scoped>
.card-appel {
  border-radius: 20px;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  backdrop-filter: blur(10px);
  position: relative;
}

.card-appel:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
}

.badge-statut {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  z-index: 1;
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

.text-orange {
  color: #ff6600;
}

.list-unstyled li {
  margin-bottom: 0.4rem;
}

.header-orange {
  padding-top: 2rem;
}
.orange-bar {
  display: block;
  width: 80px;
  height: 4px;
  background-color: #ff6600;
  margin-top: 10px;
}



</style>
