<script setup>
import {
  Bookmark,
  LayoutDashboard,
  Code2,
  Users
} from 'lucide-vue-next'
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ProfileTabs from '../components/profile/ProfileTabs.vue'
import ProfileCard from '../components/profile/ProfileCard.vue'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { useGlobalDataStore } from '../stores/globalData'
import axios from 'axios'

// Declare emits to avoid Vue warnings
defineEmits(['share_url'])

const themeStore = useThemeStore()
const authStore = useAuthStore()
const globalDataStore = useGlobalDataStore()
const router = useRouter()
const route = useRoute()

const user = ref(null)
const isLoading = ref(true)
const projects = ref([])
const socialLinks = ref([])

// Get user data from available sources (authStore or globalDataStore)
// Priority: globalDataStore.user > authStore.user
const getUserData = () => {
  // Try globalDataStore first (has more complete data including social_links)
  const globalUser = globalDataStore.user
  const authUser = authStore.user
  
  if (globalUser) {
    return {
      ...globalUser,
      // Merge with authStore data if globalData doesn't have some fields
      ...(authUser && {
        // Only override if globalData doesn't have the field
        ...Object.fromEntries(
          Object.entries(authUser).filter(([key]) => !globalUser.hasOwnProperty(key))
        )
      })
    }
  }
  
  // Fallback to authStore user
  return authUser
}

// Initialize user data from available sources
const initializeUserData = () => {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }

  isLoading.value = true
  
  // If globalData is already loaded, use it immediately
  if (globalDataStore.payload) {
    const userData = getUserData()
    if (userData) {
      user.value = userData
      socialLinks.value = userData.social_links || []
      isLoading.value = false
      return
    }
  }
  
  // If global-data is still loading, wait for it
  if (globalDataStore.loading) {
    // Use authStore.user as fallback while waiting
    if (authStore.user) {
      user.value = authStore.user
      socialLinks.value = authStore.user?.social_links || []
    }
    // Keep loading state - will be updated by watcher when global-data loads
    return
  }
  
  // If we have authStore user, use it (global-data might not be available yet)
  if (authStore.user) {
    user.value = authStore.user
    socialLinks.value = authStore.user?.social_links || []
    isLoading.value = false
  } else {
    // No user data available at all
    isLoading.value = false
  }
}

const logout = async () => {
  isLoading.value = true
  try {
    await axios.get('/api/v1/logout', authStore.config)
    authStore.clearAuthData()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    // Even if API call fails, clear local auth data and redirect
    authStore.clearAuthData()
    router.push('/login')
  } finally {
    isLoading.value = false
  }
}

// Watch for globalDataStore changes to update user data when it loads
watch(() => globalDataStore.user, (newUser) => {
  if (newUser && authStore.isAuthenticated) {
    const userData = getUserData()
    if (userData) {
      user.value = userData
      socialLinks.value = userData.social_links || []
      isLoading.value = false
    }
  }
}, { immediate: false })

// Watch for globalDataStore loading state
watch(() => globalDataStore.loading, (isLoadingGlobal) => {
  if (!isLoadingGlobal && globalDataStore.payload && authStore.isAuthenticated) {
    // Global data finished loading, update user data
    const userData = getUserData()
    if (userData) {
      user.value = userData
      socialLinks.value = userData.social_links || []
      isLoading.value = false
    }
  }
})

onMounted(() => {
  initializeUserData()
})

// Computed for gamification data
const gamificationSummary = computed(() => globalDataStore.gamificationSummary)

</script>

<template>
  <div class="min-h-screen">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 mx-auto mb-4" :class="[
          themeStore.isDark ? 'border-white' : 'border-gray-900'
        ]"></div>
        <p :class="[
          themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
        ]">Loading profile...</p>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-else-if="user">
      <!-- Hero Section with Profile Header -->
      <ProfileCard
        :user="user"
        :social-links="socialLinks"
        :gamification-summary="gamificationSummary"
        :is-logging-out="isLoading"
        @update-profile="router.push('/profile/update')"
        @settings="router.push('/settings')"
        @logout="logout"
      />

      <!-- Portfolio CTA -->
      <div class="mx-auto mt-4 mb-2 px-0">
        <div @click="router.push('/portfolio')"
          class="cursor-pointer rounded-xl border p-4 flex items-center justify-between transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
          :class="themeStore.isDark
            ? 'bg-gradient-to-r from-gray-800/80 to-gray-800/60 border-gray-700 hover:border-vue/40'
            : 'bg-gradient-to-r from-white to-gray-50 border-gray-200 hover:border-vue/40'">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-vue/20 to-vue/5 flex items-center justify-center">
              <span class="text-lg">🌐</span>
            </div>
            <div>
              <p class="font-semibold text-sm" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                Build Your Portfolio
              </p>
              <p class="text-xs" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                Create your personal portfolio website at yourname.laravue.in
              </p>
            </div>
          </div>
          <span class="text-vue text-sm font-medium hidden sm:block">Get Started →</span>
        </div>
      </div>

      <!-- Main Content -->
      <div class="mx-auto pb-5 lg:pb-16 lg:-mt-8">
        <ProfileTabs
          :user-id="user?.id"
          :tabs="[
            { value: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
            { value: 'projects', label: 'Projects', icon: Code2 },
            { value: 'activity', label: 'Activity', icon: Users },
            { value: 'saved', label: 'Saved', icon: Bookmark }
          ]"
          :default-tab="route.query.tab || 'dashboard'"
          :is-own-profile="true"
          :projects="projects"
        />
      </div>
    </div>
    <div v-else class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <Users class="h-16 w-16 mx-auto mb-4 opacity-50" :class="[
          themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
        ]" />
        <h2 class="text-2xl font-bold mb-2" :class="[
          themeStore.isDark ? 'text-white' : 'text-gray-900'
        ]">Unable to Load Profile</h2>
        <p :class="[
          themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
        ]">Please try refreshing the page.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Grid background */
.bg-grid-white\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

.bg-grid-black\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
}

/* Smooth transitions */
* {
  transition-property: transform, opacity, background-color, border-color, color, fill, stroke, box-shadow;
  transition-timing-function: cubic-bezier
}
</style>