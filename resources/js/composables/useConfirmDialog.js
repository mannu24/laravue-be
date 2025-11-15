import { ref } from 'vue'

/**
 * Composable for managing confirmation dialogs
 * Returns reactive state and helper functions
 */
export function useConfirmDialog() {
  const isOpen = ref(false)
  const title = ref('Confirm Action')
  const description = ref('Are you sure you want to proceed? This action cannot be undone.')
  const confirmText = ref('Confirm')
  const cancelText = ref('Cancel')
  const variant = ref('destructive')
  const onConfirmCallback = ref(null)

  const show = (options = {}) => {
    title.value = options.title || 'Confirm Action'
    description.value = options.description || 'Are you sure you want to proceed? This action cannot be undone.'
    confirmText.value = options.confirmText || 'Confirm'
    cancelText.value = options.cancelText || 'Cancel'
    variant.value = options.variant || 'destructive'
    onConfirmCallback.value = options.onConfirm || null
    isOpen.value = true
  }

  const hide = () => {
    isOpen.value = false
    onConfirmCallback.value = null
  }

  const confirm = () => {
    if (onConfirmCallback.value) {
      onConfirmCallback.value()
    }
    hide()
  }

  const cancel = () => {
    hide()
  }

  return {
    isOpen,
    title,
    description,
    confirmText,
    cancelText,
    variant,
    show,
    hide,
    confirm,
    cancel
  }
}

