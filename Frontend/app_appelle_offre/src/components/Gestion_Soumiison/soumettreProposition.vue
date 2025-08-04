<template>
  <Navbar />
  <div class="container py-5 min-vh-100">
    <div class="card shadow p-4 mx-auto" style="max-width: 700px">
      <h2 class="text-orange fw-bold mb-4">Soumettre une proposition</h2>
<form @submit.prevent="submitForm" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Prix proposé (TND)</label>
    <input v-model.number="form.prixPropose" type="number" class="form-control" />
    <small class="text-danger" v-if="errors.prixPropose">{{ errors.prixPropose }}</small>
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea v-model="form.description" class="form-control" rows="4"></textarea>
    <small class="text-danger" v-if="errors.description">{{ errors.description }}</small>
  </div>

  <div class="mb-3">
    <label class="form-label">Temps de réalisation (en jours)</label>
    <input v-model="form.temps_realisation" type="number" class="form-control" />
    <small class="text-danger" v-if="errors.temps_realisation">{{ errors.temps_realisation }}</small>
  </div>

  <div class="mb-3">
    <label class="form-label">Fichier joint (PDF, DOCX, DOC)</label>
    <input type="file" class="form-control" accept=".pdf,.doc,.docx" @change="handleFile" />
  </div>

  <button :disabled="!formIsValid" type="submit" class="btn btn-orange w-100 mt-3">
    ✅ Envoyer la proposition
  </button>
</form>


      <div v-if="message" class="alert alert-success mt-4">{{ message }}</div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed, onMounted,watch  } from 'vue';
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
const appel = ref({});

// 📥 Récupération de l’appel d’offre lors du chargement
onMounted(async () => {
  const res = await api.get(`/appels/${route.params.idAppel}`);
  appel.value = res.data;
});

// 📁 Gestion fichier
const handleFile = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.fichier_joint = file;
    console.log("📁 Fichier sélectionné :", file.name);
  }
};

// 🚀 Soumission
const submitForm = async () => {
  try {
    const formData = new FormData();
    formData.append('prixPropose', form.value.prixPropose);
    formData.append('description', form.value.description);
    formData.append('temps_realisation', form.value.temps_realisation);
    formData.append('idAppel', route.params.idAppel);

    if (form.value.fichier_joint) {
      formData.append('fichier_joint', form.value.fichier_joint);
    }

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
    alert("❌ Erreur lors de la soumission.");
  }
};

const errors = ref({
  prixPropose: '',
  description: '',
  temps_realisation: ''
});

const validateForm = () => {
  errors.value.prixPropose = form.value.prixPropose > 0 ? '' : 'Le prix doit être supérieur à 0';
  errors.value.description = form.value.description.length >= 5 ? '' : 'La description doit faire au moins 5 caractères';
  errors.value.temps_realisation = Number(form.value.temps_realisation) > 0 ? '' : 'Le temps de réalisation doit être un nombre > 0';
};

const formIsValid = computed(() => {
  return (
    form.value.prixPropose > 0 &&
    form.value.description.length >= 5 &&
    Number(form.value.temps_realisation) > 0
  );
});
// Watch en temps réel
watch(() => form.value.prixPropose, validateForm);
watch(() => form.value.description, validateForm);
watch(() => form.value.temps_realisation, validateForm);

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
