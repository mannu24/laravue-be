<!--
  ToastContainer Component
  Purpose: Global container for toast notifications
  Positioned fixed at top-right, renders list of ToastItem components
-->
<template>
  <Teleport to="body">
    <div
      class="toast-container fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full sm:max-w-md"
      aria-live="polite"
      aria-label="Notifications"
    >
      <TransitionGroup
        name="toast"
        tag="div"
        class="flex flex-col gap-2"
      >
        <ToastItem
          v-for="toast in toasts"
          :key="toast.id"
          :toast="toast"
          @close="handleClose"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useToastStore } from '@/stores/toastStore'
import ToastItem from './ToastItem.vue'

const toastStore = useToastStore()

const toasts = computed(() => toastStore.toasts)

const handleClose = (toastId) => {
  toastStore.removeToast(toastId)
}
</script>

<style scoped>
/* Toast container styles handled by Tailwind */
</style>

