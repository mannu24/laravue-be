<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import ActivityCard from './ActivityCard.vue'
import { Skeleton } from '../ui/skeleton'
import EmptyState from '../ui/EmptyState.vue'
import { useAuthStore } from '../../stores/auth'
import { useThemeStore } from '../../stores/theme'
import axios from 'axios'

const props = defineProps({
    username: {
        type: String,
        default: null
    }
})

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const activities = ref([])
const loading = ref(false)
const pageNo = ref(1)
const hasMore = ref(true)
const observer = ref(null)

const fetchActivities = async () => {
    if (loading.value || !hasMore.value) return
    
    loading.value = true
    try {
        const params = new URLSearchParams({
            page: pageNo.value.toString(),
            per_page: '15'
        })
        
        if (props.username) {
            params.append('username', props.username)
        }

        const response = await axios.get(`/api/v1/activities?${params.toString()}`, authStore.config)
        
        if (response.data.status === 'success' && response.data.data) {
            const newActivities = response.data.data.activities || []
            activities.value.push(...newActivities)
            hasMore.value = response.data.data.pagination?.has_more || false
        }
    } catch (error) {
        console.error('Error fetching activities:', error)
    } finally {
        loading.value = false
    }
}

const setupIntersectionObserver = () => {
    const options = {
        root: null,
        rootMargin: '100px',
        threshold: 0.1
    }

    observer.value = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore.value && !loading.value) {
                pageNo.value++
            }
        })
    }, options)

    // Observe the last activity card
    const lastCard = document.querySelector('.activity-scroll-container .last-activity-item')
    if (lastCard) {
        observer.value.observe(lastCard)
    }
}

watch(pageNo, () => {
    fetchActivities()
})

onMounted(() => {
    fetchActivities()
    // Setup observer after initial load
    setTimeout(() => {
        setupIntersectionObserver()
    }, 500)
})

onUnmounted(() => {
    if (observer.value) {
        observer.value.disconnect()
    }
})
</script>

<template>
    <div class="activity-scroll-container max-w-2xl mx-auto flex flex-col gap-4 sm:px-6 pb-5">
        <TransitionGroup name="fade" appear>
            <div
                v-for="(activity, index) in activities"
                :key="activity.id"
                :class="[
                    index === activities.length - 1 ? 'last-activity-item' : ''
                ]"
            >
                <ActivityCard :activity="activity" />
            </div>
        </TransitionGroup>

        <Transition name="fade">
            <div v-if="loading" class="gap-4 grid w-full">
                <Skeleton v-for="i in 3" :key="i" class="w-full rounded-xl h-32" />
            </div>
        </Transition>

        <div v-if="!hasMore && activities.length > 0" class="text-center py-8">
            <p class="text-sm" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">
                No more activities to load
            </p>
        </div>

        <EmptyState
            v-if="!loading && activities.length === 0"
            icon="Activity"
            title="No activities yet"
            subtitle="Activities will appear here as you interact with the platform"
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
</style>

