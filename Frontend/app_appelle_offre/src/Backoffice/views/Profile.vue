<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-dark">Mon Profil</h2>
      <router-link to="/" class="btn btn-outline-primary">
        ← Retour
      </router-link>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-3 text-center">
            <img
              src="@/Backoffice/assets/img/default-avatar.jpg"
              alt="avatar"
              class="img-fluid rounded-circle mb-2"
              style="width: 120px; height: 120px"
            />
            <p class="fw-semibold mb-0">{{ user.nom }} {{ user.prenom }}</p>
            <small class="text-muted">{{ user.role }}</small>
          </div>

          <div class="col-md-9">
            <form @submit.prevent="updateProfile">
              <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input
                  v-model="user.nom"
                  type="text"
                  class="form-control"
                  id="nom"
                />
              </div>
              <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input
                  v-model="user.prenom"
                  type="text"
                  class="form-control"
                  id="prenom"
                />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  v-model="user.email"
                  type="email"
                  class="form-control"
                  id="email"
                />
              </div>
              <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input
                  v-model="user.telephone"
                  type="text"
                  class="form-control"
                  id="telephone"
                />
              </div>
              <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <input
                  v-model="user.role"
                  type="text"
                  class="form-control"
                  id="role"
                  disabled
                />
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input
                  v-model="user.password"
                  type="password"
                  class="form-control"
                  id="password"
                />
              </div>
              <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input
                  v-model="user.password_confirmation"
                  type="password"
                  class="form-control"
                  id="password_confirmation"
                />
              </div>

              <button type="submit" class="btn btn-success">
                Mettre à jour
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserProfile',
  data() {
    return {
      user: {
        nom: '',
        prenom: '',
        email: '',
        telephone: '',
        role: '',
        password: '',
        password_confirmation: ''
      }
    };
  },
  mounted() {
    this.getUserProfile();
  },
  methods: {
    async getUserProfile() {
      try {
        const response = await axios.get('http://localhost:8000/api/user', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json',
          },
        });

        this.user = {
          ...response.data,
          password: '',
          password_confirmation: ''
        };
      } catch (error) {
        console.error("Erreur lors du chargement du profil :", error);
      }
    },
  async updateProfile() {
  try {
    const payload = {};

    // Ajouter uniquement les champs remplis/modifiés
    if (this.user.nom) payload.nom = this.user.nom;
    if (this.user.prenom) payload.prenom = this.user.prenom;
    if (this.user.email) payload.email = this.user.email;
    if (this.user.telephone) payload.telephone = this.user.telephone;
    if (this.user.password) {
      payload.password = this.user.password;
      payload.password_confirmation = this.user.password_confirmation;
    }

    const response = await axios.put('http://localhost:8000/api/profil', payload, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
    });

    alert(response.data.message || "Profil mis à jour !");
    this.getUserProfile(); // recharger les données
  } catch (error) {
    console.error("Erreur de mise à jour :", error.response?.data);
    alert("Erreur : " + (error.response?.data?.message || "Une erreur est survenue"));
  }
}

  },
};
</script>
