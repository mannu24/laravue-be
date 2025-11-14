<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { SwitchRoot, SwitchThumb } from 'radix-vue'

const props = defineProps({
  checked: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  class: { type: String, default: '' },
})

const emit = defineEmits(['update:checked'])

const isChecked = computed({
  get: () => props.checked,
  set: (value) => emit('update:checked', value),
})
</script>

<template>
  <SwitchRoot
    v-model:checked="isChecked"
    :disabled="disabled"
    :class="
      cn(
        'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50',
        isChecked ? 'bg-laravel/80' : 'bg-vue',
        props.class
      )
    "
  >
    <SwitchThumb
      :class="
        cn(
          'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
          isChecked ? 'translate-x-5' : 'translate-x-0'
        )
      "
    />
  </SwitchRoot>
</template>

