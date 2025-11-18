/**
 * useModal Composable
 * Manage modal open/close state
 */
import { ref } from 'vue'

export function useModal(initialState = false) {
  const isOpen = ref(initialState)

  /**
   * Open modal
   */
  const open = () => {
    isOpen.value = true
  }

  /**
   * Close modal
   */
  const close = () => {
    isOpen.value = false
  }

  /**
   * Toggle modal state
   */
  const toggle = () => {
    isOpen.value = !isOpen.value
  }

  return {
    isOpen,
    open,
    close,
    toggle
  }
}

