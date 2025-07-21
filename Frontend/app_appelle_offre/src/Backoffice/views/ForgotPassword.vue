<template>
  <div class="container d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card p-4 shadow rounded-4" style="min-width: 400px;">
      <h4 class="mb-3 text-center">Reset Password</h4>
      <p class="text-muted text-center mb-4">
        Enter your email address to receive a password reset link.
      </p>

      <form @submit.prevent="sendResetLink">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input
            v-model="email"
            type="email"
            class="form-control rounded-pill"
            required
            placeholder="you@example.com"
          />
        </div>
        <button class="btn btn-success w-100 rounded-pill" type="submit">
          Send Reset Link
        </button>
      </form>

      <!-- ✅ Notification verte si succès -->
      <div v-if="successMessage" class="alert alert-success mt-3 text-center" role="alert">
        {{ successMessage }}
      </div>

      <!-- ❌ Notification rouge si erreur -->
      <div v-if="errorMessage" class="alert alert-danger mt-3 text-center" role="alert">
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
        this.successMessage = '✅ Le lien de réinitialisation a été envoyé avec succès.';
        this.errorMessage = '';

        // Optionnel : vider le champ
        this.email = '';

        // Optionnel : rediriger après 3 secondes
        setTimeout(() => {
          this.$router.push({ name: 'Sign In' });
        }, 3000);
      } catch (err) {
        this.errorMessage = err.response?.data?.message || '❌ Une erreur est survenue.';
        this.successMessage = '';
      }
    }
  }
};
</script>
