<template>
  <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="row shadow-lg rounded-4 overflow-hidden" style="max-width: 960px; width: 100%;">
      <div class="col-md-6 bg-white p-5">
        <h3 class="text-success fw-bold mb-2">Welcome back</h3>
        <p class="text-muted mb-4">Please enter your email and password to sign in</p>

        <form @submit.prevent="login">
          <div class="mb-3">
            <label>Email</label>
            <input v-model="form.email" type="email" class="form-control rounded-pill" placeholder="Email" />
          </div>

          <div class="mb-3">
            <label>Password</label>
            <input v-model="form.password" type="password" class="form-control rounded-pill" placeholder="Password" />
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" v-model="form.remember" id="rememberMe" />
            <label class="form-check-label" for="rememberMe">Remember me</label>
          </div>

          <button type="submit" class="btn btn-success w-100 rounded-pill">Sign in</button>
        </form>

        <p class="mt-3 text-center text-muted">
          Don't have an account?
          <router-link :to="{ name: 'Sign Up' }" class="text-success fw-bold">Sign up</router-link>
        </p>
      </div>

      <div class="col-md-6 d-none d-md-block" :style="{
        backgroundImage: `url(${bgImage})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center'
      }">
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/Http/api';
import { mapMutations } from 'vuex';
import loginBg from '@/Backoffice/assets/img/logos/login-bg.png';

const body = document.getElementsByTagName("body")[0];

export default {
  name: "SignIn",
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      },
    bgImage: loginBg // ✅ Ceci est la bonne affectation
    };
  },
  created() {
    this.toggleEveryDisplay();
    this.toggleHideConfig();
    body.classList.remove("bg-gray-100");
  },
  beforeUnmount() {
    this.toggleEveryDisplay();
    this.toggleHideConfig();
    body.classList.add("bg-gray-100");
  },
  methods: {
    ...mapMutations(["toggleEveryDisplay", "toggleHideConfig"]),
    async login() {
      try {
        const response = await api.post('/login', {
          email: this.form.email,
          password: this.form.password
        });

        localStorage.setItem('token', response.data.access_token);
        this.$store.commit('auth/setUser', response.data.user);

        alert("Connexion réussie !");
        this.$router.push({ name: "Dashboard" });
      } catch (error) {
        if (error.response && error.response.status === 422) {
          alert("Erreur de validation : " + JSON.stringify(error.response.data.errors));
        } else {
          alert("Erreur lors de la connexion !");
          console.error(error);
        }
      }
    }
  }
};
</script>

<style scoped>
body {
  font-family: 'Poppins', sans-serif;
}
</style>