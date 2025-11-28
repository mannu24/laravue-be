<!--
  QuestionList Component
  Purpose: Renders paginated list of QuestionCard components
  Props: questions (Array), loading (boolean)
  Emits: load-more, select-question
-->
<template>
  <div class="space-y-4">
    <div v-if="loading && questions.length === 0" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-500 dark:text-gray-400">Loading questions...</p>
    </div>

    <div v-else-if="questions.length === 0" class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">No questions found</p>
    </div>

    <div v-else class="space-y-4">
      <QuestionCard
        v-for="question in questions"
        :key="question.id"
        :question="question"
        :navigate-on-click="false"
        @open="handleSelectQuestion"
        data-test="question-card"
      />
    </div>

    <div v-if="loading && questions.length > 0" class="text-center py-4">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <button
      v-if="!loading && questions.length > 0"
      type="button"
      @click="handleLoadMore"
      class="w-full py-3 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium"
      data-test="load-more-button"
    >
      Load More
    </button>
  </div>
</template>

<script setup>
import QuestionCard from './QuestionCard.vue'

const props = defineProps({
  questions: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['load-more', 'select-question'])

const handleLoadMore = () => {
  emit('load-more')
}

const handleSelectQuestion = (questionId) => {
  emit('select-question', questionId)
}
</script>

<style scoped>
/* List spacing handled by Tailwind */
</style>

