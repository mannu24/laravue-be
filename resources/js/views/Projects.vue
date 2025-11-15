<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Search,
  Filter,
  Star,
  Eye,
  Download,
  Heart,
  Code2,
  Zap,
  ChevronDown,
  Calendar,
  User,
  Folder,
  ArrowRight,
  Plus,
  Loader2,
  Github
} from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'
import GitHubImportModal from '@/components/github/GitHubImportModal.vue'
import { useNavbarSearch } from '@/composables/useNavbarSearch'
import ProjectsInfiniteList from '@/components/projects/ProjectsInfiniteList.vue'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()
const { openSearch } = useNavbarSearch()

// Reactive data
const projects = ref([])
const loading = ref(false)
const searchQuery = ref('')
const selectedFilter = ref('all')
const selectedSort = ref('recent')
const currentPage = ref(1)
const hasMore = ref(true)
const technologies = ref([])
const selectedTechnology = ref('')
const showGitHubModal = ref(false)

const handleProjectImported = () => {
  // Refresh projects list
  fetchProjects()
}

// Computed
const filteredProjects = computed(() => {
  return projects.value
})

const stats = ref([
  { label: 'Total Projects', value: '0', icon: Folder },
  { label: 'Downloads', value: '0', icon: Download },
  { label: 'Stars Given', value: '0', icon: Star },
  { label: 'Active Users', value: '0', icon: User },
])

// Methods
const fetchTechnologies = async () => {
  try {
    const response = await axios.get('/api/v1/projects/technologies')
    technologies.value = response.data.data
  } catch (error) {
    console.error('Error fetching technologies:', error)
  }
}

onMounted(() => {
  fetchTechnologies()
})
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 -z-10">
        <div :class="['absolute inset-0 transition-all duration-500',
          themeStore.isDark
            ? 'bg-gradient-to-br from-gray-900 via-blue-900/10 to-gray-900'
            : 'bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50'
        ]"></div>

        <!-- Grid Pattern -->
        <div :class="['absolute inset-0 opacity-20',
          themeStore.isDark ? 'bg-grid-white/[0.05]' : 'bg-grid-black/[0.05]'
        ]"
          style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 30px 30px;">
        </div>
      </div>

      <div class="container mx-auto px-4 py-20 sm:px-6 lg:px-8">
        <div class="text-center max-w-4xl mx-auto">
          <div class="flex items-center justify-center mb-6">
            <div
              class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-2xl">
              <Code2 class="h-8 w-8 text-white" />
            </div>
          </div>

          <h1 class="text-5xl md:text-6xl font-bold tracking-tight mb-6 leading-tight">
            <span class="font-semibold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Discover </span>
            <span class="bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500 bg-clip-text text-transparent font-bold">
              Amazing Projects
            </span>
          </h1>

          <p class="text-lg md:text-xl mb-12 leading-relaxed max-w-3xl mx-auto font-normal"
            :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
            Explore thousands of high-quality projects from talented developers. Find the perfect solution
            for your next project or get inspired by innovative ideas from our community.
          </p>

          <!-- Search Bar -->
          <div class="relative max-w-2xl mx-auto mb-8">
            <div class="absolute inset-y-0 z-10 left-0 pl-4 flex items-center pointer-events-none">
              <Search class="h-5 w-5 text-gray-400" />
            </div>
            <Input
              v-model="searchQuery"
              type="text"
              placeholder="Search projects, technologies, or authors..."
              class="pl-12 pr-4 py-5 h-12 text-lg rounded-xl border-0 shadow-xl backdrop-blur-sm transition-all duration-300 focus:ring-2 focus:ring-blue-500 cursor-pointer"
              :class="themeStore.isDark ? 'bg-gray-800/90 text-white' : 'bg-white/90'"
              @click="openSearch(searchQuery)"
              readonly
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-10">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card v-for="stat in stats" :key="stat.label" :class="['text-center transition-all duration-300 hover:scale-105 border-0 shadow-xl backdrop-blur-sm',
          themeStore.isDark ? 'bg-gray-800/90' : 'bg-white/90'
        ]">
          <CardContent class="p-4">
            <div class="flex justify-center mb-2">
              <div
                class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                <component :is="stat.icon" class="h-4 w-4 text-white" />
              </div>
            </div>
            <div
              class="text-3xl font-semibold mb-2 bg-gradient-to-r from-blue-500 to-purple-500 bg-clip-text text-transparent">
              {{ stat.value }}
            </div>
            <div class="text-sm font-normal tracking-wide" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
              {{ stat.label }}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Filters and Projects -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <!-- Filter Bar -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-12">
        <div class="flex flex-wrap gap-3">
          <Button variant="ghost" :class="[
            'transition-all duration-200 text-sm font-medium',
            selectedFilter === 'all'
              ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 dark:bg-blue-500/20'
              : 'hover:bg-gray-100 dark:hover:bg-gray-800'
          ]" @click="selectedFilter = 'all'">
            All Projects
          </Button>
          <Button variant="ghost" :class="[
            'transition-all duration-200 text-sm font-medium',
            selectedFilter === 'sellable'
              ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 dark:bg-blue-500/20'
              : 'hover:bg-gray-100 dark:hover:bg-gray-800'
          ]" @click="selectedFilter = 'sellable'">
            <Zap class="h-4 w-4 mr-2" />
            Premium
          </Button>
          <Button variant="ghost" :class="[
            'transition-all duration-200 text-sm font-medium',
            selectedFilter === 'open'
              ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 dark:bg-blue-500/20'
              : 'hover:bg-gray-100 dark:hover:bg-gray-800'
          ]" @click="selectedFilter = 'open'">
            <Heart class="h-4 w-4 mr-2" />
            Open Source
          </Button>
        </div>

        <div class="flex gap-3">
          <!-- Technology Filter -->
          <DropdownMenu>
            <DropdownMenuTrigger as="div">
              <Button variant="outline"
                class="text-sm font-medium transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-300'">
                <Code2 class="h-4 w-4 mr-2" />
                {{ selectedTechnology || 'Technology' }}
                <ChevronDown class="h-4 w-4 ml-2" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-48 max-h-60 overflow-y-auto">
              <DropdownMenuItem @click="selectedTechnology = ''">All Technologies</DropdownMenuItem>
              <DropdownMenuItem
                v-for="tech in technologies"
                :key="tech.id"
                @click="selectedTechnology = tech.name"
              >
                {{ tech.name }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <!-- Sort Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as="div">
              <Button variant="outline"
                class="text-sm font-medium transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-300'">
                <Filter class="h-4 w-4 mr-2" />
                Sort by
                <ChevronDown class="h-4 w-4 ml-2" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-48">
              <DropdownMenuItem @click="selectedSort = 'recent'">Most Recent</DropdownMenuItem>
              <DropdownMenuItem @click="selectedSort = 'popular'">Most Popular</DropdownMenuItem>
              <DropdownMenuItem @click="selectedSort = 'views'">Most Viewed</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <!-- Add Project Button -->
          <Button
            v-if="authStore.isAuthenticated"
            class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white text-sm font-medium shadow-sm hover:shadow-md transition-all duration-200"
            @click="router.push('/add-project')"
          >
            <Plus class="h-4 w-4 mr-2" />
            Add Project
          </Button>
        </div>
      </div>

      <!-- Projects Infinite List -->
      <ProjectsInfiniteList
        :filters="{
          search: searchQuery,
          type: selectedFilter,
          sort: selectedSort,
          technology: selectedTechnology
        }"
        :per-page="12"
      />
    </div>

    <!-- CTA Section -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
      <Card :class="['relative overflow-hidden border shadow-lg transition-all duration-300',
        themeStore.isDark 
          ? 'bg-gray-900/50 border-gray-800/50 backdrop-blur-sm' 
          : 'bg-white border-gray-200/50 backdrop-blur-sm'
      ]">
        <!-- Subtle gradient accent -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500/50 via-purple-500/50 to-indigo-500/50">
        </div>

        <CardContent class="p-8 md:p-12 text-center">
          <!-- Icon -->
          <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500/10 to-purple-500/10 flex items-center justify-center border"
              :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-200'">
              <Code2 class="h-8 w-8 text-vue" />
            </div>
          </div>

          <!-- Title -->
          <h2 class="text-3xl md:text-4xl font-semibold mb-4 leading-tight tracking-tight"
            :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Ready to Share Your Project?
          </h2>
          
          <!-- Description -->
          <p class="text-base md:text-lg mb-8 max-w-2xl mx-auto leading-relaxed font-normal"
            :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
            Join thousands of developers who showcase their work on LaraVue.
            Upload your project today and reach a global audience of fellow developers.
          </p>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <Button
              class="bg-vue hover:bg-vue/90 text-white px-6 py-2.5 text-base font-medium shadow-sm hover:shadow-md transition-all duration-200"
              @click="showGitHubModal = true">
              <Github class="h-4 w-4 mr-2" />
              Import from GitHub
            </Button>
            <Button
              variant="outline"
              class="px-6 py-2.5 text-base font-medium border transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800"
              :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-300'"
              @click="router.push('/add-project')">
              <Code2 class="h-4 w-4 mr-2" />
              Upload Project
            </Button>
            <Button 
              variant="ghost" 
              class="px-6 py-2.5 text-base font-medium transition-all duration-200"
              :class="themeStore.isDark ? 'hover:bg-gray-800' : 'hover:bg-gray-50'"
              @click="router.push('/about')">
              Learn More
              <ArrowRight class="h-4 w-4 ml-2" />
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- GitHub Import Modal -->
    <GitHubImportModal
      v-model:open="showGitHubModal"
      @imported="handleProjectImported"
    />
  </div>
</template>

<style scoped>
/* Grid background */
.bg-grid-white\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

.bg-grid-black\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
}

/* Line clamp utility */
.line-clamp-3 {
  display: -webkit-box;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
* {
  transition-property: transform, opacity, background-color, border-color, color, fill, stroke, box-shadow;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
