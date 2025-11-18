<!--
  QuestionsView
  Purpose: Questions listing page with search and question form toggle
-->
<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Questions</h1>
      <button
        type="button"
        @click="showForm = !showForm"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
        data-test="toggle-question-form"
      >
        {{ showForm ? 'Cancel' : 'Ask Question' }}
      </button>
    </div>

    <div v-if="showForm" class="mb-6">
      <Card title="Ask a Question">
        <QuestionForm
          @submit="handleSubmitQuestion"
          @cancel="showForm = false"
        />
      </Card>
    </div>

    <div class="mb-6">
      <QuestionSearchBar
        placeholder="Search questions..."
        @search="handleSearch"
      />
    </div>

    <QuestionList
      :questions="questions"
      :loading="loading"
      @load-more="handleLoadMore"
      @select-question="handleSelectQuestion"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useQuestionStore } from '@/stores/questionStore'
import { useXpStore } from '@/stores/xpStore'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import QuestionForm from '@/components/qa/QuestionForm.vue'
import QuestionSearchBar from '@/components/qa/QuestionSearchBar.vue'
import QuestionList from '@/components/qa/QuestionList.vue'

const router = useRouter()
const authStore = useAuthStore()
const questionStore = useQuestionStore()
const xpStore = useXpStore()
const { toastSuccess, toastError, toastXp } = useToast()

const showForm = ref(false)

// Use store computed values
const questions = computed(() => questionStore.allQuestions)
const loading = computed(() => questionStore.loading)

const handleSearch = async (query) => {
  if (!query || query.length < 2) {
    await questionStore.fetchQuestions()
    return
  }
  
  try {
    await questionStore.searchQuestions(query)
  } catch (error) {
    toastError('Failed to search questions')
  }
}

const handleSubmitQuestion = async (payload) => {
  try {
    const question = await questionStore.askQuestion(payload)
    toastSuccess('Question created successfully!')
    showForm.value = false
    
    // Refresh XP data if user is authenticated (XP is awarded on backend)
    if (authStore.isAuthenticated && authStore.user?.id) {
      await xpStore.fetchXpSummary(authStore.user.id)
      // Show XP toast if XP was awarded (backend handles this)
      toastXp(10) // Question created = 10 XP
    }
    
    // Navigate to the new question
    router.push(`/questions/${question.id}`)
  } catch (error) {
    toastError(error.message || 'Failed to create question')
  }
}

const handleLoadMore = async () => {
  try {
    await questionStore.loadMoreQuestions()
  } catch (error) {
    toastError('Failed to load more questions')
  }
}

const handleSelectQuestion = (questionId) => {
  router.push(`/questions/${questionId}`)
}

onMounted(async () => {
  try {
    await questionStore.fetchQuestions()
  } catch (error) {
    toastError('Failed to load questions')
  }
})
</script>

<style scoped>
/* Questions view styles handled by Tailwind */
</style>
