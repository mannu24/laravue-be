<script setup>
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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
  Bookmark
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ProfileTabs from '../components/profile/ProfileTabs.vue'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const router = useRouter()

const user = ref(null)
const isLoading = ref(true)
const projects = ref([])

// Computed properties for user data with fallbacks
const userAvatar = computed(() => {
  if (!user.value) return ''
  return user.value.profile_photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value.name || 'User')}&background=6366f1&color=fff&size=200`
})

const userBio = computed(() => {
  // Placeholder: Bio will be added in future
  return user.value?.bio || ''
})

const userLocation = computed(() => {
  // Placeholder: Location will be added in future
  return user.value?.location || null
})

const joinDate = computed(() => {
  if (!user.value?.created_at) return ''
  return new Date(user.value.created_at).toLocaleDateString('en-US', { 
    month: 'long', 
    year: 'numeric' 
  })
})

// Fetch user data from backend
const fetchUserData = async () => {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }

  isLoading.value = true
  try {
    const response = await axios.get('/api/v1/user', authStore.config)
    
    if (response.data.status === 'success' && response.data.data.user) {
      user.value = response.data.data.user
    } else {
      // Fallback to authStore user data
      user.value = authStore.user
    }
  } catch (error) {
    console.error('Error fetching user data:', error)
    // Fallback to authStore user data
    user.value = authStore.user
  } finally {
    isLoading.value = false
  }
}

// Placeholder: Projects will be fetched in future
const fetchProjects = async () => {
  // Projects API will be implemented in future
  projects.value = []
}

// Logout function
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

onMounted(() => {
  fetchUserData()
  fetchProjects()
})
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
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
    <div class="relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 -z-10">
        <div :class="['absolute inset-0 transition-all duration-500',
          themeStore.isDark
            ? 'bg-gradient-to-br from-gray-900 via-purple-900/10 to-gray-900'
            : 'bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50'
        ]"></div>

        <!-- Grid Pattern -->
        <div :class="['absolute inset-0 opacity-20',
          themeStore.isDark ? 'bg-grid-white/[0.05]' : 'bg-grid-black/[0.05]'
        ]"
          style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 30px 30px;">
        </div>
      </div>

      <div class="container max-w-6xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Own Profile - Minimalistic Professional Design -->
        <Card :class="['border-0 shadow-2xl backdrop-blur-sm overflow-hidden relative',
          themeStore.isDark ? 'bg-gradient-to-br from-gray-800 via-gray-800/95 to-gray-900' : 'bg-gradient-to-br from-white via-gray-50/50 to-white'
        ]">
          <!-- Subtle Top Border -->
          <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-gray-300 to-transparent dark:via-gray-700">
          </div>
          
          <!-- Decorative Half Circle Accents -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-gray-200/20 to-transparent rounded-bl-full -z-0 dark:from-gray-700/10"></div>
          <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-gray-200/20 to-transparent rounded-tr-full -z-0 dark:from-gray-700/10"></div>

          <CardContent class="p-8 relative z-10">
            <div class="flex flex-col md:flex-row gap-8">
              <!-- Profile Details -->
              <div class="flex-1" v-if="user">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6">
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
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
                    <p class="text-xl font-medium mb-3" :class="[
                      themeStore.isDark 
                        ? 'text-gray-300' 
                        : 'text-gray-600'
                    ]">
                      @{{ user.username }}
                    </p>
                    <p class="text-sm mb-4" :class="[
                      themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                    ]">
                      This is your profile. Customize it to showcase your work and interests.
                    </p>
                  </div>
                </div>

                <p v-if="userBio" class="text-lg mb-6 leading-relaxed" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                  {{ userBio }}
                </p>

                <!-- Stats - Minimalistic Professional Design -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
                </div>

                <div class="flex flex-wrap gap-4 text-sm"
                  :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                  <div v-if="user.email" class="flex items-center">
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
              </div>
              <!-- Avatar and Basic Info -->
              <div class="flex flex-col items-center md:items-start">
                <div class="relative mb-6 group">
                  <div class="absolute inset-0 rounded-full bg-gray-300/30 dark:bg-gray-600/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 blur-xl"></div>
                  <img 
                    v-if="user"
                    :src="userAvatar" 
                    :alt="user.name"
                    class="relative w-32 h-32 rounded-full border-4 shadow-xl object-cover transition-transform duration-300 group-hover:scale-105"
                    :class="[
                      themeStore.isDark 
                        ? 'border-gray-600' 
                        : 'border-gray-200'
                    ]">
                  <!-- <div
                    v-if="user?.completed"
                    class="absolute -bottom-2 -right-2 w-9 h-9 bg-green-500 rounded-full border-4 shadow-lg flex items-center justify-center"
                    :class="[
                      themeStore.isDark ? 'border-gray-800' : 'border-white'
                    ]">
                    <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
                  </div> -->
                  <!-- Edit Avatar Badge -->
                  <!-- <div class="absolute -top-2 -right-2 w-8 h-8 bg-gray-700 dark:bg-gray-600 rounded-full flex items-center justify-center shadow-lg cursor-pointer hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors">
                    <Settings class="h-4 w-4 text-white" />
                  </div> -->
                </div>

                <Button
                  @click="router.push('/profile/update')"
                  variant="default"
                  class="mb-4 shadow-md hover:shadow-lg transition-all w-full duration-300">
                  <Edit class="h-4 w-4 mr-1" />
                  Update Profile
                </Button>
                <Button
                  @click="router.push('/settings')"
                  variant="secondary"
                  class="mb-4 shadow-md hover:shadow-lg transition-all w-full duration-300">
                  <Settings class="h-4 w-4 mr-1" />
                  Settings
                </Button>
                <Button
                  @click="logout"
                  variant="outline"
                  :disabled="isLoading"
                  class="mb-4 shadow-md hover:shadow-lg transition-all w-full duration-300">
                  <LogOut class="h-4 w-4 mr-1" />
                  {{ isLoading ? 'Logging out...' : 'Logout' }}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-24 -mt-8">
      <ProfileTabs
        :tabs="[
          { value: 'projects', label: 'Projects', icon: Code2 },
          { value: 'social', label: 'Social', icon: Users },
          { value: 'activity', label: 'Activity', icon: Users },
          { value: 'saved', label: 'Saved', icon: Bookmark }
        ]"
        default-tab="social"
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