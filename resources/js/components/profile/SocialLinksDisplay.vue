<!--
  SocialLinksDisplay Component
  Purpose: Read-only display of visible social links for profile card
-->
<template>
  <div v-if="visibleLinks.length > 0" class="flex gap-3 mt-5 overflow-x-auto" v-mask-scroll>
    <a
      v-for="link in visibleLinks"
      :key="link.id"
      :href="link.url"
      target="_blank"
      rel="noopener noreferrer"
      class="flex items-center gap-2 px-3 py-1.5 rounded-full transition-all duration-200 hover:scale-[1.03] h-100"
      :class="[
        themeStore.isDark
          ? 'bg-gray-800/50 border border-gray-700/50 hover:border-gray-600 hover:bg-gray-800/70 text-gray-300'
          : 'bg-gray-100 border border-gray-200 hover:border-gray-300 hover:bg-gray-200 text-gray-700'
      ]"
      @click="trackClick(link)"
    >
      <i :class="link.social_link_type.icon" class="text-base"></i>
      <span class="text-sm font-medium">{{ link.social_link_type.name }}</span>
    </a>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useThemeStore } from '@/stores/theme'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  socialLinks: {
    type: Array,
    default: () => []
  }
})

const themeStore = useThemeStore()
const authStore = useAuthStore()

// Filter only visible links
const visibleLinks = computed(() => {
  return props.socialLinks.filter(link => link.is_visible !== false)
})

const trackClick = async (link) => {
  try {
    await axios.post(`/api/v1/social-links/${link.id}/click`, {}, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
  } catch (error) {
    // Silently fail - tracking is not critical
    console.error('Error tracking click:', error)
  }
}
</script>

<style scoped>
/* Styles handled by Tailwind classes */
</style>

