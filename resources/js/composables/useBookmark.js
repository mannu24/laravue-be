/**
 * Bookmark Composable
 * 
 * Handles bookmark/unbookmark functionality for posts, questions, and projects.
 * Similar to useFollow but for bookmarks.
 */

import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import axios from 'axios'

/**
 * Bookmark Composable
 * 
 * @param {Object} recordData - The record data (post, question, or project)
 * @param {string} type - The type: 'post', 'question', or 'project'
 */
export function useBookmark(recordData = null, type = 'post') {
    const authStore = useAuthStore()
    const router = useRouter()
    
    // Instance-specific state
    const isBookmarked = ref(false)
    const bookmarkCount = ref(0)
    const isLoading = ref(false)

    // Initialize state if recordData is provided
    if (recordData) {
        isBookmarked.value = recordData.bookmarked ?? false
        bookmarkCount.value = recordData.bookmark_count ?? 0
    }

    /**
     * Toggle bookmark status
     * 
     * @param {string} type - 'post', 'question', or 'project'
     * @param {number} recordId - The ID of the record
     * @returns {Promise<boolean>} - Returns true if bookmarked, false if unbookmarked
     */
    const toggleBookmark = async (type, recordId) => {
        if (!authStore.isAuthenticated) {
            router.push('/login')
            return false
        }

        if (isLoading.value) {
            return isBookmarked.value
        }

        isLoading.value = true

        try {
            const response = await axios.post(
                '/api/v1/bookmarks/toggle',
                {
                    type: type,
                    record_id: recordId,
                },
                authStore.config
            )

            if (response.data.status === 'success') {
                isBookmarked.value = response.data.data.bookmarked
                bookmarkCount.value = response.data.data.bookmark_count

                // Optional: You can add toast notifications here if needed
                // For now, we'll just update the state silently

                return isBookmarked.value
            } else {
                return isBookmarked.value
            }
        } catch (error) {
            console.error('Error toggling bookmark:', error)
            return isBookmarked.value
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Check bookmark status
     * 
     * @param {string} type - 'post', 'question', or 'project'
     * @param {number} recordId - The ID of the record
     */
    const checkBookmarkStatus = async (type, recordId) => {
        if (!authStore.isAuthenticated) {
            return
        }

        try {
            const response = await axios.get(
                `/api/v1/bookmarks/check?type=${type}&record_id=${recordId}`,
                authStore.config
            )

            if (response.data.status === 'success') {
                isBookmarked.value = response.data.data.bookmarked
                bookmarkCount.value = response.data.data.bookmark_count
            }
        } catch (error) {
            console.error('Error checking bookmark status:', error)
        }
    }

    /**
     * Initialize bookmark state from record data
     * 
     * @param {Object} recordData - The record data
     */
    const initialize = (recordData) => {
        if (recordData) {
            isBookmarked.value = recordData.bookmarked ?? false
            bookmarkCount.value = recordData.bookmark_count ?? 0
        }
    }

    return {
        isBookmarked,
        bookmarkCount,
        isLoading,
        toggleBookmark,
        checkBookmarkStatus,
        initialize,
    }
}

