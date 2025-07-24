<template>
  <Navbar />

  <div v-if="loading" class="text-center py-5">
    <div class="spinner-border text-warning" role="status"></div>
  </div>

  <div v-else class="container py-5">
    <!-- Hero Bienvenue -->
    <div class="hero-section bg-orange text-white p-5 rounded-4 mb-5 position-relative overflow-hidden">
      <div class="d-flex align-items-center gap-3">
        <i class="fas fa-handshake fa-2x"></i>
        <div>
          <h2 class="fw-bold mb-1">Bonjour, {{ user?.prenom || '' }} {{ user?.nom || '' }} !</h2>
          <p class="mb-0">Commencez à publier vos appels d'offres en toute simplicité.</p>
        </div>
      </div>
      <div class="wave-decoration"></div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
      <div class="col-md-2" v-for="kpi in kpis" :key="kpi.label">
        <div class="card-kpi text-center" :class="kpi.bg">
          <i :class="kpi.icon + ' icon'"></i>
          <div class="value">{{ kpi.value }}</div>
          <div class="label">{{ kpi.label }}</div>
        </div>
      </div>
    </div>

    <!-- Résumé -->
    <div class="alert alert-info d-flex justify-content-between align-items-center">
      <div><i class="fas fa-bullhorn me-2"></i> Vous avez actuellement {{ nbTotal.value }} appels d'offres en cours.</div>
      <div v-if="nextDeadline">
        Prochaine date limite : <strong>{{ nextDeadline }}</strong>
      </div>
    </div>

    <AddOffre />

    <!-- Tableau -->
    <div class="table-responsive">
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
            <th>Statut</th>
            <th>Actions</th>

          </tr>
          
        </thead>
        <tbody>
          <tr v-for="(appel, index) in paginatedAppels" :key="appel.idAppelleOffre">
            <td>{{ (page - 1) * perPage + index + 1 }}</td>

            <td @dblclick="editingField = { id: appel.idAppelleOffre, field: 'titre' }">
              <template v-if="editingField?.id === appel.idAppelleOffre && editingField?.field === 'titre'">
                <input v-model="appel.titre" class="form-control form-control-sm" @blur="updateField(appel, 'titre')" @keyup.enter="updateField(appel, 'titre')" />
              </template>
              <template v-else>
                <span class="editable">{{ appel.titre }}</span>
              </template>
            </td>

            <td @dblclick="editingField = { id: appel.idAppelleOffre, field: 'description' }">
              <template v-if="editingField?.id === appel.idAppelleOffre && editingField?.field === 'description'">
                <textarea v-model="appel.description" class="form-control form-control-sm" rows="2" @blur="updateField(appel, 'description')" @keyup.enter="updateField(appel, 'description')"></textarea>
              </template>
              <template v-else>
                <span class="editable">{{ appel.description }}</span>
              </template>
            </td>

            <td @dblclick="editingField = { id: appel.idAppelleOffre, field: 'date_debut' }">
              <template v-if="editingField?.id === appel.idAppelleOffre && editingField?.field === 'date_debut'">
                <input type="date" v-model="appel.date_debut" class="form-control form-control-sm" @blur="updateField(appel, 'date_debut')" @keyup.enter="updateField(appel, 'date_debut')" />
              </template>
              <template v-else>
                <span class="editable">{{ formatDate(appel.date_debut) }}</span>
              </template>
            </td>

            <td @dblclick="editingField = { id: appel.idAppelleOffre, field: 'date_fin' }">
              <template v-if="editingField?.id === appel.idAppelleOffre && editingField?.field === 'date_fin'">
                <input type="date" v-model="appel.date_fin" class="form-control form-control-sm" @blur="updateField(appel, 'date_fin')" @keyup.enter="updateField(appel, 'date_fin')" />
              </template>
              <template v-else>
                <span class="editable">{{ formatDate(appel.date_fin) }}</span>
              </template>
            </td>
<td @dblclick="editingField = { id: appel.idAppelleOffre, field: 'idDomaine' }">
  <template v-if="editingField?.id === appel.idAppelleOffre && editingField?.field === 'idDomaine'">
    <select
      v-model="appel.idDomaine"
      class="form-select form-select-sm"
      @blur="updateField(appel, 'idDomaine')"
      @change="updateField(appel, 'idDomaine')"
    >
      <option v-for="domaine in domaines" :key="domaine.idDomaine" :value="domaine.idDomaine">
        {{ domaine.nom }}
      </option>
    </select>
  </template>
  <template v-else>
    <span class="editable">{{ appel.domaine?.nom || '—' }}</span>
  </template>
</td>


            <td>{{ formatDate(appel.created_at) || '---' }}</td>
            <td @dblclick="editingField = { id: appel.idAppelleOffre, field: 'statut' }">
  <template v-if="editingField?.id === appel.idAppelleOffre && editingField?.field === 'statut'">
    <select
      v-model="appel.statut"
      class="form-select form-select-sm"
      @blur="updateField(appel, 'statut')"
      @change="updateField(appel, 'statut')"
    >
      <option value="publiée">Publiée</option>
      <option value="cloturé">Clôturé</option>
      <option value="cloturé">brouillon</option>

    </select>
  </template>
  <template v-else>
    <span class="badge" :class="appel.statut === 'cloturé' ? 'bg-danger' : 'bg-success'">
      {{ appel.statut.toUpperCase() }}
    </span>







  </template>
</td>

    <td>
  <button
    class="btn btn-sm btn-outline-danger"
    data-bs-toggle="modal"
    :data-bs-target="'#confirmDeleteModal' + appel.idAppelleOffre"
  >
    <i class="fa fa-trash"></i>
  </button>
</td>

<router-link
  :to="`/appels/${appel.idAppel}/soumissions`"
  class="btn btn-sm btn-info"
>
  👥 Voir participants
</router-link>


          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-end align-items-center mt-3">
      <button class="btn btn-sm btn-outline-secondary me-2" :disabled="page === 1" @click="page--">Précédent</button>
      <span class="fw-bold">Page {{ page }}</span>
      <button class="btn btn-sm btn-outline-secondary ms-2" :disabled="(page * perPage) >= appelsOffres.length" @click="page++">Suivant</button>
    </div>
  </div>

  <Footer />










<!-- Modals de confirmation de suppression -->
<div v-for="appel in paginatedAppels" :key="'modal-' + appel.idAppelleOffre">
  <div class="modal fade" :id="'confirmDeleteModal' + appel.idAppelleOffre" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirmer la suppression</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Êtes-vous sûr de vouloir supprimer <strong>{{ appel.titre }}</strong> ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button
            type="button"
            class="btn btn-danger"
            @click="deleteAppel(appel.idAppel)"
            data-bs-dismiss="modal"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</div>




</template>


<script setup>
import { onMounted, ref, computed } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import api from '@/Http/api';
import Navbar from '@/components/Navbar.vue';
import Footer from '@/components/Footer.vue';
import AddOffre from './AddOffre.vue';

const appelsOffres = ref([]);
const loading = ref(true);
const user = computed(() => store.state.auth.user);
const store = useStore();
const router = useRouter();
// Pagination
const page = ref(1);
const perPage = 5;
const paginatedAppels = computed(() => {
  const start = (page.value - 1) * perPage;
  return appelsOffres.value.slice(start, start + perPage);
});

// Format de date
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR');
};
const domaines = ref([]);

const fetchDomaines = async () => {
  try {
    const response = await api.get('/domaines'); // ou ton endpoint réel
    domaines.value = response.data;
  } catch (error) {
    console.error("Erreur lors du chargement des domaines :", error);
  }
};
// KPI dynamiques
const nbPublie = computed(() => appelsOffres.value.filter(o => o.statut === 'publié').length);
const nbBrouillon = computed(() => appelsOffres.value.filter(o => o.statut !== 'publié').length);
const nbTotal = computed(() => appelsOffres.value.length);
const nbExpires = computed(() => appelsOffres.value.filter(o => new Date(o.date_fin) < new Date()).length);
const nbRestants = computed(() => nbTotal.value - nbExpires.value);
const editingField = ref({ id: null, field: null });

// Prochaine date limite
const nextDeadline = computed(() => {
  const dates = appelsOffres.value
    .filter(o => new Date(o.date_fin) >= new Date())
    .map(o => new Date(o.date_fin))
    .sort((a, b) => a - b);
  return dates[0]?.toLocaleDateString('fr-FR') || null;
});

// KPIs à afficher
const kpis = computed(() => [
  { label: "Publiés", value: nbPublie.value, icon: "fas fa-circle-check", bg: "bg-kpi-success" },
  { label: "Brouillons", value: nbBrouillon.value, icon: "fas fa-file-lines", bg: "bg-kpi-secondary" },
  { label: "Total", value: nbTotal.value, icon: "fas fa-clipboard-list", bg: "bg-kpi-dark" },
  { label: "Expirés", value: nbExpires.value, icon: "fas fa-calendar-xmark", bg: "bg-kpi-danger" },
  { label: "Restants", value: nbRestants.value, icon: "fas fa-hourglass-start", bg: "bg-kpi-info" }
]);

// Chargement des données
const fetchAppelsOffres = async () => {
  try {
    const res = await api.get('/appelle_offres/user');
    appelsOffres.value = res.data;

  } catch (err) {
    console.error("Erreur chargement appels d'offres :", err);
  } finally {
    loading.value = false;
  }
};

// Auth & initialisation
onMounted(async () => {
  const token = localStorage.getItem('token');
  if (!user.value && token) {
    try {
      const res = await api.get('/user');
      store.commit('auth/setUser', res.data);
    } catch (err) {
      localStorage.removeItem('token');
      router.push({ name: 'Sign In' });
      return;
    }
  } else if (!user.value) {
    window.location.assign('/backoffice.html#/sign-in');

    return;
  }
  await fetchAppelsOffres();
   await  fetchDomaines();

  
});
const updateField = async (appel, field) => {
  try {
    console.log("Update:", {
  [field]: appel[field]
})
    await api.put(`/appels/${appel.idAppel}`, {
      [field]: appel[field]
    });
    editingField.value = null;
  } catch (err) {
    console.error(err);
  }
};
const deleteAppel = async (id) => {
  if (!id) {
    console.error("❌ ID manquant pour suppression");
    return;
  }

  try {
    await api.delete(`/appels/${id}`);
    appelsOffres.value = appelsOffres.value.filter(appel => appel.idAppel !== id);
    console.log("✅ Appel supprimé :", id);
  } catch (err) {
    console.error("Erreur de suppression :", err);
  }
};


</script>

<style scoped>
.hero-section {
  background: linear-gradient(45deg, #ff6f00, #f57c00);
  border-radius: 20px 0 20px 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  position: relative;
}
.wave-decoration {
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 100%;
  height: 40px;
  background: url('/images/wave.svg') no-repeat center;
  background-size: cover;
}
.card-kpi {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: none;
  border-radius: 16px;
  padding: 20px 10px;
  background: white;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  min-width: 120px;
}
.card-kpi:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}
.card-kpi .icon {
  font-size: 1.9rem;
  margin-bottom: 10px;
}
.card-kpi .label {
  font-weight: 600;
  font-size: 0.9rem;
  margin-top: 4px;
}
.card-kpi .value {
  font-size: 1.5rem;
  font-weight: 700;
}
.bg-kpi-success {
  background: linear-gradient(135deg, #16a085, #1abc9c);
  color: white;
}
.bg-kpi-secondary {
  background: linear-gradient(135deg, #7f8c8d, #95a5a6);
  color: white;
}
.bg-kpi-dark {
  background: linear-gradient(135deg, #2c3e50, #34495e);
  color: white;
}
.bg-kpi-danger {
  background: linear-gradient(135deg, #c0392b, #e74c3c);
  color: white;
}
.bg-kpi-info {
  background: linear-gradient(135deg, #2980b9, #3498db);
  color: white;
}
</style>
