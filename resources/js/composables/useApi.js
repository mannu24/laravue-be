/**
 * useApi Composable
 * Wrapper around axios to simplify API calls
 */
import api from '@/lib/api'

export function useApi() {
  /**
   * GET request
   * @param {string} url - API endpoint
   * @param {object} params - Query parameters
   * @returns {Promise} API response
   */
  const get = async (url, params = {}) => {
    return api.get(url, { params })
  }

  /**
   * POST request
   * @param {string} url - API endpoint
   * @param {object} payload - Request body
   * @returns {Promise} API response
   */
  const post = async (url, payload = {}) => {
    return api.post(url, payload)
  }

  /**
   * PUT request
   * @param {string} url - API endpoint
   * @param {object} payload - Request body
   * @returns {Promise} API response
   */
  const put = async (url, payload = {}) => {
    return api.put(url, payload)
  }

  /**
   * PATCH request
   * @param {string} url - API endpoint
   * @param {object} payload - Request body
   * @returns {Promise} API response
   */
  const patch = async (url, payload = {}) => {
    return api.patch(url, payload)
  }

  /**
   * DELETE request
   * @param {string} url - API endpoint
   * @returns {Promise} API response
   */
  const del = async (url) => {
    return api.delete(url)
  }

  return {
    get,
    post,
    put,
    patch,
    delete: del
  }
}

