<!--
  Badge Component (UI)
  Purpose: Simple badge/label component
  Props: label (string), size (string)
  Emits: none
-->
<template>
  <span :class="badgeClasses" data-test="badge">
    <slot>{{ label }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const badgeClasses = computed(() => {
  const base = 'inline-flex items-center font-medium rounded-full'
  
  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
    lg: 'px-3 py-1.5 text-base'
  }
  
  const defaultStyle = 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
  
  return `${base} ${sizes[props.size]} ${defaultStyle}`
})
</script>

<style scoped>
/* Badge styles handled by Tailwind */
</style>

