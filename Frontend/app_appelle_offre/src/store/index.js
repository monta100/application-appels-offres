import { createStore } from 'vuex';
import auth from '../Backoffice/store/auth'; // importe ton module auth

const store = createStore({
  modules: {
    auth, // tu peux appeler aussi 'auth: auth' si tu veux
  },
});

export default store;
