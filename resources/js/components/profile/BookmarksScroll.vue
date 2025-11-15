<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useThemeStore } from '../../stores/theme'
import { useAuthStore } from '../../stores/auth'
import { Skeleton } from '../../components/ui/skeleton'
import BookmarkCard from './BookmarkCard.vue'
import { Loader2 } from 'lucide-vue-next'
import EmptyState from '../ui/EmptyState.vue'
import axios from 'axios'

const themeStore = useThemeStore()
const authStore = useAuthStore()

const bookmarks = ref([])
const loading = ref(false)
const hasMore = ref(true)
const pageNo = ref(1)
const observer = ref(null)
const loadMoreTrigger = ref(null)
const hasLoaded = ref(false)

const fetchBookmarks = async (isLoadMore = false) => {
    if (loading.value) return
    
    if (isLoadMore) {
        if (!hasMore.value) return
        pageNo.value++
    } else {
        pageNo.value = 1
        bookmarks.value = []
        hasMore.value = true
        hasLoaded.value = false
    }

    loading.value = true
    
    try {
        const response = await axios.get(
            `/api/v1/bookmarks?page=${pageNo.value}&per_page=20`,
            authStore.config
        )

        if (response.data.status === 'success') {
            const data = response.data.data
            const newBookmarks = data.bookmarks || []
            
            if (isLoadMore) {
                bookmarks.value.push(...newBookmarks)
            } else {
                bookmarks.value = newBookmarks
            }

            const pagination = data.pagination
            hasMore.value = pagination.current_page < pagination.last_page
            hasLoaded.value = true

            // Setup observer after results are loaded
            if (hasMore.value) {
                await nextTick()
                setupIntersectionObserver()
            }
        }
    } catch (error) {
        console.error('Error fetching bookmarks:', error)
        hasLoaded.value = true
    } finally {
        loading.value = false
    }
}

const setupIntersectionObserver = () => {
    if (observer.value) {
        observer.value.disconnect()
    }

    const options = {
        root: null,
        rootMargin: '200px',
        threshold: 0.1
    }

    observer.value = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore.value && !loading.value) {
                fetchBookmarks(true)
            }
        })
    }, options)

    if (loadMoreTrigger.value) {
        observer.value.observe(loadMoreTrigger.value)
    }
}

const handleBookmarkedAction = (data) => {
    // data = [recordId, isBookmarked, bookmarkCount]
    const [recordId, isBookmarked] = data
    
    // If bookmark was removed (isBookmarked = false), remove it from the list
    if (!isBookmarked) {
        // Find the bookmark by matching the record's identifier
        bookmarks.value = bookmarks.value.filter(bookmark => {
            if (bookmark.type === 'post') {
                const postCode = bookmark.record?.post_code || bookmark.record?.id
                return String(postCode) !== String(recordId)
            } else if (bookmark.type === 'question') {
                const questionSlug = bookmark.record?.slug || bookmark.record?.id
                return String(questionSlug) !== String(recordId)
            } else if (bookmark.type === 'project') {
                const projectId = bookmark.record?.id
                return String(projectId) !== String(recordId)
            }
            return true
        })
    }
}


onMounted(() => {
    fetchBookmarks(false).then(() => {
        setupIntersectionObserver()
    })
})

onUnmounted(() => {
    if (observer.value) {
        observer.value.disconnect()
    }
})
</script>

<template>
    <div class="bookmarks-scroll-container max-w-2xl mx-auto flex flex-col gap-3 sm:px-6 pb-5">
        <TransitionGroup name="fade" appear>
            <BookmarkCard
                v-for="(bookmark, index) in bookmarks"
                :key="bookmark.id"
                :bookmark="bookmark"
                :class="index === bookmarks.length - 1 ? 'last-bookmark-item' : ''"
                @bookmarked-action="handleBookmarkedAction"
            />
        </TransitionGroup>

        <!-- Load More Trigger -->
        <div ref="loadMoreTrigger" class="h-20 flex items-center justify-center">
            <div v-if="loading" class="flex items-center gap-2 text-sm" :class="[
                themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
            ]">
                <Loader2 class="h-5 w-5 animate-spin" />
                <span>Loading more bookmarks...</span>
            </div>
            <div v-else-if="!hasMore && bookmarks.length > 0" class="text-sm py-8" :class="[
                themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
            ]">
                No more bookmarks to load
            </div>
        </div>

        <!-- Loading State -->
        <Transition name="fade">
            <div v-if="loading && bookmarks.length === 0" class="gap-4 grid w-full">
                <Skeleton v-for="i in 3" :key="i" class="w-full rounded-xl h-48" />
            </div>
        </Transition>

        <!-- Empty State -->
        <EmptyState
            v-if="!loading && hasLoaded && bookmarks.length === 0"
            icon="Bookmark"
            title="No saved items yet"
            subtitle="Start bookmarking posts, questions, and projects to see them here"
            size="small"
        />
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

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

