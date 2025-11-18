<!--
  SingleQuestionView
  Purpose: Single question detail page with answers and reply form
-->
<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="loading" class="text-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else-if="question">
      <div class="mb-6">
        <QuestionCard
          :question="question"
          @open="() => {}"
        />
      </div>

      <div class="mb-6">
        <AiAnswerBlock
          v-if="aiAnswer"
          :text="aiAnswer.body || aiAnswer.content"
          :confidence="aiAnswer.confidence"
          :sources="aiAnswer.sources"
          @improve="handleImproveAnswer"
        />
      </div>

      <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
          Answers ({{ answers.length }})
        </h2>
        <AnswerList
          :answers="answers"
          :can-verify="canVerify"
          @verify-answer="handleVerifyAnswer"
          @reply="handleReply"
        />
      </div>

      <div>
        <Card title="Your Answer">
          <QuestionForm
            :initial="{ question_id: question.id }"
            @submit="handleSubmitAnswer"
            @cancel="() => {}"
          />
        </Card>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">Question not found</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuestionStore } from '@/stores/questionStore'
import { useAnswerStore } from '@/stores/answerStore'
import { useAuthStore } from '@/stores/auth'
import { useXpStore } from '@/stores/xpStore'
import { useToast } from '@/composables/useToast'
import QuestionCard from '@/components/qa/QuestionCard.vue'
import AiAnswerBlock from '@/components/qa/AiAnswerBlock.vue'
import AnswerList from '@/components/qa/AnswerList.vue'
import QuestionForm from '@/components/qa/QuestionForm.vue'
import Card from '@/components/ui/Card.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const route = useRoute()
const questionStore = useQuestionStore()
const answerStore = useAnswerStore()
const authStore = useAuthStore()
const xpStore = useXpStore()
const { toastSuccess, toastError, toastXp, toastInfo } = useToast()

const loading = computed(() => questionStore.loading || answerStore.loading)
const question = computed(() => questionStore.currentQuestion)
const answers = computed(() => {
  // Get answers from question or from answer store
  if (question.value?.answers) {
    return question.value.answers
  }
  return answerStore.allAnswers
})

const aiAnswer = computed(() => {
  return answers.value.find(a => a.is_ai_generated) || null
})

const canVerify = computed(() => {
  if (!authStore.isAuthenticated || !question.value) return false
  return authStore.user?.id === question.value.user_id
})

const handleImproveAnswer = () => {
  // TODO: Open edit modal for AI answer
  toastInfo('AI answer improvement feature coming soon')
}

const handleVerifyAnswer = async (answerId) => {
  try {
    await answerStore.verifyAnswer(answerId)
    toastSuccess('Answer verified successfully!')
    
    // Refresh XP data (XP is awarded on backend for verified answers)
    if (authStore.isAuthenticated && authStore.user?.id) {
      await xpStore.fetchXpSummary(authStore.user.id)
      toastXp(25) // Answer verified = 25 XP
    }
    
    // Refresh question to get updated answers
    await questionStore.loadSingleQuestion(route.params.id)
  } catch (error) {
    toastError(error.message || 'Failed to verify answer')
  }
}

const handleReply = (answerId) => {
  // TODO: Handle reply to answer (could scroll to form and pre-fill)
  console.log('Reply to answer:', answerId)
}

const handleSubmitAnswer = async (payload) => {
  try {
    const answerPayload = {
      ...payload,
      question_id: question.value.id
    }
    
    await answerStore.submitAnswer(answerPayload)
    toastSuccess('Answer submitted successfully!')
    
    // Refresh XP data (XP is awarded on backend for answers)
    if (authStore.isAuthenticated && authStore.user?.id) {
      await xpStore.fetchXpSummary(authStore.user.id)
      toastXp(15) // Answer created = 15 XP
    }
    
    // Refresh question to get updated answers
    await questionStore.loadSingleQuestion(route.params.id)
  } catch (error) {
    toastError(error.message || 'Failed to submit answer')
  }
}

onMounted(async () => {
  const questionId = route.params.id
  
  try {
    await Promise.all([
      questionStore.loadSingleQuestion(questionId),
      answerStore.fetchAnswersForQuestion(questionId)
    ])
  } catch (error) {
    toastError('Failed to load question')
  }
})
</script>

<script>
import { useToast } from '@/composables/useToast'
const { toastInfo } = useToast()
</script>

<style scoped>
/* Single question view styles handled by Tailwind */
</style>
