import { createRouter, createWebHashHistory } from "vue-router";
import Dashboard from "../views/Dashboard.vue";
import Tables from "../views/Tables.vue";
import Billing from "../views/Billing.vue";
import Profile from "../views/Profile.vue";
import SignUp from "../views/SignUp.vue";
import SignIn from "../views/SignIn.vue";
import ForgotPassword from "../views/ForgotPassword.vue";
import ResetPassword from "../views/ResetPassword.vue";
import user from "../views/Gestion_users/user.vue";
import AppelsList from "../views/Gestion_appelle_offres/AppelsList.vue";
const routes = [
  {
    path: "/",
    name: "/",
    redirect: "/dashboard",
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: Dashboard,
  },
  {
    path: "/tables",
    name: "Tables",
    component: Tables,
  },
  {
    path: "/billing",
    name: "Billing",
    component: Billing,
  },

  {
    path: "/profile",
    name: "Profile",
    component: Profile,
  },
 
  {
    path: "/sign-in",
    name: "Sign In",
    component: SignIn,
  },
  {
    path: "/sign-up",
    name: "Sign Up",
    component: SignUp,
  },
    { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword },

  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },

   {
    path: '/admin/users',
    name: 'UsersList',
    component: user,
    //meta: { requiresAuth: true }, // protège par login si tu veux
  },
  {
  path: '/admin/appels',
  name: 'AppelsList',
  component: AppelsList,
  meta: { requiresAuth: true }
}

];

const router = createRouter({
  history: createWebHashHistory(), // plus besoin de BASE_URL
  routes,
})

export default router;
