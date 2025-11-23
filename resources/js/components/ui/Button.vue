<!--
  Button Component
  Purpose: Accessible button with variants and loading state
  Props: type, variant (primary|secondary|ghost), loading (boolean), disabled (boolean)
  Emits: click
-->
<template>
  <button
    :type="type || 'button'"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
    data-test="button"
  >
    <span v-if="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-current mr-2"></span>
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'red', 'laravel', 'vue'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => {
  const base = 'px-4 py-2 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
  
  const variants = {
    primary: 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    secondary: 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 focus:ring-gray-500',
    ghost: 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-gray-500',
    red: 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    laravel: 'bg-laravel-500 hover:bg-laravel-600 text-white focus:ring-laravel-500',
    vue: 'bg-vue-500 hover:bg-vue-600 text-white focus:ring-vue-500'
  }
  
  return `${base} ${variants[props.variant]}`
})

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>