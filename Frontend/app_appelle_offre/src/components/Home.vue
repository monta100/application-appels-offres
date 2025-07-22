<template>
  <div>
    <Navbar />

    <section class="hero-section">
      <div class="container text-center py-5">
        <h1 class="display-4 fw-bold text-uppercase mb-4">
          Bienvenue dans l'application d'appel d'offre
        </h1>
        <p class="lead">
          Consultez, postulez ou gérez vos appels d'offres en toute simplicité !
        </p>
        <router-link to="/register" class="btn btn-warning mt-3">Créer un compte</router-link>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
</script>

<style scoped>
.hero-section {
  background-color: #f8f9fa;
  padding: 80px 0;
}
</style>
