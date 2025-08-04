<template>
  <Navbar />
  <div class="container py-5">
    <div class="text-center mb-4">
      <h2 class="fw-bold text-dark">👤 Mon Profil</h2>
      <p class="text-muted">Gérez vos informations personnelles</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow border-0 rounded-4 p-4">
          <!-- Avatar -->
          <div class="text-center mb-4">
        <img
  :src="avatarPreview || (user.avatar ? 'http://localhost:8000/storage/' + user.avatar : defaultAvatar)"
  alt="Avatar"
  class="rounded-circle border"
  width="120"
  height="120"
  style="object-fit: cover"
/>

            <div class="mt-3">
              <label class="form-label">Changer la photo</label>
              <input type="file" class="form-control" @change="handleFileUpload" />
            </div>
          </div>

          <!-- Informations générales -->
          <h5 class="text-orange mb-3">📝 Informations générales</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input v-model="user.nom" type="text" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Prénom</label>
              <input v-model="user.prenom" type="text" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input v-model="user.email" type="email" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <input v-model="user.telephone" type="text" class="form-control" />
            </div>
          </div>

          <!-- Sécurité -->
          <h5 class="text-orange mt-4 mb-3">🔐 Sécurité</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nouveau mot de passe</label>
              <input v-model="user.password" type="password" class="form-control" />
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirmation mot de passe</label>
              <input v-model="user.password_confirmation" type="password" class="form-control" />
            </div>
          </div>

          <!-- Submit -->
          <div class="mt-4 d-flex justify-content-between align-items-center">
            <span class="text-muted">Rôle : {{ user.role }}</span>
            <button class="btn btn-orange" @click="updateProfile">💾 Enregistrer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/Http/api';
import Navbar from '../Navbar.vue';
import Footer from '../Footer.vue';
const defaultAvatar = '/Backoffice/assets/img/default-avatar.jpg';

const user = ref({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  password: '',
  password_confirmation: ''
});

const avatarFile = ref(null);
const avatarPreview = ref(null);

const getUserProfile = async () => {
  try {
    const res = await api.get('http://localhost:8000/api/user', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    user.value = {
      ...res.data,
      password: '',
      password_confirmation: ''
    };
  } catch (err) {
    console.error("Erreur profil:", err);
  }
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const updateProfile = async () => {
  const modifiedPayload = {};
  const originalUser = { ...user.valueInitial }; // <-- à définir lors du chargement

  // Vérifie les champs modifiés
  ['nom', 'prenom', 'email', 'telephone'].forEach(field => {
    if (user.value[field] !== originalUser[field]) {
      modifiedPayload[field] = user.value[field];
    }
  });

  // Vérifie mot de passe
  if (user.value.password) {
    modifiedPayload.password = user.value.password;
    modifiedPayload.password_confirmation = user.value.password_confirmation;
  }

  try {
    let res;

    // ⚡ Si avatar changé, on utilise FormData
    if (avatarFile.value) {
      const formData = new FormData();
      Object.entries(modifiedPayload).forEach(([key, value]) => {
        formData.append(key, value);
      });
      formData.append('avatar', avatarFile.value);

      res = await api.post('http://localhost:8000/api/profil', formData, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'multipart/form-data',
        },
      });
    } else {
      // Sinon simple JSON
      res = await api.put('http://localhost:8000/api/profil', modifiedPayload, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
    }

    alert('✅ Profil mis à jour avec succès.');

    if (res.data.avatar_url) {
      user.value.avatar_url = res.data.avatar_url;
      avatarPreview.value = null;
    }

    await getUserProfile(); // recharge tout
  } catch (err) {
    console.error("❌ Erreur de mise à jour:", err.response?.data || err);
    alert("Erreur de mise à jour.");
  }
};

onMounted(async () => {
  await getUserProfile();
  user.valueInitial = { ...user.value }; // snapshot initial
});




const getAvatarUrl = (avatarPath) => {
  return avatarPath ? `http://localhost:8000/storage/${avatarPath}` : defaultAvatar;
};
</script>

<style scoped>
.btn-orange {
  background-color: #ff6600;
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 0.5rem 1.2rem;
  transition: background 0.3s;
}
.btn-orange:hover {
  background-color: #e25700;
}
.text-orange {
  color: #ff6600;
}
</style>
