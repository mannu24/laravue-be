<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../../../stores/theme'
import { useAuthStore } from '../../../stores/auth'
import { Card, CardContent } from '../../ui/card'
import { Button } from '../../ui/button'
import { UserPlus, Users, Check } from 'lucide-vue-next'

const themeStore = useThemeStore()
const props = defineProps({
    users: {
        type: Array,
        default: () => []
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: 'blue' // blue, green, purple, etc.
    }
})

const colorClasses = computed(() => {
    const colors = {
        blue: {
            icon: themeStore.isDark ? 'text-blue-400' : 'text-blue-600',
            hover: 'hover:text-blue-600 dark:hover:text-blue-400',
            link: 'hover:text-blue-600 dark:hover:text-blue-400',
            background: '6366f1'
        },
        green: {
            icon: themeStore.isDark ? 'text-green-400' : 'text-green-600',
            hover: 'hover:text-green-600 dark:hover:text-green-400',
            link: 'hover:text-green-600 dark:hover:text-green-400',
            background: '347958'
        },
        purple: {
            icon: themeStore.isDark ? 'text-purple-400' : 'text-purple-600',
            hover: 'hover:text-purple-600 dark:hover:text-purple-400',
            link: 'hover:text-purple-600 dark:hover:text-purple-400',
            background: '8b5cf6'
        }
    }
    return colors[props.color]
})

const emit = defineEmits(['follow', 'unfollow'])

const router = useRouter()
const authStore = useAuthStore()

const followingUsers = ref(new Set())

const handleFollow = (user) => {
    if (followingUsers.value.has(user.id)) {
        followingUsers.value.delete(user.id)
        emit('unfollow', user)
    } else {
        followingUsers.value.add(user.id)
        emit('follow', user)
    }
}

const isFollowing = (userId) => {
    return followingUsers.value.has(userId) || props.users.find(u => u.id === userId)?.is_following
}

const getUserAvatar = (user) => {
    return user.avatar || user.profile_photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || user.username || 'User')}&background=6366f1&color=fff&size=200`
}

const handleUserClick = (user) => {
    if (user.username) {
        router.push(`/@${user.username}`)
    }
}
</script>

<template>
    <Card>
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <UserPlus class="h-5 w-5" :class="colorClasses.icon" />
                <h3 class="text-lg font-semibold" :class="[
                    themeStore.isDark ? 'text-white' : 'text-gray-900'
                ]">
                    Who to Follow
                </h3>
            </div>
            <!-- <p class="text-xs mt-1" :class="[
                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
            ]">
                Discover new people
            </p> -->
        </div>

        <CardContent class="p-0">
            <!-- Loading State -->
            <div v-if="isLoading" class="px-6 py-8">
                <div class="space-y-4">
                    <div v-for="i in 5" :key="i" class="flex items-center gap-3 animate-pulse">
                        <div class="w-12 h-12 rounded-full" :class="[
                            themeStore.isDark ? 'bg-gray-700' : 'bg-gray-200'
                        ]"></div>
                        <div class="flex-1">
                            <div class="h-4 rounded w-3/4 mb-2" :class="[
                                themeStore.isDark ? 'bg-gray-700' : 'bg-gray-200'
                            ]"></div>
                            <div class="h-3 rounded w-1/2" :class="[
                                themeStore.isDark ? 'bg-gray-700' : 'bg-gray-200'
                            ]"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!isLoading && users.length === 0" class="px-6 py-8 text-center">
                <Users class="h-12 w-12 mx-auto mb-3 opacity-50" :class="[
                    themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                ]" />
                <p class="text-sm" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">
                    No suggestions available
                </p>
            </div>

            <!-- Users List -->
            <div v-else class="divide-y max-h-[400px] overflow-y-auto" :class="[
                themeStore.isDark ? 'divide-gray-800' : 'divide-gray-100'
            ]">
                <div
                    v-for="user in users"
                    :key="user.id"
                    class="group p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <div 
                            @click="handleUserClick(user)"
                            class="flex-shrink-0 cursor-pointer"
                        >
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 transition-transform group-hover:scale-105" :class="[
                                themeStore.isDark ? 'border-gray-700' : 'border-gray-200'
                            ]">
                                <img
                                    :src="getUserAvatar(user)"
                                    :alt="user.name || user.username"
                                    class="w-full h-full object-cover"
                                    @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || user.username || 'User')}&background=${colorClasses.background}&color=fff&size=200`"
                                />
                            </div>
                        </div>

                        <!-- User Info -->
                        <div 
                            @click="handleUserClick(user)"
                            class="flex-1 min-w-0 cursor-pointer"
                        >
                            <h4 class="text-sm font-semibold truncate transition-colors" :class="[
                                themeStore.isDark ? 'text-white' : 'text-gray-900',
                                colorClasses.hover
                            ]">
                                {{ user.name || user.username }}
                            </h4>
                            <p class="text-xs truncate" :class="[
                                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                            ]">
                                @{{ user.username }}
                            </p>
                            <div v-if="user.followers_count !== undefined" class="flex items-center gap-2 mt-1">
                                <Users class="h-3 w-3" :class="[
                                    themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                ]" />
                                <span class="text-xs" :class="[
                                    themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                ]">
                                    {{ user.followers_count }} {{ user.followers_count === 1 ? 'follower' : 'followers' }}
                                </span>
                            </div>
                        </div>

                        <!-- Follow Button -->
                        <div class="flex-shrink-0">
                            <Button
                                v-if="!isFollowing(user.id) && user.id !== authStore.user?.id"
                                @click.stop="handleFollow(user)"
                                variant="vue"
                                class="text-xs px-3 py-1.5 h-auto"
                            >
                                <UserPlus class="h-3 w-3 mr-1" />
                                Follow
                            </Button>
                            <Button
                                v-else-if="isFollowing(user.id) && user.id !== authStore.user?.id"
                                @click.stop="handleFollow(user)"
                                variant="outline"
                                class="text-xs px-3 py-1.5 h-auto"
                            >
                                <Check class="h-3 w-3 mr-1" />
                                Following
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View All Link -->
            <div v-if="users.length > 0" class="px-6 py-4 border-t" :class="[
                themeStore.isDark ? 'border-gray-800 bg-gray-900/50' : 'border-gray-200 bg-gray-50/50'
            ]">
                <button
                    @click="router.push('/search?type=user')"
                    class="w-full text-sm font-medium flex items-center justify-center gap-2 transition-colors" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600',
                        colorClasses.link
                    ]"
                >
                    Discover more users
                    <UserPlus class="h-4 w-4" />
                </button>
            </div>
        </CardContent>
    </Card>
</template>

