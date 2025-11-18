<!--
  VerifiedAnswerBadge Component
  Purpose: Small badge showing verified answer info with tooltip
  Props: verifiedBy (Object|null with user info)
  Emits: none (pure display component)
-->
<template>
  <div class="relative group">
    <div
      class="flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full cursor-help"
      data-test="verified-badge"
    >
      <span>✓</span>
      <span>Verified</span>
    </div>
    <div
      v-if="verifiedBy"
      class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10"
    >
      Verified by {{ verifiedBy.name }}
      <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
        <div class="border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  verifiedBy: {
    type: Object,
    default: null
  }
})
</script>

<style scoped>
/* Tooltip styles handled by Tailwind */
</style>

