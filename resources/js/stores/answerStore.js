/**
 * Answer Store
 * Manages answers, answer creation, and verification
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useAnswerStore = defineStore('answer', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const answers = ref([])

  // Getters
  const allAnswers = computed(() => answers.value)
  const verifiedAnswers = computed(() => {
    return answers.value.filter(a => a.is_verified)
  })
  const aiAnswers = computed(() => {
    return answers.value.filter(a => a.is_ai_generated)
  })
  const userAnswers = computed(() => {
    return answers.value.filter(a => !a.is_ai_generated)
  })

  /**
   * Submit a new answer
   * @param {object} payload - Answer data
   */
  const submitAnswer = async (payload) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.post(endpoints.answers.create(), payload)
      const newAnswer = response.data.data
      
      // Add to answers list
      answers.value.push(newAnswer)
      
      return newAnswer
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to submit answer'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Verify an answer
   * @param {number|string} answerId - Answer ID
   */
  const verifyAnswer = async (answerId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.post(endpoints.answers.verify(), {
        answer_id: answerId
      })
      const verifiedAnswer = response.data.data
      
      // Update answer in list
      const index = answers.value.findIndex(a => a.id === answerId)
      if (index !== -1) {
        answers.value[index] = verifiedAnswer
      }
      
      return verifiedAnswer
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to verify answer'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch answers for a question
   * @param {number|string} questionId - Question ID
   */
  const fetchAnswersForQuestion = async (questionId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.answers.listForQuestion(questionId))
      answers.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch answers'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear answer data
   */
  const clearAnswers = () => {
    answers.value = []
    error.value = null
  }

  return {
    // State
    loading,
    error,
    answers,
    // Getters
    allAnswers,
    verifiedAnswers,
    aiAnswers,
    userAnswers,
    // Actions
    submitAnswer,
    verifyAnswer,
    fetchAnswersForQuestion,
    clearAnswers
  }
})

