<template>
  <header class="header-area main-header">
    <div class="container">
      <div class="row">
        <!-- Logo -->
        <div class="col-lg-2">
          <div class="logo-area">
            <router-link to="/">
              <img src="/assets/images/logo.png" alt="logo" />
            </router-link>
          </div>
        </div>

        <!-- Navigation -->
        <div class="col-lg-10">
          <div class="custom-navbar">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <div class="main-menu">
            <ul class="nav-menu">
              <li><router-link to="/" exact>HOME</router-link></li>
              <li><router-link to="/about">ABOUT US</router-link></li>
              <li><router-link to="/job-category">CATEGORY</router-link></li>
              <li>
                <a href="#">BLOG</a>
                <ul class="sub-menu">
                  <li><router-link to="/blog-home">Blog Home</router-link></li>
                  <li><router-link to="/blog-details">Blog Details</router-link></li>
                </ul>
              </li>
              <li><router-link to="/contact">CONTACT</router-link></li>
              <li>
                <a href="#">PAGES</a>
                <ul class="sub-menu">
                  <li><router-link to="/job-search">Job Search</router-link></li>
                  <li><router-link to="/job-single">Job Single</router-link></li>
                  <li><router-link to="/pricing">Pricing Plan</router-link></li>
                  <li><router-link to="/elements">Elements</router-link></li>
                </ul>
              </li>
              <li class="menu-btn">
                <router-link class="login" to="/login">LOG IN</router-link>
                <router-link class="template-btn" to="/register">SIGN UP</router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  // Vérifie que jQuery est bien chargé
  if (typeof window.$ === 'function') {
    // Re-activation des composants JS
    $('.employee-slider').owlCarousel({
      loop: true,
      margin: 20,
      autoplay: true,
      autoplayTimeout: 2000,
      autoplayHoverPause: true,
      nav: false,
      dots: true,
      responsive: {
        0: { items: 1 },
        576: { items: 1 },
        768: { items: 1 },
        992: { items: 2 }
      }
    })

    $('select').niceSelect()
    new WOW().init()
  } else {
    console.warn('jQuery non chargé. Les animations ne fonctionneront pas.')
  }
})
</script>

<style scoped>
/* Tu peux ajouter des styles si besoin ici */
</style>
