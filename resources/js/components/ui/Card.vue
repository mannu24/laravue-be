<!--
  Card Component
  Purpose: Simple card wrapper with optional title
  Props: title (string|null)
  Slots: default, footer
-->
<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <div v-if="title" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
    </div>
    <div class="px-6 py-4">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: null
  }
})
</script>

<style scoped>
/* Card styles handled by Tailwind */
</style>

