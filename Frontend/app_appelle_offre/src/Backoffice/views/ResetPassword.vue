<template>
  <div class="container d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card p-4 shadow rounded-4" style="min-width: 400px;">
      <h4 class="mb-3 text-center">Change your password</h4>
      <form @submit.prevent="resetPassword">
        <div class="mb-3">
          <label>Email</label>
          <input v-model="email" type="email" class="form-control rounded-pill" required />
        </div>
        <div class="mb-3">
          <label>New Password</label>
          <input v-model="password" type="password" class="form-control rounded-pill" required />
        </div>
        <div class="mb-3">
          <label>Confirm Password</label>
          <input v-model="password_confirmation" type="password" class="form-control rounded-pill" required />
        </div>
        <button class="btn btn-success w-100 rounded-pill" type="submit">Reset Password</button>
      </form>
    </div>
  </div>
</template>

<script>
import api from '@/Http/api';

export default {
  name: 'ResetPassword',
  data() {
    return {
      email: this.$route.query.email || '',
      token: this.$route.query.token || '',
      password: '',
      password_confirmation: '',
    };
  },
  methods: {
    async resetPassword() {
      try {
        await api.post('/reset-password', {
          email: this.email,
          token: this.token,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });
        alert("Votre mot de passe a été mis à jour !");
        this.$router.push({ name: 'SignIn' });
      } catch (err) {
        alert(err.response?.data?.message || "Erreur lors du changement de mot de passe.");
      }
    },
  },
};
</script>
