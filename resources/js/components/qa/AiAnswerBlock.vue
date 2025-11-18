<!--
  AiAnswerBlock Component
  Purpose: Styled block for AI-generated answers, separated from other answers with source links and copy button
  Props: text (string), confidence (number|null), sources (Array|null)
  Emits: improve (opens edit)
-->
<template>
  <div
    class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg border-2 border-blue-200 dark:border-blue-700 p-6 mb-6"
    data-test="ai-answer-block"
  >
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
          <span class="text-white text-lg">🤖</span>
        </div>
        <div>
          <h4 class="font-semibold text-gray-900 dark:text-white">AI-Generated Answer</h4>
          <p v-if="confidence" class="text-xs text-gray-500 dark:text-gray-400">
            Confidence: {{ Math.round(confidence * 100) }}%
          </p>
        </div>
      </div>
      <button
        type="button"
        @click="handleCopy"
        class="px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        data-test="copy-button"
      >
        📋 Copy
      </button>
    </div>

    <div class="prose dark:prose-invert max-w-none mb-4">
      <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ text }}</p>
    </div>

    <div v-if="sources && sources.length > 0" class="mb-4">
      <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sources:</h5>
      <div class="space-y-1">
        <a
          v-for="(source, index) in sources"
          :key="index"
          :href="source.url"
          target="_blank"
          rel="noopener noreferrer"
          class="block text-sm text-blue-600 dark:text-blue-400 hover:underline"
        >
          {{ source.title || source.url }}
        </a>
      </div>
    </div>

    <button
      type="button"
      @click="handleImprove"
      class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
      data-test="improve-button"
    >
      Improve this answer
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  text: {
    type: String,
    required: true
  },
  confidence: {
    type: Number,
    default: null
  },
  sources: {
    type: Array,
    default: null
  }
})

const emit = defineEmits(['improve'])

const handleCopy = () => {
  navigator.clipboard.writeText(props.text).then(() => {
    // TODO: Show toast notification
  }).catch(() => {
    // Handle error
  })
}

const handleImprove = () => {
  emit('improve')
}
</script>

<style scoped>
/* AI answer block styles handled by Tailwind */
</style>

