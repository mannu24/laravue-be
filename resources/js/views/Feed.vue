<script setup>
import { ref, defineEmits, onMounted, computed } from 'vue';
import InfiniteScroll from '../components/elements/InfiniteScroll.vue'
import PostForm from '../components/feed/PostForm.vue';
import FeedSidebar from '../components/feed/sidebar/FeedSidebar.vue';
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const key = ref(0);
const themeStore = useThemeStore()
const authStore = useAuthStore()
const emit = defineEmits(['share_url'])

// Sidebar data
const recommendedUsers = ref([])
const popularTags = ref([])
const isLoadingSidebar = ref(false)

// Filter state
const currentSort = ref('latest')
const selectedTags = ref([])

// Computed filters for InfiniteScroll
const feedFilters = computed(() => ({
    sort: currentSort.value,
    tags: selectedTags.value
}))

const share_url = (url) => {
    emit('share_url', url)
}

const fetch = () => {
    key.value++
}

// Fetch sidebar data
const fetchSidebarData = async () => {
    if (!authStore.isAuthenticated) return
    
    isLoadingSidebar.value = true
    try {
        const response = await axios.get('/api/v1/feed-sidebar?users_limit=10', authStore.config)
        
        if (response.data.status === 'success') {
            recommendedUsers.value = response.data.data.users || []
            // Get all unique tags for filtering (not just popular ones)
            popularTags.value = response.data.data.tags || []
        }
    } catch (error) {
        console.error('Error fetching sidebar data:', error)
    } finally {
        isLoadingSidebar.value = false
    }
}

// Handle follow/unfollow
const handleFollow = async (user) => {
    try {
        const response = await axios.post(`/api/v1/users/${user.username}/follow`, {}, authStore.config)
        if (response.data.status === 'success') {
            // Update user in list
            const index = recommendedUsers.value.findIndex(u => u.id === user.id)
            if (index !== -1) {
                recommendedUsers.value[index].is_following = true
                recommendedUsers.value[index].followers_count = (recommendedUsers.value[index].followers_count || 0) + 1
            }
        }
    } catch (error) {
        console.error('Error following user:', error)
    }
}

const handleUnfollow = async (user) => {
    try {
        const response = await axios.post(`/api/v1/users/${user.username}/follow`, {}, authStore.config)
        if (response.data.status === 'success') {
            // Update user in list
            const index = recommendedUsers.value.findIndex(u => u.id === user.id)
            if (index !== -1) {
                recommendedUsers.value[index].is_following = false
                recommendedUsers.value[index].followers_count = Math.max(0, (recommendedUsers.value[index].followers_count || 1) - 1)
            }
        }
    } catch (error) {
        console.error('Error unfollowing user:', error)
    }
}

// Handle sort change
const handleSortChange = (sortValue) => {
    currentSort.value = sortValue
    // Reset and refetch feed with new sort
    key.value++
}

// Handle tags change
const handleTagsChange = (tags) => {
    selectedTags.value = tags
    // Reset and refetch feed with new tags
    key.value++
}

onMounted(() => {
    fetchSidebarData()
})
</script>
<template>
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto py-5">
            <div class="flex flex-col lg:flex-row gap-5">
                <div class="flex-1 min-w-0">
                    <div class="mb-5">
                        <PostForm @fetch="fetch"></PostForm>
                    </div>
                    <div class="space-y-6">
                        <InfiniteScroll 
                            scrolling="post" 
                            :fetchKey="key" 
                            :filters="feedFilters"
                            @share_url="share_url" 
                            :username="null"
                        ></InfiniteScroll>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <FeedSidebar
                        :recommended-users="recommendedUsers"
                        :popular-tags="popularTags"
                        :is-loading="isLoadingSidebar"
                        :current-sort="currentSort"
                        :selected-tags="selectedTags"
                        color="green"
                        @follow="handleFollow"
                        @unfollow="handleUnfollow"
                        @sort-change="handleSortChange"
                        @tags-change="handleTagsChange"
                    />
                </div>
            </div>
        </div>
    </div>
</template>