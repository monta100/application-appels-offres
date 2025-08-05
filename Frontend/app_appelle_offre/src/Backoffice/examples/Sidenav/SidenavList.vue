<template>
  <div class="w-auto h-auto collapse navbar-collapse max-height-vh-100 h-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">

      <!-- Dashboard -->
      <li class="nav-item">
        <sidenav-collapse navText="Dashboard" :to="{ name: 'Dashboard' }">
          <template #icon>
            <font-awesome-icon icon="chart-line" />
          </template>
        </sidenav-collapse>
      </li>

      <!-- Users -->
      <li class="nav-item">
        <sidenav-collapse navText="Utilisateurs" :to="{ name: 'UsersList' }">
          <template #icon>
            <font-awesome-icon icon="users" />
          </template>
        </sidenav-collapse>
      </li>

      <!-- Appels d'offres -->
      <li class="nav-item">
        <sidenav-collapse navText="Appels d'offres" :to="{ name: 'AppelsList' }">
          <template #icon>
            <font-awesome-icon icon="file-alt" />
          </template>
        </sidenav-collapse>
      </li>

    </ul>
  </div>
</template>

<script>
import SidenavCollapse from "./SidenavCollapse.vue";

export default {
  name: "SidenavList",
  components: {
    SidenavCollapse
  }
};
</script>
