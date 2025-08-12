<template>
  <div class="container py-4">
    <!-- Titre -->
    <h2 class="fw-bold mb-4 text-orange">
      📊 Analyse IA des Soumissions
    </h2>

    <!-- Timeline -->
    <div class="timeline">
      <div
        v-for="soumission in soumissions"
        :key="soumission.idSoumission"
        class="timeline-item"
      >
        <!-- Point de timeline -->
        <div
          class="timeline-dot"
          :class="soumission.explanation?.verdict === 'acceptée' ? 'dot-green' : 'dot-red'"
        ></div>

        <!-- Contenu -->
        <div class="timeline-content">
          <h5 class="mb-1">
            Soumission #{{ soumission.idSoumission }}
            <span
              class="badge ms-2"
              :class="soumission.explanation?.verdict === 'acceptée' ? 'bg-success' : 'bg-danger'"
            >
              {{ soumission.explanation?.verdict || '—' }}
            </span>
          </h5>
          <p class="text-muted mb-1">
            Prix proposé : <strong>{{ soumission.prixPropose }} DT</strong> |
            Délai : <strong>{{ soumission.temps_realisation }} jours</strong>
          </p>
          <p class="mb-2">
            <strong>Raisons :</strong>
            <span v-if="soumission.explanation?.categories?.length">
              {{ soumission.explanation.categories.join(', ') }}
            </span>
            <span v-else>aucune</span>
          </p>
          <p class="public-phrase fst-italic" v-if="soumission.explanation?.public_phrase">
            "{{ soumission.explanation.public_phrase }}"
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/Http/api";

// 📌 Définition de la prop
const props = defineProps({
  idAppel: {
    type: Number,
    required: true
  }
});

// 📌 Données réactives
const soumissions = ref([]);

// 📌 Récupération des explications à l'affichage
onMounted(async () => {
  try {
    const res = await api.get(`/explications/appel/${props.idAppel}`);
    soumissions.value = res.data;
  } catch (error) {
    console.error("Erreur récupération explications :", error);
  }
});
</script>


<style scoped>
.text-orange {
  color: #f97316;
}

/* Timeline principale */
.timeline {
  position: relative;
  margin-left: 20px;
  padding-left: 20px;
  border-left: 3px solid #ddd;
}

/* Élément de timeline */
.timeline-item {
  position: relative;
  margin-bottom: 30px;
}

/* Point */
.timeline-dot {
  position: absolute;
  left: -11px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 0 0 3px #ddd;
}

.dot-green {
  background-color: #22c55e;
  box-shadow: 0 0 0 3px #22c55e44;
}

.dot-red {
  background-color: #ef4444;
  box-shadow: 0 0 0 3px #ef444444;
}

/* Contenu */
.timeline-content {
  background: white;
  padding: 15px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s;
}

.timeline-content:hover {
  transform: translateY(-2px);
}

.public-phrase {
  background: #fef3c7;
  padding: 8px;
  border-radius: 6px;
  font-size: 0.9rem;
}
</style>
