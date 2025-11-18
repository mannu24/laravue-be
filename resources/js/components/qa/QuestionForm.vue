<!--
  QuestionForm Component
  Purpose: Form for creating/editing questions with title, body, tags, and AI generation toggle
  Props: initial (Object|null for edit mode)
  Emits: submit (payload), cancel
-->
<template>
  <form @submit.prevent="handleSubmit" class="space-y-4" data-test="question-form">
    <div>
      <label
        for="title"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Question Title *
      </label>
      <input
        id="title"
        v-model="formData.title"
        type="text"
        required
        maxlength="255"
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        placeholder="What's your question?"
        data-test="question-title-input"
      />
    </div>

    <div>
      <label
        for="body"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Question Details *
      </label>
      <textarea
        id="body"
        v-model="formData.body"
        required
        rows="6"
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
        placeholder="Provide details about your question..."
        data-test="question-body-input"
      ></textarea>
    </div>

    <div>
      <label
        for="tags"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Tags (comma-separated)
      </label>
      <input
        id="tags"
        v-model="formData.tags"
        type="text"
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        placeholder="laravel, vue, api"
        data-test="question-tags-input"
      />
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Separate tags with commas
      </p>
    </div>

    <div class="flex items-center gap-2">
      <input
        id="generate_with_ai"
        v-model="formData.generate_with_ai"
        type="checkbox"
        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        data-test="ai-toggle"
      />
      <label
        for="generate_with_ai"
        class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer"
      >
        Generate AI answer automatically
      </label>
    </div>

    <div class="flex items-center gap-3 pt-4">
      <button
        type="submit"
        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
        data-test="submit-question-button"
      >
        {{ initial ? 'Update Question' : 'Ask Question' }}
      </button>
      <button
        type="button"
        @click="handleCancel"
        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
      >
        Cancel
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  initial: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['submit', 'cancel'])

const formData = ref({
  title: '',
  body: '',
  tags: '',
  generate_with_ai: false
})

onMounted(() => {
  if (props.initial) {
    formData.value = {
      title: props.initial.title || '',
      body: props.initial.body || props.initial.content || '',
      tags: props.initial.tags?.map(t => t.name).join(', ') || '',
      generate_with_ai: false
    }
  }
})

const handleSubmit = () => {
  const payload = {
    title: formData.value.title,
    body: formData.value.body,
    tags: formData.value.tags
      .split(',')
      .map(t => t.trim())
      .filter(t => t.length > 0),
    generate_with_ai: formData.value.generate_with_ai
  }
  emit('submit', payload)
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<style scoped>
/* Form styles handled by Tailwind */
</style>

