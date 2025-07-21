<template>
  <div
    class="w-auto h-auto collapse navbar-collapse max-height-vh-100 h-100"
    id="sidenav-collapse-main"
  >
    <ul class="navbar-nav">
      <li class="nav-item">
        <sidenav-collapse navText="Dashboard" :to="{ name: 'Dashboard' }">
          <template #icon>
            <shop />
          </template>
        </sidenav-collapse>





        <li class="nav-item">
        <sidenav-collapse navText="Users" :to="{ name: 'UsersList' }">
          <template #icon>
            <shop />
          </template>
        </sidenav-collapse>
      </li>

       <li class="nav-item">
        <sidenav-collapse navText="appells" :to="{ name: 'AppelsList' }">
          <template #icon>
            <shop />
          </template>
        </sidenav-collapse>
      </li>

      </li>

      

      <li class="mt-3 nav-item">
        <h6
          class="text-xs ps-4 text-uppercase font-weight-bolder opacity-6"
          :class="$store.state.isRTL ? 'me-4' : 'ms-2'"
        >
          PAGES
        </h6>
      </li>

      <li class="nav-item">
        <sidenav-collapse navText="Profile" :to="{ name: 'Profile' }">
          <template #icon>
            <customer-support />
          </template>
        </sidenav-collapse>
      </li>



      <li class="nav-item">
        <sidenav-collapse navText="Sign In" :to="{ name: 'Sign In' }">
          <template #icon>
            <document />
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse navText="Sign Up" :to="{ name: 'Sign Up' }">
          <template #icon>
            <spaceship />
          </template>
        </sidenav-collapse>
      </li>
    </ul>
  </div>
</template>

<script>
import SidenavCollapse from "./SidenavCollapse.vue";
import Shop from "../../components/Icon/Shop.vue";
import CustomerSupport from "../../components/Icon/CustomerSupport.vue";
import Document from "../../components/Icon/Document.vue";
import Spaceship from "../../components/Icon/Spaceship.vue";

export default {
  name: "SidenavList",
  components: {
    SidenavCollapse,
    Shop,
    CustomerSupport,
    Document,
    Spaceship,
  },
};
</script>
