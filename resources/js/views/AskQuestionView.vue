<!--
  AskQuestionView
  Purpose: Full screen question form to create new question
-->
<template>
  <div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Ask a Question</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-2">
        Get help from the community or AI assistant
      </p>
    </div>

    <Card>
      <QuestionForm
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </Card>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useQuestionStore } from '@/stores/questionStore'
import { useToast } from '@/composables/useToast'
import Card from '@/components/ui/Card.vue'
import QuestionForm from '@/components/qa/QuestionForm.vue'

const router = useRouter()
const questionStore = useQuestionStore()
const { toastSuccess, toastError } = useToast()

const handleSubmit = async (payload) => {
  try {
    const question = await questionStore.askQuestion(payload)
    toastSuccess('Question created successfully!')
    
    // XP reward will be shown via realtime notification from backend
    // when task is completed (if applicable)
    
    router.push(`/questions/${question.id}`)
  } catch (error) {
    toastError(error.message || 'Failed to create question')
  }
}

const handleCancel = () => {
  router.back()
}
</script>

<style scoped>
/* Ask question view styles handled by Tailwind */
</style>
