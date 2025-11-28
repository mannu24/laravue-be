<template>
  <div>
    <div class="max-w-6xl mx-auto py-5 lg:py-16">
      <Card class="border border-gray-200 dark:border-white/10 shadow-2xl overflow-hidden relative rounded-3xl bg-gradient-to-br from-sky-100 via-blue-50 to-emerald-100 dark:from-sky-500/20 dark:via-blue-900/30 dark:to-emerald-500/20">
        <!-- Radial Gradient Overlay -->
        <div
          class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.15),_transparent_55%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.35),_transparent_55%)]">
        </div>

        <CardContent class="p-5 lg:p-8 relative z-10">
          <div v-if="user">
            <!-- Header: Avatar + Name/Username on left, Buttons on right -->
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
              <div class="flex flex-row items-center md:items-start gap-4 md:gap-6 flex-1 md:text-left">
                <div class="relative group flex-shrink-0 text-center">
                  <img 
                    v-if="user"
                    :src="userAvatar" 
                    :alt="user.name"
                    class="relative w-28 h-28 md:w-24 md:h-24 rounded-full border-4 shadow-xl object-cover transition-transform duration-300 group-hover:scale-105"
                    :class="[
                      themeStore.isDark 
                        ? 'border-gray-600' 
                        : 'border-gray-200'
                    ]">
                    <div v-if="user?.completed" 
                      class="lg:hidden px-2.5 py-0.5 text-xs z-[1000] inline-block font-semibold rounded-full"
                      :class="[
                        themeStore.isDark 
                          ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                          : 'bg-green-100 text-green-700 border border-green-200'
                      ]">
                      ✓ Verified
                    </div>
                </div>
                <div class="flex-1 w-full">
                  <div class="flex flex-col md:flex-row md:items-center md:justify-start">
                    <div class="flex flex-col items-start md:items-start text-left w-full">
                      <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-2xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                          {{ user.name }}
                        </h1>
                        <span v-if="user?.completed" class="hidden lg:block px-2.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-500/30">
                          ✓ Verified
                        </span>
                      </div>
                      <p class="text-base md:text-xl font-medium mb-1 text-gray-600 dark:text-gray-300">
                        @{{ user.username }}
                      </p>
                    </div>
                  </div>
                  <p class="text-sm md:text-base max-w-2xl text-gray-500 dark:text-gray-400">
                    {{ isOwnProfile ? 'Customize your profile to showcase your work.' : `View ${user.name}'s profile and achievements.` }}
                  </p>
                </div>
              </div>
              
              <!-- Right: Action Buttons - Only show for own profile -->
              <div v-if="isOwnProfile" class="flex items-center lg:justify-center gap-2 text-nowrap overflow-x-auto lg:overflow-visible" v-mask-scroll>
                <Button
                  @click="$emit('update-profile')"
                  class="w-full h-10 rounded-lg font-medium transition-all duration-200 flex items-center justify-start gap-3 px-4 group"
                  size="sm"
                  variant="outline">
                  <Edit class="h-4 w-4 flex-shrink-0" />
                  <span class="block md:hidden">Profile</span>
                </Button>
                <Button
                  @click="$emit('settings')"
                  class="w-full h-10 rounded-lg font-medium transition-all duration-200 flex items-center justify-start gap-3 px-4 group"
                  size="sm"
                  variant="outline">
                  <Settings class="h-4 w-4 flex-shrink-0" />
                  <span class="block md:hidden">Settings</span>
                </Button>
                <Button
                  @click="$emit('logout')"
                  :disabled="isLoggingOut"
                  size="lg"
                  variant="laravel">
                  <LogOut class="h-4 w-4 flex-shrink-0" />
                  <span>{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</span>
                </Button>
              </div>
              <div v-else>
                <FollowButton 
                  v-if="!isOwnProfile && authStore.isAuthenticated"
                  :username="user.username"
                  :initial-following="isFollowing"
                  @followed="handleFollowed"
                  @unfollowed="handleUnfollowed"
                  class="w-full md:w-auto"
                />
              </div>
            </div>

            <!-- Bio -->
            <p v-if="userBio" class="text-lg mb-6 leading-relaxed" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              {{ userBio }}
            </p>

            <!-- Stats - Minimalistic Professional Design -->
            <div :class="['grid gap-4 mb-6', isOwnProfile ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-2 md:grid-cols-5']">
              <div class="group relative p-4 rounded-lg cursor-pointer transition-all duration-300"
                :class="[
                  themeStore.isDark 
                    ? 'bg-gray-800/30 border border-gray-700/30 hover:border-gray-600/50 hover:bg-gray-800/50' 
                    : 'bg-gray-50 border border-gray-200 hover:border-gray-300 hover:bg-gray-100'
                ]">
                <div class="text-3xl font-bold mb-1 text-gray-900 dark:text-gray-300">
                  {{ user.followers_count || 0 }}
                </div>
                <div class="text-xs font-medium uppercase tracking-wider" :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">Followers</div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <Users class="h-4 w-4" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                  ]" />
                </div>
              </div>
              <div class="group relative p-4 rounded-lg cursor-pointer transition-all duration-300"
                :class="[
                  themeStore.isDark 
                    ? 'bg-gray-800/30 border border-gray-700/30 hover:border-gray-600/50 hover:bg-gray-800/50' 
                    : 'bg-gray-50 border border-gray-200 hover:border-gray-300 hover:bg-gray-100'
                ]">
                <div class="text-3xl font-bold mb-1" :class="[
                  themeStore.isDark ? 'text-gray-300' : 'text-gray-900'
                ]">
                  {{ user.following_count || 0 }}
                </div>
                <div class="text-xs font-medium uppercase tracking-wider" :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">Following</div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <UserPlus class="h-4 w-4" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                  ]" />
                </div>
              </div>
              <div class="group relative p-4 rounded-lg cursor-pointer transition-all duration-300"
                :class="[
                  themeStore.isDark 
                    ? 'bg-gray-800/30 border border-gray-700/30 hover:border-gray-600/50 hover:bg-gray-800/50' 
                    : 'bg-gray-50 border border-gray-200 hover:border-gray-300 hover:bg-gray-100'
                ]">
                <div class="text-3xl font-bold mb-1 text-gray-900 dark:text-gray-300">
                  {{ user.projects_count || 0 }}
                </div>
                <div class="text-xs font-medium uppercase tracking-wider" :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">Projects</div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <Code2 class="h-4 w-4" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                  ]" />
                </div>
              </div>
              <div class="group relative p-4 rounded-lg cursor-pointer transition-all duration-300"
                :class="[
                  themeStore.isDark 
                    ? 'bg-gray-800/30 border border-gray-700/30 hover:border-gray-600/50 hover:bg-gray-800/50' 
                    : 'bg-gray-50 border border-gray-200 hover:border-gray-300 hover:bg-gray-100'
                ]">
                <div class="text-3xl font-bold mb-1 text-gray-900 dark:text-gray-300">
                  {{ user.questions_count || 0 }}
                </div>
                <div class="text-xs font-medium uppercase tracking-wider" :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">Questions</div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <MessageCircle class="h-4 w-4" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                  ]" />
                </div>
              </div>
              <!-- Mystery Progress Card - Only show for other users' profiles -->
              <div 
                v-if="!isOwnProfile"
                @click="showProgressModal = true"
                class="group relative p-4 rounded-lg cursor-pointer transition-all duration-300 overflow-hidden col-span-2 md:col-span-1"
                :class="[
                  themeStore.isDark 
                    ? 'bg-gradient-to-br from-purple-600/20 via-pink-600/20 to-indigo-600/20 border border-purple-500/30 hover:border-purple-400/50 hover:from-purple-600/30 hover:via-pink-600/30 hover:to-indigo-600/30' 
                    : 'bg-gradient-to-br from-purple-100 via-pink-100 to-indigo-100 border border-purple-200 hover:border-purple-300 hover:from-purple-200 hover:via-pink-200 hover:to-indigo-200'
                ]">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                  <div class="absolute inset-0 bg-gradient-to-br from-purple-400/10 via-pink-400/10 to-indigo-400/10 animate-pulse"></div>
                </div>
                <div class="relative z-10 flex flex-col items-center justify-center h-full min-h-[80px]">
                  <Sparkles class="h-6 w-6 mb-2 animate-pulse" :class="[
                    themeStore.isDark ? 'text-purple-400' : 'text-purple-600'
                  ]" />
                  <div class="text-xs font-medium uppercase tracking-wider text-center" :class="[
                    themeStore.isDark ? 'text-purple-300' : 'text-purple-700'
                  ]">
                    Player's Progress
                  </div>
                  <div class="mt-1 text-xs opacity-70 text-purple-600 dark:text-purple-400 hidden lg:block">
                    Click to explore
                  </div>
                </div>
              </div>
            </div>
            <div class="flex flex-wrap gap-4 text-sm mb-4"
              :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
              <div v-if="user.email && isOwnProfile" class="flex items-center">
                <Mail class="h-4 w-4 mr-2" />
                {{ user.email }}
              </div>
              <div v-if="userLocation" class="flex items-center">
                <MapPin class="h-4 w-4 mr-2" />
                {{ userLocation }}
              </div>
              <div v-else class="flex items-center opacity-50">
                <MapPin class="h-4 w-4 mr-2" />
                <span class="italic">Location coming soon</span>
              </div>
              <div v-if="joinDate" class="flex items-center">
                <Calendar class="h-4 w-4 mr-2" />
                Joined {{ joinDate }}
              </div>
            </div>
            <!-- Social Links Display -->
            <SocialLinksDisplay :social-links="socialLinks" />
          </div>
        </CardContent>
      </Card>
    </div>
    <Modal :visible="showProgressModal" @close="showProgressModal = false">
      <div class="relative">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200 dark:border-gray-700">
          <h4 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Progress Overview
          </h4>
          <button
            @click="showProgressModal = false"
            class="rounded-full p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :class="[
              themeStore.isDark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-900'
            ]"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- Stats Grid -->
        <div class="relative grid gap-8 sm:grid-cols-2">
          <!-- Total XP -->
          <div class="flex flex-col">
            <div class="text-xl md:text-3xl font-semibold mb-2" :class="[
              themeStore.isDark ? 'text-white' : 'text-gray-900'
            ]">
              {{ gamificationSummary.xp_total || 0 }}
            </div>
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-gray-400">
              Total XP
            </p>
          </div>

          <!-- Current Level -->
          <div class="flex flex-col">
            <div class="text-xl md:text-3xl font-semibold mb-2" :class="[
              themeStore.isDark ? 'text-white' : 'text-gray-900'
            ]">
              {{ levelName }}
            </div>
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-gray-400">
              Current Level
            </p>
          </div>

          <!-- Vertical Divider -->
          <div
            class="hidden sm:block absolute left-1/2 top-0 bottom-0 w-px bg-gray-200 dark:bg-gray-700 -translate-x-1/2">
          </div>

          <!-- Badges -->
          <div class="flex flex-col">
            <div class="text-xl md:text-3xl font-semibold mb-2" :class="[
              themeStore.isDark ? 'text-white' : 'text-gray-900'
            ]">
              {{ gamificationSummary.badges_count || 0 }}
            </div>
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-gray-400">
              Badges
            </p>
          </div>

          <!-- Tasks Done -->
          <div class="flex flex-col">
            <div class="text-xl md:text-3xl font-semibold mb-2" :class="[
              themeStore.isDark ? 'text-white' : 'text-gray-900'
            ]">
              {{ gamificationSummary.tasks_completed || 0 }}
            </div>
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-500 dark:text-gray-400">
              Tasks Done
            </p>
          </div>

          <!-- Horizontal Divider (Mobile) -->
          <div class="sm:hidden col-span-2 h-px bg-gray-200 dark:bg-gray-700 -mx-4"></div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import Modal from '@/components/ui/Modal.vue'
import {
  Calendar,
  Code2,
  Mail,
  MapPin,
  Settings,
  Users,
  Edit,
  LogOut,
  UserPlus,
  Star,
  Sparkles,
  X,
  MessageCircle
} from 'lucide-vue-next'
import SocialLinksDisplay from './SocialLinksDisplay.vue'
import { useThemeStore } from '../../stores/theme'
import { useAuthStore } from '../../stores/auth'
import FollowButton from '../FollowButton.vue'
import { useFollow } from '../../composables/useFollow'
import { useGlobalDataStore } from '../../stores/globalData'
import Button from '../ui/button/Button.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  socialLinks: {
    type: Array,
    default: () => []
  },
  gamificationSummary: {
    type: Object,
    default: () => ({
      xp_total: 0,
      badges_count: 0,
      tasks_completed: 0,
      streak_days: 0
    })
  },
  isLoggingOut: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update-profile', 'settings', 'logout'])

const globalDataStore = useGlobalDataStore()
const themeStore = useThemeStore()
const authStore = useAuthStore()
const showProgressModal = ref(false)
const { isFollowing, initialize } = useFollow()

initialize(props.user.username, props.user)

// Check if viewing own profile
const isOwnProfile = computed(() => {
  return authStore.isAuthenticated && 
         props.user && 
         authStore.user && 
         props.user.id === authStore.user.id
})

// Computed properties for user data with fallbacks
const userAvatar = computed(() => {
  if (!props.user) return ''
  return props.user.profile_photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(props.user.name || 'User')}&background=6366f1&color=fff&size=200`
})

const userBio = computed(() => {
  return props.user?.bio || ''
})

const userLocation = computed(() => {
  return props.user?.location || null
})

const joinDate = computed(() => {
  if (!props.user?.created_at) return ''
  return new Date(props.user.created_at).toLocaleDateString('en-US', { 
    month: 'long', 
    year: 'numeric' 
  })
})

const levelName = computed(() => {
  return props.user?.level?.name || 'Level 1'
})

const handleFollowed = () => {
    if (props.user) {
      props.user.followers_count = (props.user.followers_count || 0) + 1
      globalDataStore.user.following_count = (globalDataStore.user.following_count || 0) + 1
    }
}

const handleUnfollowed = () => {
    if (props.user) {
        props.user.followers_count = Math.max(0, (props.user.followers_count || 0) - 1)
        globalDataStore.user.following_count = Math.max(0, (globalDataStore.user.following_count || 0) - 1)
    }
}
</script>

<style scoped>
/* Grid background */
.bg-grid-white\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

.bg-grid-black\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
}
</style>

