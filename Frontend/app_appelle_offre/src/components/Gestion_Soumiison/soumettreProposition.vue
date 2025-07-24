<template>
  <Navbar />
  <div class="container py-5 min-vh-100">
    <div class="card shadow p-4 mx-auto" style="max-width: 700px">
      <h2 class="text-orange fw-bold mb-4">Soumettre une proposition</h2>

      <form @submit.prevent="submitForm" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Prix proposé (TND)</label>
          <input v-model="form.prixPropose" type="number" class="form-control" required min="0" />
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea v-model="form.description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Temps de réalisation</label>
          <input v-model="form.temps_realisation" type="text" class="form-control" placeholder="Ex : 10 jours" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Fichier joint (PDF, DOCX, DOC)</label>
          <input type="file" class="form-control" accept=".pdf,.doc,.docx" @change="handleFile" />
        </div>

        <button type="submit" class="btn btn-orange w-100 mt-3">✅ Envoyer la proposition</button>

        <router-link to="/offreCl" class="btn btn-outline-secondary w-100 mt-3">
          ⬅️ Retour à la liste
        </router-link>
      </form>

      <div v-if="message" class="alert alert-success mt-4">{{ message }}</div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/Http/api';
import Navbar from '../Navbar.vue';
import Footer from '../Footer.vue';
import { useStore } from 'vuex';

const store = useStore();
const user = computed(() => store.state.auth.user);
const route = useRoute();

const form = ref({
  prixPropose: '',
  description: '',
  temps_realisation: '',
  fichier_joint: null,
});

const message = ref('');

// 📁 Gestion fichier
const handleFile = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.fichier_joint = file;
    console.log("📁 Fichier sélectionné :", file.name);
  }
};

// 🔄 Créer un FormData
const buildFormData = (data) => {
  const formData = new FormData();
  for (const key in data) {
    if (data[key] !== null && data[key] !== undefined) {
      formData.append(key, data[key]);
    }
  }

  // Debug
  for (let [key, val] of formData.entries()) {
    console.log(`📦 ${key}:`, val);
  }

  return formData;
};

// 🚀 Soumission
const submitForm = async () => {
  try {
    const formData = new FormData();
    formData.append('prixPropose', form.value.prixPropose);
    formData.append('description', form.value.description);
    formData.append('temps_realisation', form.value.temps_realisation);
    formData.append('idAppel', route.params.idAppel);

    // Ajout du fichier SEULEMENT si choisi
    if (form.value.fichier_joint) {
      formData.append('fichier_joint', form.value.fichier_joint);
    }

    // Envoi
    await api.post('/soumissions', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    message.value = "✅ Proposition envoyée avec succès !";
    form.value = { prixPropose: '', description: '', temps_realisation: '', fichier_joint: null };
  } catch (err) {
    if (err.response?.status === 422) {
      console.error("Validation Laravel :", err.response.data.errors);
    } else {
      console.error(err);
    }
    alert("❌ Erreur lors de la soumission. Vérifie les champs ou le format du fichier.");
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
  font-weight: bold;
  border: none;
  transition: 0.3s ease;
}

.btn-orange:hover {
  background-color: #e55b00;
}
</style>
