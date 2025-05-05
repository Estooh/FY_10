import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', {
  state: () => ({
    user: {
    },
  }),
  getters: {

  },
  actions: {
    setUser(data) {
      this.user = data;
    },

  },
});
