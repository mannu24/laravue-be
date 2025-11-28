<script setup>
import { ref, watch } from 'vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  description: {
    type: String,
    default: 'Are you sure you want to proceed? This action cannot be undone.'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  variant: {
    type: String,
    default: 'destructive', // 'destructive' or 'default'
    validator: (value) => ['destructive', 'default'].includes(value)
  }
})

const emit = defineEmits(['update:open', 'confirm', 'cancel'])

const isOpen = ref(props.open)

watch(() => props.open, (newValue) => {
  isOpen.value = newValue
})

watch(isOpen, (newValue) => {
  emit('update:open', newValue)
})

const handleConfirm = () => {
  emit('confirm')
  isOpen.value = false
}

const handleCancel = () => {
  emit('cancel')
  isOpen.value = false
}
</script>

<template>
  <AlertDialog :open="isOpen" @update:open="isOpen = $event">
    <AlertDialogContent class="w-[calc(100%-2rem)] max-w-md sm:max-w-lg mx-auto">
      <AlertDialogHeader>
        <div class="flex items-center gap-3">
          <div 
            :class="[
              'flex h-10 w-10 items-center justify-center rounded-full',
              variant === 'destructive' 
                ? 'bg-red-100 dark:bg-red-900/20' 
                : 'bg-blue-100 dark:bg-blue-900/20'
            ]"
          >
            <AlertTriangle 
              :class="[
                'h-5 w-5',
                variant === 'destructive' 
                  ? 'text-red-600 dark:text-red-400' 
                  : 'text-blue-600 dark:text-blue-400'
              ]"
            />
          </div>
          <div class="flex-1">
            <AlertDialogTitle>{{ title }}</AlertDialogTitle>
            <AlertDialogDescription class="mt-2">
              {{ description }}
            </AlertDialogDescription>
          </div>
        </div>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="handleCancel">
          {{ cancelText }}
        </AlertDialogCancel>
        <AlertDialogAction
          :class="[
            variant === 'destructive' 
              ? 'bg-red-600 hover:bg-red-700 focus:ring-red-600' 
              : ''
          ]"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

