<template>
  <Navbar />

  <section class="py-5 bg-light min-vh-100 offres-section">
    <div class="container">
      <!-- En‑tête -->
      <div class="header-orange container mb-4">
        <h1 class="display-5 fw-bold text-dark position-relative">
          Appels d’offres disponibles
          <span class="orange-bar"></span>
        </h1>
        <p class="text-muted mt-2">Consultez les projets publiés par nos entreprises partenaires.</p>
      </div>

      <!-- Filtres -->
      <div class="row g-3 align-items-start mb-4">
   <!-- Recherche -->
<div class="col-md-7">
<div class="search-and-filters">
  <!-- Recherche -->
  <div class="search-wrap">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input
      v-model.trim="search"
      type="text"
      class="search-input"
      placeholder="Rechercher par titre…"
    />
  </div>

  <!-- Filtres -->
  <div class="filters-inline">
    <span class="filter-label">Filtrer par statut :</span>
    <label class="pill" :class="{active: selectedStatuses.includes('publiee')}">
      <input type="checkbox" value="publiee" v-model="selectedStatuses" />
      <span class="dot dot-success"></span> Publiée
    </label>
    <label class="pill" :class="{active: selectedStatuses.includes('cloture')}">
      <input type="checkbox" value="cloture" v-model="selectedStatuses" />
      <span class="dot dot-danger"></span> Clôturé
    </label>
      <label class="pill" :class="{active: selectedStatuses.includes('participee')}">
    <input type="checkbox" value="participee" v-model="selectedStatuses" />
    <!-- petit point neutre -->
    <span class="dot" style="background:#6b7280"></span> Participée
  </label>
    <button class="btn-link-reset" @click="clearStatuses">Tout désélectionner</button>
  </div>
</div>

</div>
</div>


      <!-- Barre actions -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="text-muted">
          {{ filteredAppels.length }} résultat(s)
          <span v-if="!selectedStatuses.length" class="ms-2 small">(tous statuts)</span>
        </div>
       
      </div>

      <!-- Liste -->
      <div class="row g-4" v-if="filteredAppels.length">
        <div
          v-for="appel in filteredAppels"
          :key="appel.idAppel"
          class="col-md-6 col-lg-4 d-flex"
        >
          <div class="card card-appel w-100 shadow-lg border-0">
            <!-- Statut effectif -->
            <div
              class="badge-statut"
              :class="appel.statutEffectif === 'cloture' ? 'bg-danger' : 'bg-success'"
            >
              {{ appel.statutEffectif.toUpperCase() }}
            </div>

            <div class="card-body d-flex flex-column">
              <h5 class="fw-bold text-orange mb-2">{{ appel.titre }}</h5>
              <p class="text-muted small mb-3">{{ appel.description }}</p>

              <ul class="list-unstyled small mb-3 text-dark">
                <li><i class="fas fa-calendar-alt me-2 text-orange"></i><strong>Début :</strong> {{ formatDate(appel.date_debut) }}</li>
                <li><i class="fas fa-calendar-check me-2 text-orange"></i><strong>Fin :</strong> {{ formatDate(appel.date_fin) }}</li>
                <li><i class="fas fa-layer-group me-2 text-orange"></i><strong>Domaine :</strong> {{ appel.domaine?.nom }}</li>
                <li><i class="fas fa-user me-2 text-orange"></i><strong>Publié par :</strong> {{ appel.user?.nomSociete || '—' }}</li>
              </ul>

              <router-link
                :to="`/offre/${appel.idAppel}`"
                class="btn btn-orange-light me-2"
              >
                🔍 Voir les détails
              </router-link>
               <!-- 👇 Affiché uniquement si l'utilisateur a participé -->
               <button
                 v-if="isParticipated(appel)"
                class="btn btn-orange-light"
                @click="goToResults(appel.idAppel)"
                title="Voir l'analyse IA de vos soumissions"
                                                             >
                                  📊 Consulter résultats
                                               </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center text-muted py-5">
        <i class="fas fa-folder-open fa-2x mb-3"></i>
        <div>Aucun appel d’offre ne correspond à vos filtres.</div>
      </div>
    </div>
  </section>

  <Footer />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/Http/api';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import Navbar from '../Navbar.vue';
import Footer from '../Footer.vue';

// ---- state
const appels = ref([]);
const search = ref('');

// Cases cochées (multi-sélection). Vide = tous les statuts.
const selectedStatuses = ref([]); // ['publiee','cloture','participee'] par ex.

// ✅ NEW: ids des appels où l'utilisateur connecté a déposé une soumission
const participatedIds = ref(new Set()); // ex: Set([3,7,12])

const statusOptions = [
  { label: 'Publiée', value: 'publiee' },
  { label: 'Clôturé', value: 'cloture' },
  // (Pas besoin d'ajouter "Participée" ici si tu ne l'utilises pas pour l'affichage des labels)
];

const labelFromValue = (v) => statusOptions.find(o => o.value === v)?.label || v;

// ---- store/router
const store = useStore();
const router = useRouter();
const user = computed(() => store.state.auth.user);

// ---- helpers
const formatDate = (dateStr) => {
  const d = new Date(dateStr);
  return isNaN(d) ? '—' : d.toLocaleDateString('fr-FR');
};

const normalize = (s) =>
  (s || '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

const normalizeStatut = (s) => {
  const n = normalize(s);
  if (n.includes('clot')) return 'cloture';
  if (n.includes('publ')) return 'publiee';
  if (n.includes('brou')) return 'brouillon';
  return n || '';
};

// Clôture auto si date fin passée
const statutEffectifFrom = (appel) => {
  const today = new Date();
  const dateFin = new Date(appel.date_fin);
  if (dateFin < today && normalizeStatut(appel.statut) !== 'cloture') {
    return 'cloture';
  }
  return normalizeStatut(appel.statut);
};

// Fetch + pré-calcul du statut effectif (cohérence UI)
const fetchAppels = async () => {
  const res = await api.get('/appels');
  appels.value = res.data.map(a => ({
    ...a,
    statutEffectif: statutEffectifFrom(a),
  }));
};

// ✅ NEW: récupérer la liste des appels où le user connecté a participé
const fetchParticipations = async () => {
  try {
    // Attend un payload type: { success: true, data: [ { idAppel: ... }, ... ] }
    const res = await api.get('/me/appels-participes');
    const list = res.data?.data || res.data || [];
    participatedIds.value = new Set(list.map(a => a.idAppel));
  } catch (e) {
    console.error('fetchParticipations error:', e);
    participatedIds.value = new Set();
  }
};

// Filtrage local (titre + statuts cochés + participée)
const filteredAppels = computed(() => {
  const q = normalize(search.value);

  // On sépare le filtre "participee" des statuts
  const wantsParticipated = selectedStatuses.value.includes('participee');
  const wanted = selectedStatuses.value
    .filter(v => v !== 'participee')
    .map(normalizeStatut); // tableau des statuts cochés normalisés

  return appels.value.filter((a) => {
    const inTitle = normalize(a.titre).includes(q);
    const okStatut = wanted.length === 0 ? true : wanted.includes(a.statutEffectif);
    const okParticipation = wantsParticipated ? participatedIds.value.has(a.idAppel) : true;
    return inTitle && okStatut && okParticipation;
  });
});

// Actions
const resetFilters = () => {
  search.value = '';
  selectedStatuses.value = [];
};
const clearStatuses = () => {
  selectedStatuses.value = [];
};

// Auth + data
onMounted(async () => {
  // ⚠️ Auth d'abord (sinon /me/appels-participes échoue)
  const token = localStorage.getItem('token');
  if (!user.value && token) {
    try {
      const res = await api.get('/user');
      store.commit('auth/setUser', res.data);
    } catch {
      localStorage.removeItem('token');
      router.push({ name: 'Sign In' });
      return;
    }
  } else if (!user.value) {
    window.location.assign('/backoffice.html#/sign-in');
    return;
  }

  // ✅ Ensuite participations (pour que le filtre "Participée" marche dès le 1er rendu)
  await fetchParticipations();

  // Puis les appels (inchangé)
  await fetchAppels();
});




// ✅ Détecter si l'utilisateur a participé à cet appel
const isParticipated = (appel) => {
  // Cas 1: backend renvoie déjà appel.participe (withExists)
  if (typeof appel.participe !== 'undefined') return !!appel.participe;
  // Cas 2: front maintient participatedIds (Set des idAppel)
  if (typeof participatedIds !== 'undefined' && participatedIds.value instanceof Set) {
    return participatedIds.value.has(appel.idAppel);
  }
  return false;
};

// ✅ Aller vers la page des résultats
const goToResults = (idAppel) => {
  router.push({ name: 'ResultatSoumission', params: { idAppel } });
};
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
.card-appel:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,0.08); }

.badge-statut {
  position: absolute; top: 1rem; right: 1rem;
  padding: 0.4rem 1rem; border-radius: 999px;
  font-size: 0.75rem; font-weight: 600; color: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1); z-index: 1;
}

.text-orange { color: #ff6600; }
.header-orange { padding-top: 2rem; }
.orange-bar { display:block; width:80px; height:4px; background:#ff6600; margin-top:10px; }

/* Bouton "Voir les détails" discret */
.btn-orange-light {
  border: 1px solid #ff6600;
  color: #ff6600;
  border-radius: 40px;
  padding: 0.55rem 1.1rem;
}
.btn-orange-light:hover { background:#ff6600; color:#fff; }










/* ====== SEARCH ====== */
.search-wrap{
  position: relative;
  display: flex;
  align-items: center;
  background: #fff;
  border: 1px solid #e8e9ee;
  border-radius: 14px;
  padding: 10px 14px 10px 42px;
  box-shadow: 0 4px 14px rgba(20, 20, 43, 0.06);
  transition: box-shadow .2s, border-color .2s;
}
.search-wrap i{
  position: absolute; left: 14px; font-size: 16px; color: #9aa3af;
}
.search-input{
  width: 100%;
  border: 0; outline: 0;
  font-size: 16px;
  background: transparent;
}
.search-wrap:focus-within{
  border-color: #ff6600;
  box-shadow: 0 8px 22px rgba(255,102,0,.12);
}

/* ====== FILTER CARD ====== */
.filter-card{
  background: #fff;
  border: 1px solid #e8e9ee;
  border-radius: 16px;
  padding: 16px 16px 10px;
  box-shadow: 0 4px 14px rgba(20, 20, 43, 0.06);
}
.filter-head{
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom: 10px;
}
.btn-link-reset{
  background:none; border:0; padding:0;
  color:#2563eb; text-decoration:underline; font-size: 14px;
}
.btn-link-reset:hover{ color:#1d4ed8; }

/* Pills */
.filter-pills{ display:flex; flex-wrap:wrap; gap:10px; }
.pill{
  display:inline-flex; align-items:center; gap:8px;
  background:#f6f7fb;
  border:1px solid #e8e9ee;
  color:#1f2937;
  padding:8px 12px;
  border-radius:999px;
  font-size:14px;
  cursor:pointer;
  transition: background .2s, border-color .2s, transform .05s;
  user-select:none;
}
.pill input{ display:none; }
.pill .dot{
  width:10px; height:10px; border-radius:50%;
  display:inline-block;
}
.dot-success{ background:#16a34a; }
.dot-danger{ background:#dc2626; }

.pill:hover{ background:#eef0f7; }
.pill.active{ border-color:#ff6600; background: rgba(255,102,0,.08); }

/* ====== BUTTONS ====== */
.btn-ghost-orange{
  display:inline-flex; align-items:center; justify-content:center;
  width:100%;
  border:1.5px solid #ff6600;
  color:#ff6600;
  background:#fff;
  border-radius: 999px;
  padding: 10px 14px;
  font-weight: 600;
  transition: transform .05s, background .2s, color .2s, box-shadow .2s;
}
.btn-ghost-orange:hover{
  background:#ff6600; color:#fff;
  box-shadow: 0 10px 20px rgba(255,102,0,.18);
  transform: translateY(-1px);
}

.btn-reset{
  border:1px solid #cfd3da;
  background:#f7f8fb;
  color:#4b5563;
  border-radius: 10px;
  padding: 10px 16px;
  font-weight:600;
  transition: background .2s, box-shadow .2s, transform .05s;
}
.btn-reset:hover{
  background:#eef0f7;
  box-shadow: 0 8px 18px rgba(20, 20, 43, 0.06);
  transform: translateY(-1px);
}

/* ====== CARDS ====== */
.card-appel{
  border-radius: 22px;
  border:1px solid #eef0f5;
  background: #ffffff;
  padding: 1.4rem;
  box-shadow: 0 10px 30px rgba(20,20,43,.06);
}
.badge-statut{
  top: 14px; right: 14px;
  padding: .35rem .9rem;
  border-radius: 999px;
  font-size: .72rem;
  letter-spacing: .4px;
}

/* Responsive micro‑tweaks */
@media (max-width: 768px){
  .filter-card{ margin-top: 8px; }
}


.search-and-filters{
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.filters-inline{
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.filter-label{
  font-weight: 600;
  color: #374151;
}

.btn-link-reset{
  background: none;
  border: none;
  padding: 0;
  color: #2563eb;
  text-decoration: underline;
  font-size: 14px;
  cursor: pointer;
}
.btn-link-reset:hover{
  color: #1d4ed8;
}

</style>
