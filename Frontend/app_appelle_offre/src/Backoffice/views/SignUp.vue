<template>
  <div class="signup-page">
    <header class="header">
      <div class="header-content">
        <h1><i class="fa fa-star spark"></i> Créez votre compte gratuitement</h1>
        <p>Gérez vos appels d'offres avec style et efficacité.</p>
      </div>
    </header>

    <div class="form-container">
      <button class="btn-exit" @click="$router.go(-1)">
        <i class="fa fa-arrow-left"></i> Retour
      </button>

      <div class="stepper">
        <div :class="['step', step === 1 ? 'active' : '']">1</div>
        <div class="line"></div>
        <div :class="['step', step === 2 ? 'active' : '']">2</div>
      </div>

      <transition name="fade" mode="out-in">
        <form v-if="step === 1" key="step1" class="card" @submit.prevent="nextStep">
          <h2>1. Informations personnelles</h2>

          <div class="form-group">
            <label for="nom"><i class="fa fa-user"></i> Nom</label>
            <input id="nom" v-model="form.nom" @input="validateField('nom')" type="text" required />
            <p v-if="errors.nom" class="error-msg">{{ errors.nom }}</p>
          </div>

          <div class="form-group">
            <label for="prenom"><i class="fa fa-user"></i> Prénom</label>
            <input id="prenom" v-model="form.prenom" @input="validateField('prenom')" type="text" required />
            <p v-if="errors.prenom" class="error-msg">{{ errors.prenom }}</p>
          </div>

          <div class="form-group">
            <label for="email"><i class="fa fa-envelope"></i> Email</label>
            <input id="email" v-model="form.email" @input="validateField('email')" type="email" required />
            <p v-if="errors.email" class="error-msg">{{ errors.email }}</p>
          </div>

          <div class="form-group">
            <label for="telephone"><i class="fa fa-phone"></i> Téléphone</label>
            <input id="telephone" v-model="form.telephone" @input="validateField('telephone')" type="text" required />
            <p v-if="errors.telephone" class="error-msg">{{ errors.telephone }}</p>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-next">Suivant ➔</button>
          </div>
        </form>

        <form v-else key="step2" class="card" @submit.prevent="submitForm">
          <h2>2. Sécurité & Rôle</h2>

          <div class="form-group">
            <label for="password"><i class="fa fa-lock"></i> Mot de passe</label>
            <input id="password" v-model="form.password" @input="validateField('password')" type="password" required />
            <p v-if="errors.password" class="error-msg">{{ errors.password }}</p>
          </div>

          <div class="form-group">
            <label for="confirm"><i class="fa fa-lock"></i> Confirmation</label>
            <input id="confirm" v-model="form.password_confirmation" @input="validateField('password_confirmation')" type="password" required />
            <p v-if="errors.password_confirmation" class="error-msg">{{ errors.password_confirmation }}</p>
          </div>

          <div class="form-group">
            <label for="role"><i class="fa fa-user-tag"></i> Rôle</label>
            <select id="role" v-model="form.role" @change="validateField('role')" required>
              <option disabled value="">Sélectionnez un rôle</option>
              <option value="representant">Représentant</option>
              <option value="participant">Participant</option>
            </select>
            <p v-if="errors.role" class="error-msg">{{ errors.role }}</p>
          </div>

          <div class="form-group checkbox">
            <input type="checkbox" id="terms" v-model="form.terms" @change="validateField('terms')" required />
            <label for="terms">J'accepte les <a href="#">conditions d'utilisation</a></label>
          </div>
          <p v-if="errors.terms" class="error-msg">{{ errors.terms }}</p>

          <div class="form-actions">
            <button type="button" @click="step = 1" class="btn-back">← Retour</button>
            <button type="submit" class="submit-btn">🚀 S'inscrire</button>
          </div>
        </form>
      </transition>
      <div v-if="showSuccessModal" class="modal-overlay">
  <div class="modal-box">
    <h3>✅ Inscription réussie !</h3>
    <p>Vous allez être redirigé vers la page de connexion pour vous connecter.</p>
    <div class="loader"></div>
  </div>
</div>

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
      showSuccessModal: false,

      form: {
        nom: "",
        prenom: "",
        email: "",
        telephone: "+216",
        password: "",
        password_confirmation: "",
        role: "",
        terms: false,

      },
      errors: {
        nom: '',
        prenom: '',
        email: '',
        telephone: '',
        password: '',
        password_confirmation: '',
        role: '',
        terms: ''
      }
    };

  },
  created() {
    this.toggleEveryDisplay?.();
    this.toggleHideConfig?.();
    document.querySelector(".g-sidenav-show")?.classList.add("g-sidenav-hidden");
  },
  beforeUnmount() {
    this.toggleEveryDisplay?.();
    this.toggleHideConfig?.();
    document.querySelector(".g-sidenav-show")?.classList.remove("g-sidenav-hidden");
  },
  methods: {
    ...mapMutations(["toggleEveryDisplay", "toggleHideConfig"]),
    validateField(field) {
      const val = this.form[field];
      switch (field) {
        case 'nom':
        case 'prenom':
          this.errors[field] = val.length < 3 ? 'Minimum 3 caractères requis.' : '';
          break;
        case 'email':
          this.errors.email = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)
            ? 'Email invalide.' : '';
          break;
        case 'telephone':
          const num = val.replace('+216', '');
          this.errors.telephone = !/^[0-9]{8}$/.test(num)
            ? 'Numéro invalide (8 chiffres).' : '';
          break;
        case 'password':
          this.errors.password = val.length < 8 ? 'Minimum 8 caractères.' : '';
          break;
        case 'password_confirmation':
          this.errors.password_confirmation =
            val !== this.form.password ? 'Ne correspond pas au mot de passe.' : '';
          break;
        case 'role':
          this.errors.role = !val ? 'Rôle requis.' : '';
          break;
        case 'terms':
          this.errors.terms = !this.form.terms ? 'Obligatoire.' : '';
          break;
      }
    },
    nextStep() {
      this.validateField('nom');
      this.validateField('prenom');
      this.validateField('email');
      this.validateField('telephone');

      if (!this.errors.nom && !this.errors.prenom && !this.errors.email && !this.errors.telephone) {
        this.step = 2;
      }
    },
    submitForm() {
      this.validateField('password');
      this.validateField('password_confirmation');
      this.validateField('role');
      this.validateField('terms');

      const hasErrors = Object.values(this.errors).some(e => e !== '');
      if (hasErrors) {
        alert("Veuillez corriger les erreurs.");
        return;
      }

      axios.post('http://localhost:8000/api/register', this.form)
    .then(() => {
  this.showSuccessModal = true;
  setTimeout(() => {
    this.$router.push({ name: "Sign In" });
  }, 3000);
})

        .catch(error => {
          if (error.response && error.response.status === 422) {
            const errors = Object.values(error.response.data.errors).flat().join(', ');
            alert("❌ Erreur : " + errors);
          } else {
            alert("Erreur lors de l'inscription.");
          }
        });
    }
  }
};
</script>


<style scoped>


.error-msg {
  color: red;
  font-size: 0.85rem;
  margin-top: 4px;
}
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css");

.signup-page {
  background: #fffaf4;
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
}

.header {
  text-align: center;
  background: linear-gradient(135deg, #ff6b00, #ff9900);
  color: white;
  padding: 40px 20px;
  border-bottom-left-radius: 40px;
  border-bottom-right-radius: 40px;
  box-shadow: 0 6px 20px rgba(255, 107, 0, 0.2);
}

.header h1 {
  font-size: 2.4rem;
  margin-bottom: 8px;
}

.header p {
  font-size: 1.1rem;
  margin-bottom: 20px;
}

.form-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 20px;
}

.btn-exit {
  align-self: flex-start;
  margin-bottom: 20px;
  background: none;
  border: none;
  color: #ff6b00;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
}

/* Stepper */
.stepper {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}
.step {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #555;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.3s;
}
.step.active {
  background: #f97316;
  color: white;
  box-shadow: 0 0 10px rgba(249, 115, 22, 0.5);
}
.line {
  width: 40px;
  height: 2px;
  background: #e2e8f0;
  margin: 0 10px;
}

/* Formulaire */
.card {
  background: white;
  padding: 32px;
  border-radius: 16px;
  max-width: 520px;
  width: 100%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  animation: fadeInUp 0.6s ease forwards;
  opacity: 0;
}

.card h2 {
  color: #f97316;
  font-size: 1.4rem;
  text-align: center;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
  color: #444;
}

input,
select {
  width: 100%;
  padding: 12px;
  font-size: 0.95rem;
  border-radius: 10px;
  border: 1px solid #ddd;
  transition: 0.3s;
}

input:focus,
select:focus {
  outline: none;
  border-color: #f97316;
  box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
}

.checkbox {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
}

.checkbox input {
  margin-right: 8px;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 25px;
}

.btn-next,
.btn-back,
.submit-btn {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
}

.btn-next {
  background: #f97316;
  color: white;
}

.btn-next:hover,
.submit-btn:hover {
  background: #ea580c;
}

.btn-back {
  background: #e2e8f0;
  color: #333;
}

.submit-btn {
  background: linear-gradient(to right, #f97316, #fb923c);
  color: white;
  box-shadow: 0 4px 10px rgba(249, 115, 22, 0.3);
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes fadeInUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}


.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-box {
  background: white;
  padding: 30px 40px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  width: 90%;
}

.modal-box h3 {
  color: #16a34a;
  margin-bottom: 10px;
}

.modal-box p {
  color: #555;
  font-size: 1rem;
}

/* Petit loader simple */
.loader {
  margin-top: 15px;
  width: 24px;
  height: 24px;
  border: 3px solid #f97316;
  border-top: 3px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-inline: auto;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

</style>
