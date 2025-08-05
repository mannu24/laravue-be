<script setup>
import { inject, computed } from 'vue'

const props = defineProps({
  value: {
    type: String,
    required: true
  }
})

const tabs = inject('tabs')

const isActive = computed(() => tabs.activeTab.value === props.value)

const handleClick = () => {
  tabs.setActiveTab(props.value)
}
</script>

<template>
  <button
    :class="[
      'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
      isActive ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/50'
    ]"
    :data-state="isActive ? 'active' : 'inactive'"
    @click="handleClick"
    role="tab"
    :aria-selected="isActive"
  >
    <slot />
  </button>
</template>
