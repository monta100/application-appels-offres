<template>
  <div v-if="showModal" class="modal-backdrop">
    <div class="modal-container">
      <!-- Bouton fermer -->
      <button class="close-btn" @click="showModal = false">&times;</button>

      <!-- Titre + infos -->
      <h5 class="mb-2">
        📎 {{ fileName }}
        <small class="text-muted">
          ({{ fileSize }} - {{ fileType }} - {{ fileDate }})
        </small>
      </h5>

      <!-- Boutons actions -->
      <div class="d-flex gap-2 mb-3">
        <a :href="fileUrl" download class="btn btn-success btn-sm">⬇ Télécharger</a>
        <a :href="fileUrl" target="_blank" class="btn btn-primary btn-sm">🔗 Ouvrir</a>
        <button v-if="isImage" class="btn btn-secondary btn-sm" @click="zoomIn">🔍 Zoom +</button>
        <button v-if="isImage" class="btn btn-secondary btn-sm" @click="zoomOut">🔍 Zoom -</button>
      </div>

      <!-- Loader -->
      <div v-if="loading" class="text-center py-4">
        <div class="spinner-border text-orange" role="status"></div>
        <p class="mt-2">Chargement du document...</p>
      </div>

      <!-- Contenu fichier -->
      <div v-else>
        <!-- PDF -->
        <iframe
          v-if="isPDF"
          :src="fileUrl"
          width="100%"
          height="500px"
          frameborder="0"
        ></iframe>

        <!-- Image -->
        <img
          v-else-if="isImage"
          :src="fileUrl"
          :style="{ transform: 'scale(' + zoom + ')' }"
          class="img-fluid"
        />

        <!-- Autres formats -->
        <p v-else class="text-muted">
          📄 Format non prévisualisable. <a :href="fileUrl" target="_blank">Ouvrir</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const showModal = ref(false);
const fileUrl = ref("");
const fileName = ref("");
const fileSize = ref("");
const fileType = ref("");
const fileDate = ref("");
const isPDF = ref(false);
const isImage = ref(false);
const loading = ref(true);
const zoom = ref(1);

const openModal = (url, name, size, type, date) => {
  fileUrl.value = url;
  fileName.value = name;
  fileSize.value = size;
  fileType.value = type;
  fileDate.value = date;
  isPDF.value = type.includes("pdf");
  isImage.value = type.includes("image");
  zoom.value = 1;
  loading.value = true;
  showModal.value = true;

  setTimeout(() => (loading.value = false), 800); // simulation chargement
};

const zoomIn = () => {
  zoom.value += 0.1;
};
const zoomOut = () => {
  zoom.value = Math.max(0.5, zoom.value - 0.1);
};


// 🔴 expose au parent
defineExpose({ openModal, close })
// Exemple : ouverture manuelle
// openModal('/storage/file.pdf', 'file.pdf', '120 Ko', 'application/pdf', '2025-08-15')
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}
.modal-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  width: 90%;
  max-width: 800px;
  position: relative;
  animation: fadeIn 0.3s ease-in-out;
}
.close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
