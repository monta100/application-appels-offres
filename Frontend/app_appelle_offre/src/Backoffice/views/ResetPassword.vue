<template>
  <form @submit.prevent="resetPassword" class="reset-form">
    <h4 class="form-title">🔒 Réinitialiser votre mot de passe</h4>

    <div class="form-group">
      <label>Email</label>
      <input v-model="email" type="email" class="form-control" required />
    </div>

    <div class="form-group">
      <label>Nouveau mot de passe</label>
      <input v-model="password" type="password" class="form-control" required />
    </div>

    <div class="form-group">
      <label>Confirmation</label>
      <input v-model="password_confirmation" type="password" class="form-control" required />
    </div>

    <button class="btn-orange w-100" type="submit">✔️ Réinitialiser</button>
  </form>
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
created() {
  // Masquer les éléments globaux (navbar, sidenav, footer, etc.)
  this.$store.commit('toggleEveryDisplay', false);
  this.$store.commit('toggleHideConfig', true);
},
beforeUnmount() {
  // Réactiver les éléments globaux à la sortie du composant
  this.$store.commit('toggleEveryDisplay', true);
  this.$store.commit('toggleHideConfig', false);
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
        alert("✅ Mot de passe mis à jour !");
this.$router.replace({ name: 'Home' });
      } catch (err) {
      }
    }
  }
};

</script>

<style scoped>
.reset-form {
  max-width: 420px;
  margin: 80px auto;
  padding: 30px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
}

.form-title {
  text-align: center;
  color: #f97316;
  margin-bottom: 20px;
  font-weight: bold;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  font-weight: 600;
  color: #333;
  display: block;
  margin-bottom: 6px;
}

.form-control {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 10px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #f97316;
  box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
}

.btn-orange {
  background: linear-gradient(to right, #f97316, #fb923c);
  color: white;
  font-weight: bold;
  padding: 12px;
  border: none;
  border-radius: 10px;
  transition: background 0.3s ease;
}

.btn-orange:hover {
  background: #ea580c;
}
</style>
