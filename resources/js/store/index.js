import { createStore } from 'vuex';

const store = createStore({
  state() {
    return {
      user: null
    };
  },
  mutations: {
    SET_USER(state, userData) {
      state.user = userData;
    }
  },
  actions: {
    updateUser({ commit }, userData) {
      commit('SET_USER', userData);
    },
  },
  getters: {
    isAuthenticated(state) {
      return !!state.user;
    },
    user(state) {
      return state.user;
    }
  }
});

export default store;
