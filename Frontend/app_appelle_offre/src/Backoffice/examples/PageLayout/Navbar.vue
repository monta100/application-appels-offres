<template>
  <nav
    class="shadow-none navbar navbar-main navbar-expand-lg border-radius-xl"
    v-bind="$attrs"
    id="navbarBlur"
    data-scroll="true"
  >
    <div class="px-3 py-1 container-fluid">
      <breadcrumbs :currentPage="currentRouteName" :textWhite="textWhite" />

      <div
        class="mt-2 collapse navbar-collapse mt-sm-0 me-md-0 me-sm-4"
        :class="this.$store.state.isRTL ? 'px-0' : 'me-sm-4'"
        id="navbar"
      >
        <div
          class="pe-md-3 d-flex align-items-center"
          :class="this.$store.state.isRTL ? 'me-md-auto' : 'ms-md-auto'"
        >
          <div class="input-group">
            <span class="input-group-text text-body">
              <i class="fas fa-search" aria-hidden="true"></i>
            </span>
            <input
              type="text"
              class="form-control"
              :placeholder="
                this.$store.state.isRTL ? 'أكتب هنا...' : 'Type here...'
              "
            />
          </div>
        </div>

        <ul class="navbar-nav justify-content-end">

          <!-- 🔒 SI NON CONNECTÉ -->
          <li class="nav-item d-flex align-items-center" v-if="!user">
            <router-link
              :to="{ name: 'Sign In' }"
              class="px-0 nav-link font-weight-bold text-dark"
            >
              <i class="fa fa-user me-1"></i>
              <span class="d-sm-inline d-none">Sign In</span>
            </router-link>
          </li>

          <!-- 🔓 SI CONNECTÉ -->
          <li class="nav-item dropdown d-flex align-items-center" v-else>
            <a
              class="nav-link dropdown-toggle d-flex align-items-center gap-2"
              href="#"
              id="userMenu"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="fa fa-user-circle fa-lg text-dark"></i>
<span class="fw-semibold text-dark">{{ user.prenom }} {{ user.nom }}</span>
              <i class="fa fa-chevron-down small text-muted"></i>
            </a>

            <ul
              class="dropdown-menu dropdown-menu-end shadow border-0 p-2 rounded-3"
              aria-labelledby="userMenu"
              style="min-width: 180px;"
            >
              <li>
                <router-link
                  class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2"
                  :to="{ name: 'Profile' }"
                >
                  <i class="fa fa-user text-secondary"></i>
                  Mon profil
                </router-link>
              </li>
              <li>
                <a
                  href="#"
                  class="dropdown-item rounded-2 d-flex align-items-center gap-2 py-2 text-danger"
                  @click.prevent="logout"
                >
                  <i class="fa fa-sign-out-alt"></i>
                  Se déconnecter
                </a>
              </li>
            </ul>
          </li>

          <!-- Sidebar + Config (non modifiés) -->
          <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
            <a
              href="#"
              @click="toggleSidebar"
              class="p-0 nav-link text-body"
              id="iconNavbarSidenav"
            >
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
              </div>
            </a>
          </li>
          <li class="px-3 nav-item d-flex align-items-center">
            <a
              class="p-0 nav-link"
              @click="toggleConfigurator"
              :class="textWhite ? textWhite : 'text-body'"
            >
              <i class="cursor-pointer fa fa-cog fixed-plugin-button-nav"></i>
            </a>
          </li>

        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import Breadcrumbs from "../Breadcrumbs.vue";
import { mapMutations, mapActions } from "vuex";

export default {
  name: "navbar",
  props: ["minNav", "textWhite"],
  components: {
    Breadcrumbs,
  },
computed: {
  currentRouteName() {
    return this.$route.name;
  },
  user() {
      console.log("USER in navbar:", this.$store.getters["auth/user"]);

    return this.$store.getters["auth/user"];
  },
},

  methods: {
    ...mapMutations(["navbarMinimize", "toggleConfigurator"]),
    ...mapActions(["toggleSidebarColor"]),

    toggleSidebar() {
      this.toggleSidebarColor("bg-white");
      this.navbarMinimize();
    },

    logout() {
      this.$store.dispatch("auth/logout");
      this.$router.push({ name: "Sign In" });
    },
  },
  updated() {
    const navbar = document.getElementById("navbarBlur");
    window.addEventListener("scroll", () => {
      if (window.scrollY > 10 && this.$store.state.isNavFixed) {
        navbar.classList.add("blur", "position-sticky", "shadow-blur");
      } else {
        navbar.classList.remove("blur", "position-sticky", "shadow-blur");
      }
    });
  },
};
</script>
