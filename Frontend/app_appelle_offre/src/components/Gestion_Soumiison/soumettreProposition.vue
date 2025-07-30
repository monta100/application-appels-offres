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
        <button
  type="button"
  class="btn btn-outline-primary w-100 mb-3"
  @click="autofillWithGpt4"
>
  ⚡ Remplir automatiquement avec l'IA
</button>


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
import { ref, computed, onMounted } from 'vue';
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

// ⚡ Auto-remplissage via IA
const autofillWithGpt4 = async () => {
  console.log("▶️ Début de la génération via GPT-4...");

  try {
    const idAppel = route.params.idAppel;
    console.log("📌 ID de l’appel :", idAppel);

    const res = await api.get(`/appels/${idAppel}`);
    const appelData = res.data;
    appel.value = appelData;
    console.log("📥 Appel d’offre récupéré :", appelData);

    // 💡 Prompt basé uniquement sur la description
    const prompt = `
Tu es un prestataire expérimenté.

Voici la description d'un projet à traiter : 
"${appelData.description}"

Donne une proposition adaptée, incluant :
1. Un prix proposé (en TND)
2. Un temps estimé (ex : 10 jours)
3. Une courte description de ta proposition
Réponds STRICTEMENT en format JSON comme ceci , c est a dire propose ton propre suggestion  pour toutes les champos vous avez liberte totalle:
{
  "prix": 3000,
  "temps": "12 jours",
  "description": "Je propose une prestation complète incluant le développement, les tests et la documentation..."
}
`.trim();

    console.log("✉️ Prompt envoyé :", prompt);

    const response = await fetch("https://chatgpt-42.p.rapidapi.com/aitohuman", {
      method: "POST",
      headers: {
        "content-type": "application/json",
        "X-RapidAPI-Key": "83870a5b6fmshd9175a2f4a4f98fp12596djsnf63906983894", // 👈 ta clé API
        "X-RapidAPI-Host": "chatgpt-42.p.rapidapi.com"
      },
      body: JSON.stringify({ text: prompt })
    });

    console.log("⏳ Attente de la réponse...");
    const result = await response.json();
    console.log("📨 Réponse GPT-4 brute :", result);

    if (result?.result?.length > 0) {
      try {
        const rawJson = result.result[0];
        console.log("📦 JSON reçu brut :", rawJson);

        const data = JSON.parse(rawJson);
        console.log("✅ JSON parsé :", data);

        form.value.prixPropose = data.prix;
        form.value.temps_realisation = data.temps;
        form.value.description = data.description;
      } catch (err) {
        console.error("❌ Erreur de parsing JSON :", err);
        alert("⚠️ Erreur de parsing JSON : " + result.result[0]);
      }
    } else {
      console.warn("❌ Réponse vide ou invalide de GPT-4.");
      alert("❌ Réponse vide ou invalide de GPT-4.");
    }
  } catch (error) {
    console.error("❌ Erreur générale GPT-4 :", error);
    alert("❌ Erreur lors de l’appel à GPT-4.");
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
