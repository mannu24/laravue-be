import { defineStore } from 'pinia'
import { useAuthStore } from './auth'
import axios from 'axios'

export const usePortfolioStore = defineStore('portfolio', {
  state: () => ({
    portfolio: null,
    subscription: null,
    plans: [],
    templates: [],
    loading: false,
    saving: false,
    error: null,
  }),

  getters: {
    hasPortfolio: (state) => !!state.portfolio,
    isPublished: (state) => state.portfolio?.is_published ?? false,
    hasActiveSubscription: (state) => !!state.subscription,
    currentPlan: (state) => state.subscription?.plan ?? null,
    subdomainUrl: (state) => {
      if (!state.portfolio) return null
      const domain = import.meta.env.VITE_PORTFOLIO_DOMAIN || 'laravue.in'
      const scheme = window.location.protocol
      return `${scheme}//${state.portfolio.subdomain}.${domain}`
    },
  },

  actions: {
    async fetchPortfolio() {
      const auth = useAuthStore()
      if (!auth.isAuthenticated) return

      this.loading = true
      this.error = null
      try {
        const res = await axios.get('/api/v1/portfolio', auth.config)
        if (res.data.status === 'success' && res.data.data) {
          this.portfolio = res.data.data.portfolio
          this.subscription = res.data.data.subscription
        }
      } catch (e) {
        if (e.response?.status !== 404) {
          this.error = e.response?.data?.message || 'Failed to load portfolio'
        }
      } finally {
        this.loading = false
      }
    },

    async createPortfolio(subdomain, templateSlug = 'minimal') {
      const auth = useAuthStore()
      this.saving = true
      this.error = null
      try {
        const res = await axios.post('/api/v1/portfolio', {
          subdomain,
          template_slug: templateSlug,
        }, auth.config)
        if (res.data.status === 'success') {
          this.portfolio = res.data.data.portfolio
        }
        return res.data
      } catch (e) {
        this.error = e.response?.data?.message || 'Failed to create portfolio'
        throw e
      } finally {
        this.saving = false
      }
    },

    async updatePortfolio(data) {
      const auth = useAuthStore()
      this.saving = true
      try {
        const res = await axios.put('/api/v1/portfolio', data, auth.config)
        if (res.data.status === 'success') {
          this.portfolio = { ...this.portfolio, ...res.data.data }
        }
        return res.data
      } catch (e) {
        throw e
      } finally {
        this.saving = false
      }
    },

    async updateSection(section, data) {
      const auth = useAuthStore()
      this.saving = true
      try {
        const res = await axios.put(`/api/v1/portfolio/${section}`, data, auth.config)
        if (res.data.status === 'success' && this.portfolio) {
          // Map section name to portfolio key
          const keyMap = {
            'social-links': 'social_links',
            'skills': 'skills',
            'experience': 'experiences',
            'education': 'educations',
            'projects': 'projects',
            'testimonials': 'testimonials',
            'custom-sections': 'custom_sections',
          }
          const key = keyMap[section]
          if (key) {
            this.portfolio[key] = res.data.data
          }
        }
        return res.data
      } catch (e) {
        throw e
      } finally {
        this.saving = false
      }
    },

    async updateTemplate(templateSlug) {
      const auth = useAuthStore()
      try {
        const res = await axios.put('/api/v1/portfolio/template', {
          template_slug: templateSlug,
        }, auth.config)
        if (res.data.status === 'success') {
          this.portfolio = { ...this.portfolio, template_slug: templateSlug }
        }
        return res.data
      } catch (e) {
        throw e
      }
    },

    async publish() {
      const auth = useAuthStore()
      try {
        const res = await axios.post('/api/v1/portfolio/publish', {}, auth.config)
        if (res.data.status === 'success') {
          this.portfolio = { ...this.portfolio, is_published: true }
        }
        return res.data
      } catch (e) {
        throw e
      }
    },

    async unpublish() {
      const auth = useAuthStore()
      try {
        const res = await axios.post('/api/v1/portfolio/unpublish', {}, auth.config)
        if (res.data.status === 'success') {
          this.portfolio = { ...this.portfolio, is_published: false }
        }
        return res.data
      } catch (e) {
        throw e
      }
    },

    async fetchPlans() {
      try {
        const res = await axios.get('/api/v1/portfolio/plans')
        if (res.data.status === 'success') {
          this.plans = res.data.data
        }
      } catch (e) {
        console.error('Failed to fetch plans:', e)
      }
    },

    async fetchTemplates() {
      try {
        const res = await axios.get('/api/v1/portfolio/templates')
        if (res.data.status === 'success') {
          this.templates = res.data.data
        }
      } catch (e) {
        console.error('Failed to fetch templates:', e)
      }
    },

    async fetchSubscription() {
      const auth = useAuthStore()
      if (!auth.isAuthenticated) return
      try {
        const res = await axios.get('/api/v1/portfolio/subscription', auth.config)
        if (res.data.status === 'success') {
          this.subscription = res.data.data.active ? res.data.data.subscription : null
        }
      } catch (e) {
        console.error('Failed to fetch subscription:', e)
      }
    },

    async checkSubdomain(subdomain) {
      const auth = useAuthStore()
      try {
        const res = await axios.get('/api/v1/portfolio/subdomain/check', {
          ...auth.config,
          params: { subdomain },
        })
        return res.data.data
      } catch (e) {
        return { available: false, reason: 'Check failed' }
      }
    },

    clear() {
      this.portfolio = null
      this.subscription = null
      this.plans = []
      this.templates = []
      this.error = null
    },
  },
})
