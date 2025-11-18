/**
 * Question Store
 * Manages questions, search, and question creation
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useQuestionStore = defineStore('question', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const questions = ref([])
  const singleQuestion = ref(null)
  const searchResults = ref([])
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0
  })

  // Getters
  const allQuestions = computed(() => questions.value)
  const currentQuestion = computed(() => singleQuestion.value)
  const searchResultsList = computed(() => searchResults.value)

  /**
   * Fetch questions with pagination
   * @param {object} params - Query parameters (page, per_page)
   */
  const fetchQuestions = async (params = {}) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.questions.list(), params)
      questions.value = response.data.data
      
      // Update pagination if available
      if (response.data.meta) {
        pagination.value = {
          current_page: response.data.meta.current_page || 1,
          last_page: response.data.meta.last_page || 1,
          per_page: response.data.meta.per_page || 20,
          total: response.data.meta.total || 0
        }
      }
      
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch questions'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Search questions
   * @param {string} query - Search query
   * @param {object} params - Additional query parameters
   */
  const searchQuestions = async (query, params = {}) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.questions.search(), {
        query,
        ...params
      })
      searchResults.value = response.data.data
      questions.value = response.data.data // Also update main questions list
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to search questions'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new question
   * @param {object} payload - Question data
   */
  const askQuestion = async (payload) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.post(endpoints.questions.create(), payload)
      const newQuestion = response.data.data
      
      // Add to questions list
      questions.value.unshift(newQuestion)
      
      return newQuestion
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create question'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Load single question with answers
   * @param {number|string} id - Question ID
   */
  const loadSingleQuestion = async (id) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.questions.show(id))
      singleQuestion.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch question'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Load more questions (pagination)
   */
  const loadMoreQuestions = async () => {
    if (pagination.value.current_page >= pagination.value.last_page) {
      return
    }
    
    try {
      loading.value = true
      error.value = null
      const nextPage = pagination.value.current_page + 1
      const response = await api.get(endpoints.questions.list(), {
        page: nextPage,
        per_page: pagination.value.per_page
      })
      
      // Append to existing questions
      questions.value = [...questions.value, ...response.data.data]
      
      // Update pagination
      if (response.data.meta) {
        pagination.value = {
          current_page: response.data.meta.current_page || nextPage,
          last_page: response.data.meta.last_page || 1,
          per_page: response.data.meta.per_page || 20,
          total: response.data.meta.total || 0
        }
      }
      
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to load more questions'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear question data
   */
  const clearQuestions = () => {
    questions.value = []
    singleQuestion.value = null
    searchResults.value = []
    error.value = null
  }

  return {
    // State
    loading,
    error,
    questions,
    singleQuestion,
    searchResults,
    pagination,
    // Getters
    allQuestions,
    currentQuestion,
    searchResultsList,
    // Actions
    fetchQuestions,
    searchQuestions,
    askQuestion,
    loadSingleQuestion,
    loadMoreQuestions,
    clearQuestions
  }
})

