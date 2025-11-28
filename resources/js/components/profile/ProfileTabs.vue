<script setup>
import { ref, computed, watch } from 'vue'
import { useThemeStore } from '../../stores/theme'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import EmptyState from '../ui/EmptyState.vue'
import SocialLinks from './SocialLinks.vue'
import InfiniteScroll from '../elements/InfiniteScroll.vue'
import ActivityScroll from './ActivityScroll.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { 
    Code2, 
    Users, 
    Building2,
    FileText,
    Bookmark,
    LayoutDashboard
} from 'lucide-vue-next'
import BookmarksScroll from './BookmarksScroll.vue'
import DashboardTab from './DashboardTab.vue'

const props = defineProps({
    // Tab configuration
    tabs: {
        type: Array,
        default: () => [
            { value: 'feed', label: 'Feed', icon: FileText },
            { value: 'projects', label: 'Projects', icon: Building2 },
            // { value: 'blogs', label: 'Blogs', icon: FileText }
        ]
    },
    defaultTab: {
        type: String,
        default: 'feed'
    },
    // User ID for projects
    userId: {
        type: [Number, String],
        default: null
    },
    // For user profile feed
    username: {
        type: String,
        default: null
    },
    // For own profile
    isOwnProfile: {
        type: Boolean,
        default: false
    },
    // Projects data (if available)
    projects: {
        type: Array,
        default: () => []
    }
})

const themeStore = useThemeStore()
const activeTab = ref(props.defaultTab)
const slideDirection = ref('right') // 'left' or 'right'
const previousTabIndex = ref(0)

// Get tab index by value
const getTabIndex = (tabValue) => {
  return props.tabs.findIndex(tab => tab.value === tabValue)
}

// Handle tab change with direction detection
const handleTabChange = (newTab) => {
  const currentIndex = getTabIndex(activeTab.value)
  const newIndex = getTabIndex(newTab)
  
  // Determine slide direction
  if (newIndex > currentIndex) {
    slideDirection.value = 'left' // Moving forward (slide left)
  } else if (newIndex < currentIndex) {
    slideDirection.value = 'right' // Moving backward (slide right)
  }
  
  previousTabIndex.value = currentIndex
  activeTab.value = newTab
}

// Watch for changes in defaultTab prop (e.g., from route query)
watch(() => props.defaultTab, (newTab) => {
  if (newTab && newTab !== activeTab.value) {
    handleTabChange(newTab)
  }
}, { immediate: true })

// Computed class for grid columns
const gridColsClass = computed(() => {
    const count = props.tabs.length
    const classes = {
        2: 'grid-cols-2',
        3: 'grid-cols-3',
        4: 'grid-cols-4',
        5: 'grid-cols-5'
    }
    return classes[count] || 'grid-cols-3'
})

// Computed class for slide direction
const slideClass = computed(() => {
  return `slide-${slideDirection.value}`
})
</script>

<template>
    <div class="w-full">
        <Tabs :model-value="activeTab" @update:model-value="handleTabChange" class="bg-transparent border-0 space-y-5">
            <!-- Tabs List - Consistent Design -->
            <TabsList :class="['grid w-full max-w-2xl !p-0 !h-[unset] mx-auto border-0 shadow-lg overflow-hidden',
                gridColsClass,
                themeStore.isDark 
                    ? 'bg-gray-800/50 border border-gray-700 backdrop-blur-sm' 
                    : 'bg-white/80 border border-gray-200 backdrop-blur-sm'
            ]">
                <TabsTrigger 
                    v-for="(tab, index) in tabs" 
                    :key="tab.value"
                    :value="tab.value"
                    :class="[
                        'data-[state=active]:bg-gray-900 data-[state=active]:text-white dark:data-[state=active]:bg-gray-700 transition-all duration-200 py-3',
                        ((index != 0) || index != (tabs.length - 1)) ? '!rounded-none' : ''
                    ]"
                >
                    <component 
                        v-if="tab.icon" 
                        :is="tab.icon" 
                        class="h-4 w-4 mr-2" 
                    />
                    {{ tab.label }}
                </TabsTrigger>
            </TabsList>

            <!-- Feed Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'feed')" value="feed" class="mt-0 tab-content-wrapper">
                <Transition :name="`slide-${slideDirection}`" mode="out-in">
                    <div v-if="activeTab === 'feed'" :key="activeTab" class="tab-content-inner">
                        <InfiniteScroll 
                            v-if="username"
                            scrolling="post"
                            :fetchKey="null"
                            :username="username"
                        />
                        <EmptyState
                            v-else
                            icon="FileText"
                            title="Feed Coming Soon"
                            subtitle="This feature will be available in a future update."
                            size="small"
                        />
                    </div>
                </Transition>
            </TabsContent>

            <!-- Projects Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'projects')" value="projects" class="mt-0 tab-content-wrapper">
                <Transition :name="`slide-${slideDirection}`" mode="out-in">
                    <div v-if="activeTab === 'projects'" :key="activeTab" class="tab-content-inner">
                        <InfiniteScroll
                            v-if="userId"
                            scrolling="project"
                            :user-id="Number(userId)"
                            :per-page="12"
                        />
                        <EmptyState
                            v-else
                            icon="FolderOpen"
                            title="Projects Coming Soon"
                            subtitle="User projects will be displayed here."
                            size="small"
                        />
                    </div>
                </Transition>
            </TabsContent>

            <!-- Blogs Tab Content -->
            <!-- <TabsContent v-if="tabs.find(t => t.value === 'blogs')" value="blogs" class="mt-0">
                <div class="text-center py-12">
                    <FileText class="h-16 w-16 mx-auto mb-4 opacity-50" :class="[
                        themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                    ]" />
                    <p class="text-lg font-medium mb-2" :class="[
                        themeStore.isDark ? 'text-white' : 'text-gray-900'
                    ]">Blogs Coming Soon</p>
                    <p class="text-sm" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]">This feature will be available in a future update.</p>
                </div>
            </TabsContent> -->

            <!-- Social Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'social')" value="social" class="mt-0 tab-content-wrapper">
                <Transition :name="`slide-${slideDirection}`" mode="out-in">
                    <div v-if="activeTab === 'social'" :key="activeTab" class="tab-content-inner">
                        <SocialLinks :read-only="true" />
                    </div>
                </Transition>
            </TabsContent>

            <!-- Activity Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'activity')" value="activity" class="mt-0 tab-content-wrapper">
                <Transition :name="`slide-${slideDirection}`" mode="out-in">
                    <div v-if="activeTab === 'activity'" :key="activeTab" class="tab-content-inner">
                        <ActivityScroll :username="isOwnProfile ? null : username" />
                    </div>
                </Transition>
            </TabsContent>

            <!-- Saved/Bookmarks Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'saved')" value="saved" class="mt-0 tab-content-wrapper">
                <Transition :name="`slide-${slideDirection}`" mode="out-in">
                    <div v-if="activeTab === 'saved'" :key="activeTab" class="tab-content-inner">
                        <BookmarksScroll v-if="isOwnProfile" />
                        <EmptyState
                            v-else
                            icon="Bookmark"
                            title="Saved Items"
                            subtitle="Only you can see your saved items."
                            size="small"
                        />
                    </div>
                </Transition>
            </TabsContent>

            <!-- Dashboard Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'dashboard')" value="dashboard" class="mt-0 tab-content-wrapper">
                <Transition :name="`slide-${slideDirection}`" mode="out-in">
                    <div v-if="activeTab === 'dashboard'" :key="activeTab" class="tab-content-inner">
                        <DashboardTab v-if="isOwnProfile" />
                        <EmptyState
                            v-else
                            icon="LayoutDashboard"
                            title="Dashboard"
                            subtitle="Only you can see your dashboard."
                            size="small"
                        />
                    </div>
                </Transition>
            </TabsContent>
        </Tabs>
    </div>
</template>

<style scoped>
/* Tab content wrapper */
.tab-content-wrapper {
    position: relative;
    overflow: hidden;
    min-height: 200px;
}

.tab-content-inner {
    width: 100%;
}

/* Slide Left Animation (moving forward: tab 1 -> tab 3) */
.slide-left-enter-active,
.slide-left-leave-active {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}

.slide-left-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.slide-left-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.slide-left-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.slide-left-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

/* Slide Right Animation (moving backward: tab 3 -> tab 1) */
.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}

.slide-right-enter-from {
    transform: translateX(-100%);
    opacity: 0;
}

.slide-right-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.slide-right-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.slide-right-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .slide-left-enter-active,
    .slide-left-leave-active,
    .slide-right-enter-active,
    .slide-right-leave-active {
        transition: opacity 0.2s ease !important;
    }
    
    .slide-left-enter-from,
    .slide-left-leave-to,
    .slide-right-enter-from,
    .slide-right-leave-to {
        transform: none !important;
    }
}
</style>

