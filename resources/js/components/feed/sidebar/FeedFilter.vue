<script setup>
import { ref, computed } from 'vue'
import { useThemeStore } from '../../../stores/theme'
import { Card, CardContent } from '../../ui/card'
import { Filter, MessageCircle, TrendingUp, Heart, Clock, ArrowUpDown } from 'lucide-vue-next'

const themeStore = useThemeStore()
const isOpen = ref(false)

const props = defineProps({
    currentSort: {
        type: String,
        default: 'latest'
    },
    color: {
        type: String,
        default: 'blue' // blue, green, purple, etc.
    }
})

const colorClasses = computed(() => {
    const colors = {
        blue: {
            iconBg: themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600',
            activeBg: themeStore.isDark ? 'bg-blue-900/30 text-blue-400 border-blue-700/50' : 'bg-blue-50 text-blue-600 border-blue-200',
            activeIcon: themeStore.isDark ? 'text-blue-400' : 'text-blue-600',
            activeDot: themeStore.isDark ? 'bg-blue-400' : 'bg-blue-600'
        },
        green: {
            iconBg: themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600',
            activeBg: themeStore.isDark ? 'bg-green-900/30 text-green-400 border-green-700/50' : 'bg-green-50 text-green-600 border-green-200',
            activeIcon: themeStore.isDark ? 'text-green-400' : 'text-green-600',
            activeDot: themeStore.isDark ? 'bg-green-400' : 'bg-green-600'
        },
        purple: {
            iconBg: themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600',
            activeBg: themeStore.isDark ? 'bg-purple-900/30 text-purple-400 border-purple-700/50' : 'bg-purple-50 text-purple-600 border-purple-200',
            activeIcon: themeStore.isDark ? 'text-purple-400' : 'text-purple-600',
            activeDot: themeStore.isDark ? 'bg-purple-400' : 'bg-purple-600'
        }
    }
    return colors[props.color] || colors.blue
})

const emit = defineEmits(['sort-change'])

const sortOptions = [
    { 
        value: 'latest', 
        label: 'Latest', 
        icon: Clock,
        description: 'Most recent posts'
    },
    { 
        value: 'comments', 
        label: 'Most Comments', 
        icon: MessageCircle,
        description: 'Posts with most comments'
    },
    { 
        value: 'highest_rated', 
        label: 'Highest Rated', 
        icon: Heart,
        description: 'Most liked posts'
    },
    { 
        value: 'trending', 
        label: 'Trending', 
        icon: TrendingUp,
        description: 'Popular right now'
    }
]

const currentSortOption = computed(() => {
    return sortOptions.find(opt => opt.value === props.currentSort) || sortOptions[0]
})

const handleSortChange = (sortValue) => {
    emit('sort-change', sortValue)
    isOpen.value = false
}
</script>

<template>
    <Card>
        <CardContent class="p-0">
            <!-- Filter Header (Always Visible) -->
            <div 
                @click="isOpen = !isOpen"
                class="px-6 py-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg" :class="colorClasses.iconBg">
                            <Filter class="h-4 w-4" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold" :class="[
                                themeStore.isDark ? 'text-white' : 'text-gray-900'
                            ]">
                                Sort Posts
                            </h3>
                            <p class="text-xs mt-0.5" :class="[
                                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                            ]">
                                {{ currentSortOption.label }}
                            </p>
                        </div>
                    </div>
                    <ArrowUpDown class="h-4 w-4 transition-transform" :class="[
                        isOpen ? 'rotate-180' : '',
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                    ]" />
                </div>
            </div>

            <!-- Filter Options (Expandable) -->
            <Transition name="expand">
                <div v-if="isOpen" class="border-t" :class="[
                    themeStore.isDark ? 'border-gray-800 bg-gray-900/30' : 'border-gray-200 bg-gray-50/50'
                ]">
                    <div class="px-6 py-3 space-y-1">
                        <button
                            v-for="option in sortOptions"
                            :key="option.value"
                            @click="handleSortChange(option.value)"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition-all duration-200" :class="[
                                currentSort === option.value
                                    ? colorClasses.activeBg + ' border'
                                    : themeStore.isDark
                                        ? 'hover:bg-gray-800 text-gray-300'
                                        : 'hover:bg-gray-100 text-gray-700'
                            ]"
                        >
                            <component 
                                :is="option.icon" 
                                class="h-4 w-4 flex-shrink-0" 
                                :class="[
                                    currentSort === option.value
                                        ? colorClasses.activeIcon
                                        : themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                ]"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium">{{ option.label }}</div>
                                <div class="text-xs mt-0.5" :class="[
                                    themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                ]">
                                    {{ option.description }}
                                </div>
                            </div>
                            <!-- <div 
                                v-if="currentSort === option.value"
                                class="flex-shrink-0 w-2 h-2 rounded-full" :class="colorClasses.activeDot"
                            ></div> -->
                        </button>
                    </div>
                </div>
            </Transition>
        </CardContent>
    </Card>
</template>

<style scoped>
.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
}

.expand-enter-to,
.expand-leave-from {
    max-height: 500px;
    opacity: 1;
}
</style>

