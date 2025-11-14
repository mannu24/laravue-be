/**
 * Recent Searches Composable
 * 
 * Manages recent searches in localStorage (max 5 searches)
 */

import { ref } from 'vue'

const RECENT_SEARCHES_KEY = 'recent_searches'
const MAX_RECENT_SEARCHES = 5

/**
 * Recent Searches Composable
 * 
 * @returns {Object} Recent searches state and methods
 */
export function useRecentSearches() {
    // Instance-specific state
    const recentSearches = ref([])

    /**
     * Load recent searches from localStorage
     */
    const loadRecentSearches = () => {
        try {
            const stored = localStorage.getItem(RECENT_SEARCHES_KEY)
            if (stored) {
                recentSearches.value = JSON.parse(stored)
            } else {
                recentSearches.value = []
            }
        } catch (error) {
            console.error('Error loading recent searches:', error)
            recentSearches.value = []
        }
    }

    /**
     * Save recent searches to localStorage
     */
    const saveRecentSearches = () => {
        try {
            localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(recentSearches.value))
        } catch (error) {
            console.error('Error saving recent searches:', error)
        }
    }

    // Load on initialization
    loadRecentSearches()

    /**
     * Add a search to recent searches
     * 
     * @param {string} query - The search query
     */
    const addRecentSearch = (query) => {
        if (!query || query.trim().length < 3) {
            return
        }

        const trimmedQuery = query.trim()

        // Remove if already exists (to move it to top)
        recentSearches.value = recentSearches.value.filter(
            search => search.query.toLowerCase() !== trimmedQuery.toLowerCase()
        )

        // Add to beginning
        recentSearches.value.unshift({
            query: trimmedQuery,
            timestamp: Date.now(),
        })

        // Keep only max recent searches
        if (recentSearches.value.length > MAX_RECENT_SEARCHES) {
            recentSearches.value = recentSearches.value.slice(0, MAX_RECENT_SEARCHES)
        }

        saveRecentSearches()
    }

    /**
     * Remove a specific recent search
     * 
     * @param {string} query - The search query to remove
     */
    const removeRecentSearch = (query) => {
        recentSearches.value = recentSearches.value.filter(
            search => search.query.toLowerCase() !== query.toLowerCase()
        )
        saveRecentSearches()
    }

    /**
     * Clear all recent searches
     */
    const clearRecentSearches = () => {
        recentSearches.value = []
        saveRecentSearches()
    }

    /**
     * Get formatted time ago for a search
     * 
     * @param {number} timestamp - The timestamp
     * @returns {string}
     */
    const getTimeAgo = (timestamp) => {
        const now = Date.now()
        const diff = now - timestamp
        const seconds = Math.floor(diff / 1000)
        const minutes = Math.floor(seconds / 60)
        const hours = Math.floor(minutes / 60)
        const days = Math.floor(hours / 24)

        if (days > 0) return `${days}d ago`
        if (hours > 0) return `${hours}h ago`
        if (minutes > 0) return `${minutes}m ago`
        return 'Just now'
    }

    return {
        recentSearches,
        addRecentSearch,
        removeRecentSearch,
        clearRecentSearches,
        getTimeAgo,
        loadRecentSearches,
    }
}

