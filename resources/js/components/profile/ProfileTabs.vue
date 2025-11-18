<script setup>
import { ref, computed } from 'vue'
import { useThemeStore } from '../../stores/theme'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import EmptyState from '../ui/EmptyState.vue'
import SocialLinks from './SocialLinks.vue'
import InfiniteScroll from '../elements/InfiniteScroll.vue'
import ActivityScroll from './ActivityScroll.vue'
import ProjectsInfiniteList from '../projects/ProjectsInfiniteList.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { 
    Code2, 
    Users, 
    Building2,
    FileText,
    Bookmark
} from 'lucide-vue-next'
import BookmarksScroll from './BookmarksScroll.vue'

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
</script>

<template>
    <div class="w-full">
        <Tabs :model-value="activeTab" @update:model-value="activeTab = $event" class="space-y-8">
            <!-- Tabs List - Consistent Design -->
            <TabsList :class="['grid w-full max-w-2xl mx-auto border-0 shadow-lg rounded-xl overflow-hidden',
                gridColsClass,
                themeStore.isDark 
                    ? 'bg-gray-800/50 border border-gray-700 backdrop-blur-sm' 
                    : 'bg-white/80 border border-gray-200 backdrop-blur-sm'
            ]">
                <TabsTrigger 
                    v-for="tab in tabs" 
                    :key="tab.value"
                    :value="tab.value"
                    class="data-[state=active]:bg-gray-900 data-[state=active]:text-white dark:data-[state=active]:bg-gray-700 transition-all duration-200"
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
            <TabsContent v-if="tabs.find(t => t.value === 'feed')" value="feed" class="mt-0">
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
            </TabsContent>

            <!-- Projects Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'projects')" value="projects" class="mt-0">
                <ProjectsInfiniteList
                    v-if="userId"
                    :user-id="userId"
                    :per-page="12"
                />
                <EmptyState
                    v-else
                    icon="FolderOpen"
                    title="Projects Coming Soon"
                    subtitle="User projects will be displayed here."
                    size="small"
                />
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
            <TabsContent v-if="tabs.find(t => t.value === 'social')" value="social" class="mt-0">
                <SocialLinks />
            </TabsContent>

            <!-- Activity Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'activity')" value="activity" class="mt-0">
                <ActivityScroll :username="isOwnProfile ? null : username" />
            </TabsContent>

            <!-- Saved/Bookmarks Tab Content -->
            <TabsContent v-if="tabs.find(t => t.value === 'saved')" value="saved" class="mt-0">
                <BookmarksScroll v-if="isOwnProfile" />
                <EmptyState
                    v-else
                    icon="Bookmark"
                    title="Saved Items"
                    subtitle="Only you can see your saved items."
                    size="small"
                />
            </TabsContent>
        </Tabs>
    </div>
</template>

<style scoped>
/* Ensure smooth transitions */
:deep(.tabs-content) {
    transition: opacity 0.3s ease;
}
</style>

