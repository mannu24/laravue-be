<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useThemeStore } from '@/stores/theme'
import { ChevronLeft } from 'lucide-vue-next'

const props = defineProps({
    url: {
        type: String,
        default: ''
    },
    items: {
        type: Array,
        default: () => [],
        validator: (value) => {
            return value.every(item =>
                typeof item === 'object' &&
                'name' in item &&
                'href' in item
            )
        }
    },
    // Option to disable back button
    showBackButton: {
        type: Boolean,
        default: true
    }
})

const route = useRoute()
const themeStore = useThemeStore()

// Computed property to determine text color class based on route (same logic as App.vue)
const routeColorClass = computed(() => {
    const path = route.path
    
    if (path.startsWith('/feed')) {
        return 'text-green-500' // Vue green
    } else if (path.startsWith('/qna')) {
        return 'text-red-600' // Laravel red
    } else if (path.startsWith('/dashboard')) {
        return 'text-blue-500' // Blue
    } else if (path.startsWith('/projects')) {
        return 'text-purple-500' // Purple
    } else if (path.startsWith('/about')) {
        return 'text-red-500' // Danger red
    } else if (path.startsWith('/home') || path === '/') {
        // For white, use a subtle gray that works on both light/dark backgrounds
        return themeStore.isDark ? 'text-white' : 'text-gray-900' // White for dark mode, dark gray for light mode
    }
    
    // Default color
    return 'text-blue-600'
})

const segments = computed(() => {
    // If items array is provided, use it directly
    if (props.items.length > 0) {
        return props.items.map((item, index) => ({
            label: item.name,
            path: item.href,
            isLast: index === props.items.length - 1,
            exists: true // All manually provided items are assumed to exist
        }))
    }

    // Otherwise parse from URL
    const parts = props.url.replace(/^\/|\/$/g, '').split('/')
    return parts.map((part, index) => ({
        label: part.replace(/-/g, ' '),
        path: '/' + parts.slice(0, index + 1).join('/'),
        isLast: index === parts.length - 1,
        exists: true // You might want to check existence here based on your routes
    }))
})

const hasValidSegments = computed(() =>
    segments.value.length > 0 && segments.value.some(segment => segment.exists)
)
</script>

<template>
    <nav v-if="hasValidSegments" class="flex flex-wrap items-center gap-2 text-sm my-4">
        <!-- Back Button -->
        <!-- <router-link v-if="showBackButton" :to="segments[0]?.path || '/'" class="inline-flex items-center px-2.5 py-1.5 text-sm font-medium rounded-md
            text-gray-300 hover:text-white transition-colors duration-200
            hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
            <ChevronLeft class="w-4 h-4 mr-1" />
            Back
        </router-link> -->

        <!-- Breadcrumb Trail -->
        <div class="flex flex-wrap items-center">
            <template v-for="(segment, index) in segments" :key="segment.path">
                <!-- Separator -->
                <span v-if="index > 0" class="mx-2 text-gray-500" aria-hidden="true">/</span>

                <!-- Segment -->
                <template v-if="segment.exists">
                    <router-link v-if="!segment.isLast" :to="segment.path" class="px-2 py-1 rounded-md transition-colors duration-200
                            text-gray-400 hover:text-white hover:bg-gray-800
                            focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        {{ segment.label }}
                    </router-link>
                    <span v-else :class="['px-2 py-1 font-medium', routeColorClass]">
                        {{ segment.label }}
                    </span>
                </template>
            </template>
        </div>
    </nav>
</template>