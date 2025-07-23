<template>
  <div class="signup-page">
    <header class="header">
      <div class="header-content">
        <h1>Rejoignez-nous</h1>
        <p>Créez votre compte pour gérer les appels d'offres efficacement.</p>
      </div>
    </header>

    <div class="form-container">
      <button class="btn-exit" @click="$router.go(-1)">
        <i class="fa fa-arrow-left"></i> Retour
      </button>

      <transition name="fade" mode="out-in">
        <form v-if="step === 1" key="step1" class="card" @submit.prevent="nextStep">
          <h2>Informations personnelles</h2>

          <div class="form-group">
            <label for="nom"><i class="fa fa-user"></i> Nom</label>
            <input id="nom" v-model="form.nom" type="text" required />
          </div>

          <div class="form-group">
            <label for="prenom"><i class="fa fa-user"></i> Prénom</label>
            <input id="prenom" v-model="form.prenom" type="text" required />
          </div>

          <div class="form-group">
            <label for="email"><i class="fa fa-envelope"></i> Email</label>
            <input id="email" v-model="form.email" type="email" required />
          </div>

          <div class="form-group">
            <label for="telephone"><i class="fa fa-phone"></i> Téléphone</label>
            <input id="telephone" v-model="form.telephone" type="text" required />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-next">Suivant ➔</button>
          </div>
        </form>

        <form v-else key="step2" class="card" @submit.prevent="submitForm">
          <h2>Sécurité & Rôle</h2>

          <div class="form-group">
            <label for="password"><i class="fa fa-lock"></i> Mot de passe</label>
            <input id="password" v-model="form.password" type="password" required />
          </div>

          <div class="form-group">
            <label for="confirm"><i class="fa fa-lock"></i> Confirmation</label>
            <input id="confirm" v-model="form.password_confirmation" type="password" required />
          </div>

          <div class="form-group">
            <label for="role"><i class="fa fa-user-tag"></i> Rôle</label>
            <select id="role" v-model="form.role" required>
              <option disabled value="">Sélectionnez un rôle</option>
              <option value="admin">Admin</option>
              <option value="representant">Représentant</option>
              <option value="participant">Participant</option>
            </select>
          </div>

          <div class="form-group checkbox">
            <input type="checkbox" id="terms" v-model="form.terms" required />
            <label for="terms">J'accepte les <a href="#">conditions d'utilisation</a></label>
          </div>

          <div class="form-actions">
            <button type="button" @click="step = 1" class="btn-back">← Retour</button>
            <button type="submit" class="submit-btn">S'inscrire</button>
          </div>
        </form>
      </transition>
    </div>
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import axios from 'axios';

export default {
  name: "SignUpAnimated",
  data() {
    return {
      step: 1,
      form: {
        nom: "",
        prenom: "",
        email: "",
        telephone: "",
        password: "",
        password_confirmation: "",
        role: "",
        terms: false
      }
    };
  },
  created() {
    // Cache navbar/sidebar/footer via Vuex si existant
    this.toggleEveryDisplay?.();
    this.toggleHideConfig?.();

    // Fallback : cacher sidenav manuellement
    const sidenav = document.querySelector(".g-sidenav-show");
    sidenav?.classList.add("g-sidenav-hidden");
  },
  beforeUnmount() {
    this.toggleEveryDisplay?.();
    this.toggleHideConfig?.();

    const sidenav = document.querySelector(".g-sidenav-show");
    sidenav?.classList.remove("g-sidenav-hidden");
  },
  methods: {
    ...mapMutations(["toggleEveryDisplay", "toggleHideConfig"]),
    nextStep() {
      this.step = 2;
    },
   submitForm() {
  if (!this.form.terms) {
    alert("Veuillez accepter les conditions.");
    return;
  }

  axios.post('http://localhost:8000/api/register', this.form)
    .then(response => {
      alert("Inscription réussie !");
      this.$router.push({ name: "Dashboard" });
    })
    .catch(error => {
      console.error(error);
      alert("Erreur lors de l'inscription.");
    });
}

  }
};
</script>

<style scoped>
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css");

.signup-page {
  background: #f3f5f9;
  min-height: 100vh;
  font-family: "Inter", sans-serif;
}

.header {
  text-align: center;
  background: linear-gradient(to right, #16a085, #1abc9c);
  color: white;
  padding: 50px 20px;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}

.header h1 {
  font-size: 2.3rem;
}

.form-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 15px;
}

.btn-exit {
  align-self: flex-start;
  margin-bottom: 20px;
  background: none;
  border: none;
  color: #1abc9c;
  font-size: 0.95rem;
  cursor: pointer;
  display: flex;
  align-items: center;
}
.btn-exit i {
  margin-right: 6px;
}

.card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
}

.card h2 {
  margin-bottom: 20px;
  color: #1abc9c;
  text-align: center;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
  font-size: 0.9rem;
}

input,
select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.95rem;
}

input:focus,
select:focus {
  border-color: #1abc9c;
  outline: none;
}

.checkbox {
  font-size: 0.9rem;
}

.checkbox input {
  margin-right: 6px;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 20px;
}

.btn-next,
.btn-back,
.submit-btn {
  flex: 1;
  padding: 10px;
  background: #1abc9c;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-back {
  background: #ccc;
  color: #333;
}

.btn-next:hover,
.submit-btn:hover {
  background: #149e85;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
