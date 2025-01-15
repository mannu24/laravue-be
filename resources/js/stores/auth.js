import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isAuthenticated: false,
    user: null,
    token: null,
    config: null,
  }),
  actions: {
    setAuthData(token, user) {
      this.token = token;
      this.user = user;
      this.config = { headers: { Authorization: `Bearer ${token}` } };
      this.isAuthenticated = true;
    },
    clearAuthData() {
      this.token = null;
      this.user = null;
      this.config = null;
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
