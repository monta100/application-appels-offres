const state = {
  user: null,
};

const mutations = {
  setUser(state, user) {
    state.user = user;
  },
  clearUser(state) {
    state.user = null;
  },
};

const actions = {
  login({ commit }, userData) {

      localStorage.setItem('user', JSON.stringify(userData)); // 🔥

    commit("setUser", userData);
  },
  logout({ commit }) {
    commit("clearUser");
  },
};

const getters = {
  isAuthenticated: (state) => !!state.user,
  user: (state) => state.user,
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
};