<script setup>
import { computed } from 'vue'
import { useThemeStore } from '@/stores/theme'
import { Button } from '@/components/ui/button'
import { Search, FileText, Code2, Bell, Bookmark, Activity, FolderOpen } from 'lucide-vue-next'

const props = defineProps({
  icon: {
    type: [String, Object],
    default: 'Search'
  },
  title: {
    type: String,
    default: 'No items found'
  },
  subtitle: {
    type: String,
    default: 'Try adjusting your search criteria or filters.'
  },
  actionLabel: {
    type: String,
    default: null
  },
  actionHandler: {
    type: Function,
    default: null
  },
  size: {
    type: String,
    default: 'default', // 'small' | 'default' | 'large'
    validator: (value) => ['small', 'default', 'large'].includes(value)
  }
})

const emit = defineEmits(['action'])

const themeStore = useThemeStore()

// Icon mapping for common icons
const iconMap = {
  Search,
  FileText,
  Code2,
  Bell,
  Bookmark,
  Activity,
  FolderOpen
}

const displayIcon = computed(() => {
  if (typeof props.icon === 'string') {
    return iconMap[props.icon] || Search
  }
  return props.icon || Search
})

const sizeClasses = computed(() => {
  const sizes = {
    small: {
      container: 'py-12',
      icon: 'h-12 w-12',
      iconContainer: 'w-16 h-16',
      title: 'text-lg',
      subtitle: 'text-sm',
      spacing: 'mb-4'
    },
    default: {
      container: 'py-20',
      icon: 'h-10 w-10',
      iconContainer: 'w-20 h-20',
      title: 'text-2xl',
      subtitle: 'text-base',
      spacing: 'mb-6'
    },
    large: {
      container: 'py-24',
      icon: 'h-16 w-16',
      iconContainer: 'w-24 h-24',
      title: 'text-3xl',
      subtitle: 'text-lg',
      spacing: 'mb-8'
    }
  }
  return sizes[props.size] || sizes.default
})

const handleAction = () => {
  if (props.actionHandler) {
    props.actionHandler()
  }
  emit('action')
}
</script>

<template>
  <div class="text-center" :class="sizeClasses.container">
    <div class="flex justify-center" :class="sizeClasses.spacing">
      <div
        :class="[
          sizeClasses.iconContainer,
          'rounded-2xl bg-gradient-to-br from-blue-500/10 to-purple-500/10 flex items-center justify-center border',
          themeStore.isDark ? 'border-gray-800' : 'border-gray-200'
        ]"
      >
        <component
          :is="displayIcon"
          :class="[
            sizeClasses.icon,
            themeStore.isDark ? 'text-blue-500' : 'text-blue-600'
          ]"
        />
      </div>
    </div>
    <h3
      :class="[
        sizeClasses.title,
        'font-semibold mb-3 tracking-tight',
        themeStore.isDark ? 'text-white' : 'text-gray-900'
      ]"
    >
      {{ title }}
    </h3>
    <p
      :class="[
        sizeClasses.subtitle,
        'mb-8 font-normal max-w-md mx-auto',
        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
      ]"
    >
      {{ subtitle }}
    </p>
    <Button
      v-if="actionLabel && (actionHandler || $listeners.action)"
      @click="handleAction"
      class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-6 py-2.5 text-sm font-medium shadow-sm hover:shadow-md transition-all duration-200"
    >
      {{ actionLabel }}
    </Button>
    <slot name="action"></slot>
  </div>
</template>

