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
               <li> <router-link @click="goToOffreCl" class="login" to="/OffreCl">Appelle offre</router-link> </li>
             <li>  <router-link @click="goToSoumission" class="login" to="/ mes_soumission">Consulter mes soumissions </router-link> </li> 
             <li>  <router-link @click="goToAssist" class="login" to="/Assistant">Assistance </router-link> </li> 



              <li>
                <a href="#">gestion soumissions</a>
                <ul class="sub-menu">
                  <li><router-link @click =" goTosoumissionchosiis"to="/">Les soumissions choisis</router-link></li>
                  <li><router-link to="/blog-details">Blog Details</router-link></li>
                </ul>
              </li>
                  <li><router-link @click =" goTochat"to="/">chat</router-link></li>
              <li>
                <a href="#">PAGES</a>
                <ul class="sub-menu">
                  <li><router-link to="/job-search">Job Search</router-link></li>
                  <li><router-link to="/job-single">Job Single</router-link></li>
                  <li><router-link to="/pricing">Pricing Plan</router-link></li>
                  <li><router-link to="/elements">Elements</router-link></li>
                </ul>
              </li>

              <!-- Auth buttons -->
              <li class="menu-btn" v-if="!user">
                <router-link @click="goToSignin" class="login" to="/login">LOG IN</router-link>
                <router-link class="template-btn" to="/register">SIGN UP</router-link>
              </li>

              <!-- User connected dropdown -->
         <li class="nav-item dropdown" v-else>
  <button
    class="btn btn-sm dropdown-toggle"
    type="button"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    style="background-color: #ff6b00; color: white; font-weight: 500; border-radius: 10px; padding: 6px 12px;"
  >
    <i class="fa fa-user me-1"></i> {{ user.prenom }} {{ user.nom }}
  </button>
  <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm animate__animated animate__fadeIn">
    <li>
      <router-link class="dropdown-item" to="/backoffice.html#/profile">
        <i class="fa fa-id-card me-2"></i> Profil
      </router-link>
    </li>
    <li>
      <button class="dropdown-item text-danger" @click="logout">
        <i class="fa fa-sign-out-alt me-2"></i> Se déconnecter
      </button>
    </li>
  </ul>
</li>

            </ul>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

const store = useStore()
const router = useRouter()

const user = computed(() => store.state.auth.user)

const goToSignin = () => {
  window.location.assign('/backoffice.html#/sign-in')
}
const goToProfil = () => {
  window.location.assign('/backoffice.html#/profile')
}
const goToOffreCl =()=>

{

  window.location.assign('/offreCl')
}
const goToSoumission = () =>
{

  window.location.assign('/mes_soumission')
}
    const goTosoumissionchosiis = ()  =>

{
  window.location.assign('/Soumission_chosi')
}
 const goTochat =()=>
 {
  window.location.assign('/chat')
 }

 const goToAssist = ( )=>
 {window.location.assign('/Assistant')}


const logout = () => {
  store.dispatch('auth/logout')
  localStorage.removeItem('token')
    window.location.href = '/backoffice.html#/sign-in'; // fallback
}

onMounted(() => {
  // jQuery stuff (si nécessaire)
  if (typeof window.$ === 'function') {
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
    console.warn('jQuery non chargé.')
  }
})
</script>

<style scoped>
/* Tu peux ajouter des styles si besoin ici */
</style>
