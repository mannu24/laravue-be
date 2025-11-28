<script setup>
import { ref } from 'vue'
import FeedFilter from './FeedFilter.vue'
import RecommendedUsers from './RecommendedUsers.vue'
import PopularTags from './PopularTags.vue'

const props = defineProps({
    recommendedUsers: {
        type: Array,
        default: () => []
    },
    popularTags: {
        type: Array,
        default: () => []
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    currentSort: {
        type: String,
        default: 'latest'
    },
    selectedTags: {
        type: Array,
        default: () => []
    },
    color: {
        type: String,
        default: 'green' // Vue green for feed
    }
})

const emit = defineEmits(['follow', 'unfollow', 'sort-change', 'tags-change'])

const handleFollow = (user) => {
    emit('follow', user)
}

const handleUnfollow = (user) => {
    emit('unfollow', user)
}

const handleSortChange = (sortValue) => {
    emit('sort-change', sortValue)
}
</script>

<template>
    <aside class="w-full lg:w-80 flex-shrink-0 space-y-6 lg:sticky lg:top-8 lg:self-start">
        <!-- Feed Filter (At Top) -->
        <FeedFilter 
            :current-sort="currentSort"
            :color="color"
            @sort-change="handleSortChange"
        />

        <!-- Popular Tags -->
        <!-- <PopularTags 
            :tags="popularTags"
            :is-loading="isLoading"
            :selected-tags="selectedTags"
            :color="color"
            @tags-change="(tags) => emit('tags-change', tags)"
        /> -->

        <!-- Recommended Users -->
        <RecommendedUsers 
            :users="recommendedUsers"
            :is-loading="isLoading"
            :color="color"
            @follow="handleFollow"
            @unfollow="handleUnfollow"
        />
    </aside>
</template>
