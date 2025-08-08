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
<ul class="nav-menu d-flex align-items-center">
  <li><router-link to="/">HOME</router-link></li>

  <!-- Participant uniquement -->
  <li v-if="role === 'participant'">
    <router-link to="/OffreCl">Appel d'offre</router-link>
  </li>
  <li v-if="role === 'participant'">
    <router-link to="/mes_soumission">Consulter mes soumissions</router-link>
  </li>

  <!-- Représentant uniquement -->
  <li v-if="role === 'representant'">
    <router-link to="/Appelles">Gestion des appels d'offres</router-link>
  </li>
  <li v-if="role === 'representant'">
    <router-link to="/Soumission_chosi">Les soumissions choisies</router-link>
  </li>

  <!-- Les deux rôles -->
  <li v-if="role === 'participant' || role === 'representant'">
    <router-link to="/chat">Chat</router-link>
  </li>

  <li v-if="role === 'participant' || role === 'representant'">
    <router-link to="/Assistant">Assistance</router-link>
  </li>

            <li v-if="user">
    <Notifications />
  </li>
          
              <!-- Auth buttons -->
              <li class="menu-btn" v-if="!user">
                <router-link @click="goToSignin" class="login" to="/login">LOG IN</router-link>
                <router-link class="template-btn" to="/register">SIGN UP</router-link>
              </li>
<li class="nav-item dropdown" v-else>
  <button
    class="btn d-flex align-items-center gap-2 px-2 py-1 dropdown-toggle"
    type="button"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    style="background: linear-gradient(135deg, #ff6b00, #ff9100); color: white; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); font-weight: 600;"
  >
    <img
      :src="getAvatarUrl(user.avatar)"
      alt="Avatar"
      class="rounded-circle border"
      width="34"
      height="34"
      style="object-fit: cover;"
    />
    <div class="d-flex flex-column align-items-start lh-sm text-start">
      <span>{{ user.prenom }} {{ user.nom }}</span>
      <small style="font-size: 0.7rem; opacity: 0.85;">{{ user.role }}</small>
    </div>
    <i class="fa fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
  </button>

  <ul class="dropdown-menu dropdown-menu-end mt-2 shadow border-0 animate__animated animate__fadeIn">
    <li>
      <router-link to="/profil" class="dropdown-item d-flex align-items-center gap-2">
        <i class="fa fa-user-circle text-primary"></i> Mon Profil
      </router-link>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
      <button class="dropdown-item d-flex align-items-center gap-2 text-danger" @click="logout">
        <i class="fa fa-sign-out-alt"></i> Se déconnecter
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
import Notifications from './notif/Notifications.vue'
const store = useStore()
const router = useRouter()

const role = computed(() => user.value?.role)

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

 const goToAppelles =() =>
 
 
 {

  window.location.assign('/appelles')
 }

const defaultAvatar = '/Backoffice/assets/img/default-avatar.jpg';

 const getAvatarUrl = (avatarPath) => {
  return avatarPath ? `http://localhost:8000/storage/${avatarPath}` : defaultAvatar;
};
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
