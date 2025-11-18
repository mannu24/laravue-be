<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useThemeStore } from '../stores/theme'
import { useFollow } from '../composables/useFollow'
import FollowButton from '../components/FollowButton.vue'
import ProfileTabs from '../components/profile/ProfileTabs.vue'
import { Button } from '../components/ui/button'
import { Card, CardContent } from '../components/ui/card'
import { 
    Mail, 
    Calendar, 
    Users, 
    Settings,
    MapPin,
    Building2,
    FileText
} from 'lucide-vue-next'
import axios from 'axios'

const route = useRoute()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const username = route.params.username

const user = ref(null)
const isLoading = ref(true)
const isOwnProfile = computed(() => {
    return authStore.isAuthenticated && authStore.user?.username === username
})

const { isFollowing, followersCount, followingCount } = useFollow()

// Fetch user profile
const fetchUserProfile = async () => {
    isLoading.value = true
    try {
        const config = authStore.isAuthenticated ? authStore.config : {}
        const response = await axios.get(`/api/v1/users/${username}`, config)
        
        if (response.data.status === 'success') {
            user.value = response.data.data
            followersCount.value = user.value?.followers_count || 0
            followingCount.value = user.value?.following_count || 0
            
            // Set follow status from API response
            if (authStore.isAuthenticated && !isOwnProfile.value) {
                isFollowing.value = user.value?.is_following || false
            }
        }
    } catch (error) {
        console.error('Error fetching user profile:', error)
        user.value = null
    } finally {
        isLoading.value = false
    }
}

const handleFollowed = () => {
    if (user.value) {
        followersCount.value = (followersCount.value || 0) + 1
    }
}

const handleUnfollowed = () => {
    if (user.value) {
        followersCount.value = Math.max(0, (followersCount.value || 0) - 1)
    }
}

onMounted(() => {
    fetchUserProfile()
})
</script>

<template>
    <div :class="[
        'min-h-screen transition-colors duration-300',
        themeStore.isDark ? 'bg-gray-950' : 'bg-gray-50'
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
        <div v-else-if="user" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Profile Header Card - Visitor View Design -->
            <Card :class="[
                'mb-8 border-0 shadow-2xl overflow-hidden relative',
                themeStore.isDark 
                    ? 'bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900' 
                    : 'bg-gradient-to-br from-white via-gray-50 to-white'
            ]">
                <!-- Subtle Top Border -->
                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-gray-300 to-transparent dark:via-gray-700"></div>
                
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-32 h-32 opacity-5">
                    <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 rounded-bl-full"></div>
                </div>

                <CardContent class="p-8 relative z-10">
                    <div class="flex flex-col md:flex-row gap-8">
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center md:items-start">
                            <div class="relative mb-6">
                                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 opacity-20 blur-2xl"></div>
                                <img 
                                    :src="user.profile_photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=fff&size=200`"
                                    :alt="user.name"
                                    class="relative w-32 h-32 rounded-full border-4 shadow-2xl object-cover transition-all duration-300 hover:shadow-blue-500/20"
                                    :class="[
                                        themeStore.isDark 
                                            ? 'border-gray-600 shadow-gray-900' 
                                            : 'border-gray-200 shadow-gray-200'
                                    ]"
                                />
                                <!-- <div v-if="user.completed" 
                                    class="absolute -bottom-2 -right-2 w-9 h-9 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full border-4 shadow-lg flex items-center justify-center"
                                    :class="[
                                        themeStore.isDark ? 'border-gray-900' : 'border-white'
                                    ]">
                                    <div class="w-3.5 h-3.5 bg-white rounded-full"></div>
                                </div> -->
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 mb-4 w-full md:w-auto">
                                <FollowButton 
                                    v-if="!isOwnProfile && authStore.isAuthenticated"
                                    :username="username"
                                    :initial-following="isFollowing"
                                    @followed="handleFollowed"
                                    @unfollowed="handleUnfollowed"
                                    class="w-full md:w-auto"
                                />
                                <Button 
                                    v-if="isOwnProfile"
                                    variant="outline"
                                    class="gap-2 w-full md:w-auto"
                                >
                                    <Settings class="h-4 w-4" />
                                    Edit Profile
                                </Button>
                            </div>
                        </div>

                        <!-- Profile Details -->
                        <div class="flex-1">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h1 class="text-4xl font-bold" :class="[
                                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                                        ]">
                                            {{ user.name }}
                                        </h1>
                                        <span v-if="user.completed" 
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
                                        themeStore.isDark ? 'text-gray-300' : 'text-gray-600'
                                    ]">
                                        @{{ user.username }}
                                    </p>
                                </div>
                            </div>

                            <!-- Stats - Clean Minimal Design -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="text-center md:text-left p-3 rounded-lg transition-colors" :class="[
                                    themeStore.isDark 
                                        ? 'hover:bg-gray-800/50' 
                                        : 'hover:bg-gray-50'
                                ]">
                                    <div class="text-3xl font-bold mb-1" :class="[
                                        themeStore.isDark ? 'text-blue-400' : 'text-blue-600'
                                    ]">
                                        {{ followersCount || 0 }}
                                    </div>
                                    <div class="text-xs font-medium uppercase tracking-wider" :class="[
                                        themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                                    ]">
                                        Followers
                                    </div>
                                </div>
                                <div class="text-center md:text-left p-3 rounded-lg transition-colors" :class="[
                                    themeStore.isDark 
                                        ? 'hover:bg-gray-800/50' 
                                        : 'hover:bg-gray-50'
                                ]">
                                    <div class="text-3xl font-bold mb-1" :class="[
                                        themeStore.isDark ? 'text-indigo-400' : 'text-indigo-600'
                                    ]">
                                        {{ followingCount || 0 }}
                                    </div>
                                    <div class="text-xs font-medium uppercase tracking-wider" :class="[
                                        themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                                    ]">
                                        Following
                                    </div>
                                </div>
                                <div class="text-center md:text-left p-3 rounded-lg transition-colors" :class="[
                                    themeStore.isDark 
                                        ? 'hover:bg-gray-800/50' 
                                        : 'hover:bg-gray-50'
                                ]">
                                    <div class="text-3xl font-bold mb-1" :class="[
                                        themeStore.isDark ? 'text-purple-400' : 'text-purple-600'
                                    ]">
                                        <!-- Placeholder: Posts count - will be implemented in future -->
                                        --
                                    </div>
                                    <div class="text-xs font-medium uppercase tracking-wider" :class="[
                                        themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                                    ]">
                                        Posts
                                    </div>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="flex flex-wrap gap-4 text-sm mb-6" :class="[
                                themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                            ]">
                                <div v-if="user.email" class="flex items-center gap-2">
                                    <Mail class="h-4 w-4" />
                                    {{ user.email }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    Joined {{ new Date(user.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) }}
                                </div>
                                <!-- Placeholder: Location - will be implemented in future -->
                                <div class="flex items-center gap-2 opacity-50">
                                    <MapPin class="h-4 w-4" />
                                    <span class="italic">Location coming soon</span>
                                </div>
                                <!-- Placeholder: Company - will be implemented in future -->
                                <div class="flex items-center gap-2 opacity-50">
                                    <Building2 class="h-4 w-4" />
                                    <span class="italic">Company coming soon</span>
                                </div>
                            </div>

                            <!-- Bio Section - Placeholder for future -->
                            <div v-if="false" class="mb-6">
                                <p class="text-base leading-relaxed" :class="[
                                    themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                ]">
                                    <!-- Bio will be added in future -->
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabs Section -->
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
