<!--
  Modal Component
  Purpose: Generic modal with slot content, ESC key support, and backdrop
  Props: visible (boolean), closeOnEsc (boolean)
  Emits: close
-->
<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click.self="handleBackdropClick"
      @keydown.esc="handleEsc"
      role="dialog"
      aria-modal="true"
      data-test="modal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  closeOnEsc: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close'])

const handleBackdropClick = () => {
  emit('close')
}

const handleEsc = (event) => {
  if (props.closeOnEsc && event.key === 'Escape') {
    emit('close')
  }
}

// Prevent body scroll when modal is open
watch(() => props.visible, (isVisible) => {
  if (isVisible) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  if (props.visible) {
    document.body.style.overflow = 'hidden'
  }
})

onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Modal styles handled by Tailwind */
</style>

