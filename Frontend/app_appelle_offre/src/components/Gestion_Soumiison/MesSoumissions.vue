<template>
  <Navbar />
  <div class="container py-5">
    <h2 class="text-orange fw-bold mb-4">📄 Mes Soumissions</h2>

    <div v-if="soumissions.length">
      <div v-for="s in soumissions" :key="s.idSoumission" class="card shadow p-3 mb-3">
        <h5 class="fw-bold">Soumission pour Appel #{{ s.idAppel }}</h5>
        <p><strong>Prix :</strong> {{ s.prixPropose }} TND</p>
        <p><strong> temps_realisation Propose:</strong> {{ s.temps_realisation }}</p>
        <p><strong>Description :</strong> {{ s.description }}</p>

        <div class="d-flex gap-2 mt-3">
          <a :href="`http://localhost:8000/storage/${s.fichier_joint}`" target="_blank" class="btn btn-orange-light me-2">
            📎 Voir le fichier
          </a>
          <button class="btn btn-outline-danger" @click="confirmDelete(s.idSoumission)">🗑 Supprimer</button>
        </div>
      </div>
    </div>
    <div v-else>
      <p class="text-muted">Vous n'avez encore soumis aucune proposition.</p>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-backdrop">
      <div class="modal-content p-4">
        <p>Êtes-vous sûr de vouloir supprimer cette soumission ?</p>
        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-danger" @click="deleteSoumission">Oui, Supprimer</button>
          <button class="btn btn-secondary" @click="showModal = false">Annuler</button>
        </div>
      </div>
    </div>

  </div>
  <Footer />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/Http/api';
import { useStore } from 'vuex';
import Navbar from '../Navbar.vue';
import Footer from '../Footer.vue';

const store = useStore();
const user = computed(() => store.state.auth.user);
const soumissions = ref([]);

const showModal = ref(false);
const selectedId = ref(null);

const confirmDelete = (id) => {
  selectedId.value = id;
  showModal.value = true;
};

const deleteSoumission = async () => {
  try {
    await api.delete(`/soumissions/${selectedId.value}`);
    soumissions.value = soumissions.value.filter(s => s.idSoumission !== selectedId.value);
    showModal.value = false;
  } catch (err) {
    console.error('Erreur suppression', err);
  }
};

onMounted(async () => {
  try {
    const res = await api.get('/mes-soumissions'); // Vous devez créer cette route côté Laravel
    soumissions.value = res.data;
  } catch (err) {
    console.error('Erreur chargement soumissions', err);
  }
});
</script>

<style scoped>
.text-orange {
  color: #ff6600;
}
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex; justify-content: center; align-items: center;
}
.modal-content {
  background: white;
  border-radius: 10px;
  max-width: 400px;
}

.btn-orange-light {
  background-color: #ffa94d; /* orange clair */
  color: white;
  font-weight: 600;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease, transform 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 15px;
}

.btn-orange-light:hover {
  background-color: #ff922b;
  transform: translateY(-1px);
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

</style>
