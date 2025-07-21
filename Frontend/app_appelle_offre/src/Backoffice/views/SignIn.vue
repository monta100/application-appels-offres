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

          <div class="mb-3 text-end">
  <router-link :to="{ name: 'ForgotPassword' }" class="text-muted small text-decoration-none">
    Forgot your password?
  </router-link>
</div>


          <button type="submit" class="btn btn-success w-100 rounded-pill">Sign in</button>
         <div
  v-if="errorMessage"
  class="alert-custom d-flex align-items-center justify-content-center gap-2"
>
  <i class="fas fa-circle-exclamation"></i>
  <span>{{ errorMessage }}</span>
</div>



          <div class="mt-3 text-center">
  <button @click="redirectToGoogle" class="btn btn-outline-dark rounded-pill d-flex align-items-center justify-content-center w-100">
    <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" style="width: 20px; margin-right: 8px" />
    Sign in with Google
  </button>
</div>

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
          errorMessage: '', // 💬 message d'erreur visible dans la page

      bgImage: loginBg
    };
  },
  created() {
    this.toggleEveryDisplay();
    this.toggleHideConfig();
    body.classList.remove("bg-gray-100");

    // 🔥 Si on revient de Google avec le token dans l'URL
    const googleToken = this.$route.query.google_token;
    if (googleToken) {
      localStorage.setItem('token', googleToken);

api.get('/user', {
  headers: {
    Authorization: `Bearer ${googleToken}`
  }
})
.then(res => {
  this.$store.commit('auth/setUser', res.data); // 🔁 remplace res.data.user par res.data
  this.$router.push({ name: 'Dashboard' });
})
.catch(() => {
  alert("Échec lors de la récupération du profil Google.");
});

    }
  },
  beforeUnmount() {
    this.toggleEveryDisplay();
    this.toggleHideConfig();
    body.classList.add("bg-gray-100");
  },
  methods: {
    ...mapMutations(["toggleEveryDisplay", "toggleHideConfig"]),
    
  async login() {
  this.errorMessage = ''; // reset avant chaque tentative
  try {
    const response = await api.post('/login', {
      email: this.form.email,
      password: this.form.password
    });

    localStorage.setItem('token', response.data.access_token);
    this.$store.commit('auth/setUser', response.data.user);

    this.$router.push({ name: "Dashboard" });
  } catch (error) {
    if (error.response?.status === 403) {
      this.errorMessage = "Votre compte est désactivé. Veuillez contacter le service d'administration.";
    } else if (error.response?.status === 401) {
      this.errorMessage = "Email ou mot de passe incorrect.";
    } else if (error.response?.status === 422) {
      this.errorMessage = "Champs invalides. Vérifiez vos données.";
    } else {
      this.errorMessage = "Une erreur est survenue lors de la connexion.";
    }

    console.error(error);
  }
},


    redirectToGoogle() {
      // 👇 Redirection vers ton backend Laravel qui gère l'auth Google
      window.location.href = "http://localhost:8000/api/auth/google";
    }
  }




  
};



</script>


<style scoped>
body {
  font-family: 'Poppins', sans-serif;
}

.alert-custom {
  background: linear-gradient(135deg, #ff4e50, #f00000);
  color: #fff;
  font-weight: 500;
  padding: 12px 24px;
  border-radius: 40px;
  box-shadow: 0 4px 12px rgba(255, 0, 0, 0.3);
  font-size: 1rem;
  margin-top: 20px;
  text-align: center;
  animation: fadeIn 0.5s ease;
}

.alert-custom i {
  font-size: 1.2rem;
}

</style>