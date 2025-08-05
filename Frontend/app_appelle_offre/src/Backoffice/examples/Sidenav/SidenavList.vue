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
