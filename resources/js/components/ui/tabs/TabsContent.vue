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
</script>

<template>
  <div
    v-show="isActive"
    :class="[
      'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2'
    ]"
    :data-state="isActive ? 'active' : 'inactive'"
    role="tabpanel"
  >
    <slot />
  </div>
</template>
