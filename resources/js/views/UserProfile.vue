<script setup>
import { ref, computed, onMounted, watch, onBeforeMount } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useThemeStore } from '../stores/theme'
import { useFollow } from '../composables/useFollow'
import ProfileTabs from '../components/profile/ProfileTabs.vue'
import ProfileCard from '../components/profile/ProfileCard.vue'
import { 
    Users, 
    Building2,
    FileText
} from 'lucide-vue-next'
import api from '@/lib/api'
import { useGlobalDataStore } from '../stores/globalData'

// Declare emits to avoid Vue warnings
defineEmits(['share_url'])

const route = useRoute()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const globalDataStore = useGlobalDataStore()
const username = ref(null)

const user = ref(null)
const isLoading = ref(true)
const isOwnProfile = computed(() => {
    return authStore.isAuthenticated && authStore.user?.username === username.value
})

const { isFollowing, followersCount, followingCount } = useFollow()

// Computed for gamification data
const gamificationSummary = computed(() => {
  // Get from API response if available, otherwise from globalDataStore
  if (user.value?.gamification?.summary) {
    return user.value.gamification.summary
  }
  return globalDataStore.gamificationSummary
})

// Fetch user profile
const fetchUserProfile = async () => {
    isLoading.value = true
    try {
        // Use api instance which automatically attaches auth token
        const response = await api.get(`/users/${username.value}`)
        
        if (response.data.status === 'success') {
            // Handle both old format (data) and new format (data.user)
            user.value = response.data.data.user || response.data.data
            followersCount.value = user.value?.followers_count || 0
            followingCount.value = user.value?.following_count || 0
            
            // Set follow status from API response
            if (authStore.isAuthenticated && !isOwnProfile.value) {
                isFollowing.value = user.value?.is_following || false
            }
            
            // Store gamification data if available
            if (response.data.data.gamification) {
                user.value.gamification = response.data.data.gamification
            }
        }
    } catch (error) {
        console.error('Error fetching user profile:', error)
        user.value = null
    } finally {
        isLoading.value = false
    }
}

// Handle route updates when navigating to same route with different params
onBeforeMount(async (to, from) => {
    if (to?.params?.username !== from?.params?.username) {
        username.value = to.params.username
        await fetchUserProfile()
    }
})

// Watch route params to handle navigation changes (fallback)
watch(() => route.params.username, (newUsername) => {
    if (newUsername && newUsername !== username.value) {
        username.value = newUsername
        fetchUserProfile()
    }
})

onMounted(async () => {
    username.value = route.params.username
    await fetchUserProfile()
    // Fetch global data if gamification not in response
    if (!user.value?.gamification) {
        await globalDataStore.fetchGlobalData()
    }
})
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
            <ProfileCard
                :user="user"
                :social-links="user.social_links || []"
                :gamification-summary="gamificationSummary"
            />

            <!-- Tabs Section -->
            <div class="pb-5 lg:pb-10">
                <ProfileTabs
                    :user-id="user?.id"
                    :tabs="[
                        { value: 'feed', label: 'Feed', icon: FileText },
                        { value: 'projects', label: 'Projects', icon: Building2 },
                        // { value: 'blogs', label: 'Blogs', icon: FileText }
                    ]"
                    default-tab="feed"
                    :username="username"
                    :is-own-profile="isOwnProfile"
                />
            </div>
        </div>

        <!-- Error State -->
        <div v-else class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <Users class="h-16 w-16 mx-auto mb-4 opacity-50" :class="[
                    themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                ]" />
                <h2 class="text-2xl font-bold mb-2" :class="[
                    themeStore.isDark ? 'text-white' : 'text-gray-900'
                ]">User Not Found</h2>
                <p :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">The user you're looking for doesn't exist.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
