import { ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

export function useFollow() {
    const authStore = useAuthStore()
    const isFollowing = ref(false)
    const isLoading = ref(false)
    const followersCount = ref(0)
    const followingCount = ref(0)

    /**
     * Toggle follow/unfollow a user
     * @param {string} username - Username of the user to follow/unfollow
     * @returns {Promise<boolean>} - Returns true if successful, false otherwise
     */
    const toggleFollow = async (username) => {
        if (!authStore.isAuthenticated) {
            console.warn('Authentication required to follow users')
            return false
        }

        if (isLoading.value) {
            return false
        }

        isLoading.value = true

        try {
            const response = await axios.post(
                `/api/v1/users/${username}/follow`,
                {},
                authStore.config
            )

            if (response.data.status === 'success') {
                isFollowing.value = response.data.data.is_following
                followersCount.value = response.data.data.user.followers_count
                followingCount.value = response.data.data.user.following_count

                return true
            }

            return false
        } catch (error) {
            console.error('Follow toggle error:', error.response?.data || error.message)
            return false
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Check if current user is following a specific user
     * @param {string} username - Username to check
     */
    const checkFollowStatus = async (username) => {
        if (!authStore.isAuthenticated) {
            isFollowing.value = false
            return
        }

        try {
            const response = await axios.get(
                `/api/v1/users/${username}/follow/check`,
                authStore.config
            )

            if (response.data.status === 'success') {
                isFollowing.value = response.data.data.is_following
                followersCount.value = response.data.data.user.followers_count
                followingCount.value = response.data.data.user.following_count
            }
        } catch (error) {
            console.error('Error checking follow status:', error)
            isFollowing.value = false
        }
    }

    /**
     * Initialize follow state
     * @param {string} username - Username to initialize for
     * @param {Object} userData - Optional user data with follow counts
     */
    const initialize = (username, userData = null) => {
        if (userData) {
            isFollowing.value = userData.is_following || false
            followersCount.value = userData.followers_count || 0
            followingCount.value = userData.following_count || 0
        } else {
            checkFollowStatus(username)
        }
    }

    return {
        isFollowing,
        isLoading,
        followersCount,
        followingCount,
        toggleFollow,
        checkFollowStatus,
        initialize
    }
}

