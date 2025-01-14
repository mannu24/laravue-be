import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isAuthenticated: false,
    user: null,
    token: null,
  }),
  actions: {
    setAuthData(token, user) {
      this.token = token;
      this.user = user;
      this.isAuthenticated = true;
    },
    clearAuthData() {
      this.token = null;
      this.user = null;
      this.isAuthenticated = false;
    },
  },
  persist: {
    enabled: true,
    strategies: [
      {
        key: 'auth',
        storage: localStorage,
      },
    ],
  },
});
