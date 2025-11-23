<template>
  <div>
    <div class="container max-w-6xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
      <Card class="border border-gray-200 dark:border-white/10 shadow-2xl overflow-hidden relative rounded-3xl bg-gradient-to-br from-sky-100 via-blue-50 to-emerald-100 dark:from-sky-500/20 dark:via-blue-900/30 dark:to-emerald-500/20">
        <!-- Radial Gradient Overlay -->
        <div
          class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.15),_transparent_55%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.35),_transparent_55%)]">
        </div>

        <CardContent class="p-8 relative z-10">
          <div v-if="user">
            <!-- Header: Avatar + Name/Username on left, Buttons on right -->
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
              <!-- Left: Avatar + Name/Username -->
              <div class="flex items-start gap-6 flex-1">
                <!-- Avatar -->
                <div class="relative group flex-shrink-0">
                  <div class="absolute inset-0 rounded-full bg-gray-300/30 dark:bg-gray-600/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-xl"></div>
                  <img 
                    v-if="user"
                    :src="userAvatar" 
                    :alt="user.name"
                    class="relative w-24 h-24 rounded-full border-4 shadow-xl object-cover transition-transform duration-300 group-hover:scale-105"
                    :class="[
                      themeStore.isDark 
                        ? 'border-gray-600' 
                        : 'border-gray-200'
                    ]">
                </div>
                
                <!-- Name and Username -->
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <h1 class="text-4xl font-bold" :class="[
                      themeStore.isDark ? 'text-white' : 'text-gray-900'
                    ]">
                      {{ user.name }}
                    </h1>
                    <span v-if="user?.completed" 
                      class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                      :class="[
                        themeStore.isDark 
                          ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                          : 'bg-green-100 text-green-700 border border-green-200'
                      ]">
                      ✓ Verified
                    </span>
                  </div>
                  <p class="text-xl font-medium mb-2" :class="[
                    themeStore.isDark 
                      ? 'text-gray-300' 
                      : 'text-gray-600'
                  ]">
                    @{{ user.username }}
                  </p>
                  <p class="text-sm" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                  ]">
                    {{ isOwnProfile ? 'This is your profile. Customize it to showcase your work and interests.' : `View ${user.name}'s profile and achievements.` }}
                  </p>
                </div>
              </div>
              
              <!-- Right: Action Buttons - Only show for own profile -->
              <div v-if="isOwnProfile" class="flex items-center justify-center gap-2">
                <button
                  @click="$emit('update-profile')"
                  class="w-full h-10 rounded-lg font-medium transition-all duration-200 flex items-center justify-start gap-3 px-4 group"
                  :class="[
                    themeStore.isDark
                      ? 'bg-gray-800/60 hover:bg-gray-800 text-white border border-gray-700/50 hover:border-gray-600'
                      : 'bg-gray-900 hover:bg-gray-800 text-white border border-gray-800'
                  ]">
                  <Edit class="h-4 w-4 flex-shrink-0" />
                  <span class="block md:hidden">Update Profile</span>
                  <span class="hidden md:inline-block">Profile</span>
                </button>
                <button
                  @click="$emit('settings')"
                  class="w-full h-10 rounded-lg font-medium transition-all duration-200 flex items-center justify-start gap-3 px-4 group"
                  :class="[
                    themeStore.isDark
                      ? 'bg-gray-800/60 hover:bg-gray-800 text-white border border-gray-700/50 hover:border-gray-600'
                      : 'bg-gray-900 hover:bg-gray-800 text-white border border-gray-800'
                  ]">
                  <Settings class="h-4 w-4 flex-shrink-0" />
                  <span class="block md:hidden">Settings</span>
                </button>
                <button
                  @click="$emit('logout')"
                  :disabled="isLoggingOut"
                  class="w-full h-10 rounded-lg font-medium transition-all duration-200 flex items-center justify-start gap-3 px-4 group disabled:opacity-50 disabled:cursor-not-allowed bg-laravel/75 hover:bg-laravel/90 text-white border border-laravel/75 hover:border-laravel/90">
                  <LogOut class="h-4 w-4 flex-shrink-0" />
                  <span>{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</span>
                </button>
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
                <div class="text-3xl font-bold mb-1" :class="[
                  themeStore.isDark ? 'text-gray-300' : 'text-gray-900'
                ]">
                  <!-- Placeholder: Projects count - will be implemented in future -->
                  --
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
                <div class="text-3xl font-bold mb-1" :class="[
                  themeStore.isDark ? 'text-gray-300' : 'text-gray-900'
                ]">
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
                <div class="text-3xl font-bold mb-1" :class="[
                  themeStore.isDark ? 'text-gray-300' : 'text-gray-900'
                ]">
                  <!-- Placeholder: Stars count - will be implemented in future -->
                  --
                </div>
                <div class="text-xs font-medium uppercase tracking-wider" :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">Stars</div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <Star class="h-4 w-4" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                  ]" />
                </div>
              </div>
              <!-- Mystery Progress Card - Only show for other users' profiles -->
              <div 
                v-if="!isOwnProfile"
                @click="showProgressModal = true"
                class="group relative p-4 rounded-lg cursor-pointer transition-all duration-300 overflow-hidden"
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
                  <div class="mt-1 text-xs opacity-70" :class="[
                    themeStore.isDark ? 'text-purple-400' : 'text-purple-600'
                  ]">
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
  X
} from 'lucide-vue-next'
import SocialLinksDisplay from './SocialLinksDisplay.vue'
import { useThemeStore } from '../../stores/theme'
import { useAuthStore } from '../../stores/auth'

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

const themeStore = useThemeStore()
const authStore = useAuthStore()
const showProgressModal = ref(false)

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

