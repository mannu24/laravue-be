<!--
  Tabs Component
  Purpose: Accessible tab component
  Props: tabs (Array of {id, label, content})
  Emits: change (activeTab)
-->
<template>
  <div class="w-full" data-test="tabs">
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="flex -mb-px space-x-8" role="tablist">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          role="tab"
          :aria-selected="activeTab === tab.id"
          :class="tabButtonClasses(tab.id)"
          @click="setActiveTab(tab.id)"
          :data-test="`tab-${tab.id}`"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>
    <div class="mt-4">
      <div
        v-for="tab in tabs"
        :key="tab.id"
        v-show="activeTab === tab.id"
        role="tabpanel"
        :data-test="`tab-panel-${tab.id}`"
      >
        <slot :name="tab.id" :tab="tab">
          {{ tab.content }}
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    default: () => []
  },
  defaultTab: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['change'])

const activeTab = ref(props.defaultTab || props.tabs[0]?.id || '')

const setActiveTab = (tabId) => {
  activeTab.value = tabId
  emit('change', tabId)
}

const tabButtonClasses = (tabId) => {
  const base = 'py-4 px-1 border-b-2 font-medium text-sm transition-colors'
  const active = 'border-blue-500 text-blue-600 dark:text-blue-400'
  const inactive = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
  
  return activeTab.value === tabId
    ? `${base} ${active}`
    : `${base} ${inactive}`
}
</script>

<style scoped>
/* Tabs styles handled by Tailwind */
</style>

