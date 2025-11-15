<script setup>
import { provide, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const selectedValue = ref(props.modelValue)

watch(() => props.modelValue, (newValue) => {
  selectedValue.value = newValue
})

const open = () => {
  if (!props.disabled) {
    isOpen.value = true
  }
}

const close = () => {
  isOpen.value = false
}

const select = (value) => {
  selectedValue.value = value
  emit('update:modelValue', value)
  close()
}

provide('select', {
  isOpen,
  selectedValue,
  open,
  close,
  select,
  disabled: props.disabled
})
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template>

