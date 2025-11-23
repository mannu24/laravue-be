<script setup>
import { cn } from '@/lib/utils';
import { Primitive } from 'radix-vue';
import { buttonVariants } from '.';
import { computed } from 'vue';

const props = defineProps({
  variant: { type: null, required: false },
  size: { type: null, required: false },
  class: { type: null, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false, default: 'button' },
});

const buttonClasses = computed(() => {
  const base = 'px-4 py-2 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
  
  const variants = {
    primary: 'bg-blue-700 hover:bg-blue-600 text-white focus:ring-blue-500',
    secondary: 'bg-gray-700 dark:bg-gray-200 hover:bg-gray-600 dark:hover:bg-gray-300 text-gray-700 dark:text-gray-300 focus:ring-gray-500',
    ghost: 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-gray-500',
    red: 'bg-red-700 hover:bg-red-600 text-white focus:ring-red-500',
    laravel: 'bg-laravel-600 hover:bg-laravel-500 text-white focus:ring-laravel-500',
    vue: 'bg-vue-600 hover:bg-vue-500 text-white focus:ring-vue-500'
  }
  
  return `${base} ${variants[props.variant]}`
})
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :class="cn(cn(buttonVariants({ variant, size }), props.class), buttonClasses)"
  >
    <slot />
  </Primitive>
</template>
