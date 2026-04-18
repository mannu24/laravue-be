import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isAuthenticated: false,
    user: null,
    token: null,
    config: null,
    // OTP flow state (persisted so page refresh doesn't break it)
    otpEmail: null,
    otpSentAt: null, // timestamp when OTP was sent
    otpCooldownSeconds: 120, // 2 minutes between resends
  }),
  getters: {
    /**
     * Seconds remaining before resend is allowed.
     * Returns 0 if resend is available.
     */
    otpResendRemaining() {
      if (!this.otpSentAt) return 0;
      const elapsed = Math.floor((Date.now() - this.otpSentAt) / 1000);
      const remaining = this.otpCooldownSeconds - elapsed;
      return remaining > 0 ? remaining : 0;
    },
    /**
     * Whether we're in the OTP verification step.
     */
    isOtpPending() {
      if (!this.otpEmail || !this.otpSentAt) return false;
      // OTP expires after 5 minutes on backend — auto-expire the flow after 5 min
      const elapsed = (Date.now() - this.otpSentAt) / 1000;
      return elapsed < 300; // 5 minutes
    },
  },
  actions: {
    setAuthData(token, user) {
      this.token = token;
      this.user = user;
      this.config = { headers: { Authorization: `Bearer ${token}` } };
      this.isAuthenticated = true;
      // Clear OTP state on successful login
      this.clearOtpState();
    },
    clearAuthData() {
      this.token = null;
      this.user = null;
      this.config = null;
      this.isAuthenticated = false;
      this.clearOtpState();
    },
    setOtpSent(email) {
      this.otpEmail = email;
      this.otpSentAt = Date.now();
    },
    clearOtpState() {
      this.otpEmail = null;
      this.otpSentAt = null;
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
