<template>
  <div class="forgot-modal-overlay">
    <div class="card forgot-modal-box p-4 shadow rounded-4">
      <button class="btn-close-modal" @click="$emit('close')">
        <i class="fa fa-times"></i>
      </button>

      <h4 class="mb-3 text-center">🔐 Réinitialiser le mot de passe</h4>
      <p class="text-muted text-center mb-4">
        Entrez votre adresse e-mail pour recevoir un lien de réinitialisation.
      </p>

      <form @submit.prevent="sendResetLink">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input
            v-model="email"
            type="email"
            class="form-control rounded-pill"
            required
            placeholder="vous@exemple.com"
          />
        </div>
        <button class="btn btn-orange w-100 rounded-pill" type="submit">
          📩 Envoyer le lien
        </button>
      </form>

      <div v-if="successMessage" class="alert alert-orange mt-3 text-center" role="alert">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="alert alert-orange border-danger mt-3 text-center" role="alert">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/Http/api';

export default {
  name: 'ForgotPassword',
  data() {
    return {
      email: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  methods: {
    async sendResetLink() {
      try {
        await api.post('/forgot-password', { email: this.email });
        this.successMessage = '✅ Lien envoyé avec succès !';
        this.errorMessage = '';
        this.email = '';

        setTimeout(() => {
          this.$emit('close');
        }, 3000);
      } catch (err) {
        this.errorMessage = err.response?.data?.message || '❌ Une erreur est survenue.';
        this.successMessage = '';
      }
    }
  }
};
</script>

<style scoped>
.forgot-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 999;
  background: rgba(0, 0, 0, 0.4);
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.forgot-modal-box {
  background: white;
  width: 90%;
  max-width: 420px;
  position: relative;
  animation: fadeIn 0.3s ease;
}

.btn-close-modal {
  position: absolute;
  top: 12px;
  right: 12px;
  border: none;
  background: transparent;
  font-size: 1.2rem;
  color: #888;
  cursor: pointer;
}

.btn-orange {
  background-color: #f97316;
  color: white;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.btn-orange:hover {
  background-color: #ea580c;
}

.alert-orange {
  background-color: #fff7ed;
  border: 1px solid #f97316;
  color: #b45309;
  border-radius: 8px;
  padding: 12px;
  font-size: 0.95rem;
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
