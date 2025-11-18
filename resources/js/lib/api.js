/**
 * Axios API Instance
 * Base configuration for all API requests
 */
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

// Create axios instance
const api = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor - attach auth token
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.isAuthenticated && authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle errors globally
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle common errors
    if (error.response) {
      const status = error.response.status
      
      // Unauthorized - clear auth and redirect to login
      if (status === 401) {
        const authStore = useAuthStore()
        authStore.clearAuthData()
        // Optionally redirect to login
        if (window.location.pathname !== '/login') {
          window.location.href = '/login'
        }
      }
      
      // Forbidden
      if (status === 403) {
        console.error('Access forbidden:', error.response.data)
      }
      
      // Not found
      if (status === 404) {
        console.error('Resource not found:', error.response.data)
      }
      
      // Server error
      if (status >= 500) {
        console.error('Server error:', error.response.data)
      }
    } else if (error.request) {
      console.error('Network error:', error.request)
    } else {
      console.error('Error:', error.message)
    }
    
    return Promise.reject(error)
  }
)

export default api

