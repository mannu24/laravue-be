<!--
  BadgeList Component
  Purpose: Renders a grid of BadgeItem components
  Props: badges (Array of badge objects)
  Emits: view-badge (badgeSlug)
-->
<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <BadgeItem
      v-for="badge in badges"
      :key="badge.id"
      :badge="badge"
      @click="handleBadgeClick"
      data-test="badge-item"
    />
  </div>
</template>

<script setup>
import BadgeItem from './BadgeItem.vue'

const props = defineProps({
  badges: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['view-badge'])

const handleBadgeClick = (badge) => {
  emit('view-badge', badge.slug)
}
</script>

<style scoped>
/* Grid layout handled by Tailwind */
</style>

