<script setup>
import { ref, computed } from 'vue'
import { useThemeStore } from '../../../stores/theme'
import { Card, CardContent } from '../../ui/card'
import { Hash, X, Check } from 'lucide-vue-next'

const props = defineProps({
    tags: {
        type: Array,
        default: () => []
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    selectedTags: {
        type: Array,
        default: () => []
    },
    color: {
        type: String,
        default: 'purple' // blue, green, purple, etc.
    }
})

const themeStore = useThemeStore()

const colorClasses = computed(() => {
    const colors = {
        blue: {
            icon: themeStore.isDark ? 'text-blue-400' : 'text-blue-600',
            selectedBg: themeStore.isDark ? 'bg-blue-900/30 text-blue-300 border-blue-700/50' : 'bg-blue-100 text-blue-700 border-blue-200',
            selectedButton: themeStore.isDark ? 'bg-blue-600 text-white border-blue-500' : 'bg-blue-600 text-white border-blue-500',
            badge: themeStore.isDark ? 'bg-blue-500/20 text-blue-300' : 'bg-blue-100 text-blue-700',
            link: 'hover:text-blue-600 dark:hover:text-blue-400'
        },
        green: {
            icon: themeStore.isDark ? 'text-green-400' : 'text-green-600',
            selectedBg: themeStore.isDark ? 'bg-green-900/30 text-green-300 border-green-700/50' : 'bg-green-100 text-green-700 border-green-200',
            selectedButton: themeStore.isDark ? 'bg-green-600 text-white border-green-500' : 'bg-green-600 text-white border-green-500',
            badge: themeStore.isDark ? 'bg-green-500/20 text-green-300' : 'bg-green-100 text-green-700',
            link: 'hover:text-green-600 dark:hover:text-green-400'
        },
        purple: {
            icon: themeStore.isDark ? 'text-purple-400' : 'text-purple-600',
            selectedBg: themeStore.isDark ? 'bg-purple-900/30 text-purple-300 border-purple-700/50' : 'bg-purple-100 text-purple-700 border-purple-200',
            selectedButton: themeStore.isDark ? 'bg-purple-600 text-white border-purple-500' : 'bg-purple-600 text-white border-purple-500',
            badge: themeStore.isDark ? 'bg-purple-500/20 text-purple-300' : 'bg-purple-100 text-purple-700',
            link: 'hover:text-purple-600 dark:hover:text-purple-400'
        }
    }
    return colors[props.color] || colors.purple
})

const emit = defineEmits(['tags-change'])

const selectedTagIds = ref(new Set(props.selectedTags || []))

const toggleTag = (tag) => {
    const tagId = typeof tag === 'object' ? tag.id : tag
    const tagName = typeof tag === 'object' ? tag.name : tag
    
    if (selectedTagIds.value.has(tagId)) {
        selectedTagIds.value.delete(tagId)
    } else {
        selectedTagIds.value.add(tagId)
    }
    
    const selected = Array.from(selectedTagIds.value).map(id => {
        const tagObj = props.tags.find(t => (typeof t === 'object' ? t.id : t) === id)
        return typeof tagObj === 'object' ? tagObj.name : tagObj
    })
    
    emit('tags-change', selected)
}

const isTagSelected = (tag) => {
    const tagId = typeof tag === 'object' ? tag.id : tag
    return selectedTagIds.value.has(tagId)
}

const getTagName = (tag) => {
    return typeof tag === 'string' ? tag : tag.name
}

const getTagCount = (tag) => {
    return typeof tag === 'object' && tag.count !== undefined ? tag.count : null
}

const clearAll = () => {
    selectedTagIds.value.clear()
    emit('tags-change', [])
}

const selectedTagsList = computed(() => {
    return props.tags.filter(tag => {
        const tagId = typeof tag === 'object' ? tag.id : tag
        return selectedTagIds.value.has(tagId)
    })
})
</script>

<template>
    <Card>
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Hash class="h-5 w-5" :class="colorClasses.icon" />
                    <h3 class="text-lg font-semibold" :class="[
                        themeStore.isDark ? 'text-white' : 'text-gray-900'
                    ]">
                        Filter by Tags
                    </h3>
                </div>
                <button
                    v-if="selectedTagsList.length > 0"
                    @click="clearAll"
                    class="text-xs font-medium hover:text-red-600 dark:hover:text-red-400 transition-colors" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]"
                >
                    Clear
                </button>
            </div>
            <p class="text-xs mt-1" :class="[
                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
            ]">
                {{ selectedTagsList.length > 0 ? `${selectedTagsList.length} selected` : 'Select tags to filter posts' }}
            </p>
        </div>

        <CardContent class="p-0">
            <!-- Loading State -->
            <div v-if="isLoading" class="px-6 py-6">
                <div class="flex flex-wrap gap-2">
                    <div 
                        v-for="i in 8" 
                        :key="i" 
                        class="h-8 rounded-full animate-pulse" :class="[
                            themeStore.isDark ? 'bg-gray-700' : 'bg-gray-200'
                        ]"
                        :style="{ width: `${Math.random() * 40 + 60}px` }"
                    ></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!isLoading && tags.length === 0" class="px-6 py-8 text-center">
                <Hash class="h-12 w-12 mx-auto mb-3 opacity-50" :class="[
                    themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                ]" />
                <p class="text-sm" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">
                    No tags available
                </p>
            </div>

            <!-- Selected Tags Display -->
            <div v-if="selectedTagsList.length > 0" class="px-6 pt-4 pb-2 border-b" :class="[
                themeStore.isDark ? 'border-gray-800' : 'border-gray-200'
            ]">
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="tag in selectedTagsList"
                        :key="typeof tag === 'object' ? tag.id : tag"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border" :class="colorClasses.selectedBg"
                    >
                        <Hash class="h-3 w-3" />
                        <span>{{ getTagName(tag) }}</span>
                        <button
                            @click.stop="toggleTag(tag)"
                            class="ml-0.5 hover:opacity-70 transition-opacity"
                        >
                            <X class="h-3 w-3" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tags List (Multiselect) -->
            <div class="px-6 py-4 max-h-64 overflow-y-auto">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tag in tags"
                        :key="typeof tag === 'object' ? tag.id : tag"
                        @click="toggleTag(tag)"
                        class="group relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200 hover:scale-105 border" :class="[
                            isTagSelected(tag)
                                ? colorClasses.selectedButton
                                : themeStore.isDark
                                    ? 'bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white border-gray-700 hover:border-gray-600'
                                    : 'bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 border-gray-200 hover:border-gray-300'
                        ]"
                    >
                        <Hash class="h-3.5 w-3.5" />
                        <span>{{ getTagName(tag) }}</span>
                        <span 
                            v-if="getTagCount(tag)"
                            class="text-xs px-1.5 py-0.5 rounded-full font-semibold" :class="[
                                isTagSelected(tag)
                                    ? 'bg-white/20 text-white'
                                    : colorClasses.badge
                            ]"
                        >
                            {{ getTagCount(tag) }}
                        </span>
                        <Check 
                            v-if="isTagSelected(tag)"
                            class="h-3.5 w-3.5 ml-0.5"
                        />
                    </button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
