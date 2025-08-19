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
  Loader2
} from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

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
const fetchProjects = async (reset = false) => {
  if (loading.value) return

  loading.value = true

  try {
    const params = {
      page: reset ? 1 : currentPage.value,
      search: searchQuery.value,
      type: selectedFilter.value,
      sort: selectedSort.value,
      technology: selectedTechnology.value,
      per_page: 12
    }

    const response = await axios.get('/api/v1/projects', { params })

    if (reset) {
      projects.value = response.data.data
      currentPage.value = 1
    } else {
      projects.value = [...projects.value, ...response.data.data]
    }

    hasMore.value = response.data.current_page < response.data.last_page
    currentPage.value++

  } catch (error) {
    console.error('Error fetching projects:', error)
    toast({
      title: "Error",
      description: "Failed to load projects",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const fetchTechnologies = async () => {
  try {
    const response = await axios.get('/api/v1/projects/technologies')
    technologies.value = response.data.data
  } catch (error) {
    console.error('Error fetching technologies:', error)
  }
}

const upvoteProject = async (project) => {
  if (!authStore.isAuthenticated) {
    toast({
      title: "Authentication Required",
      description: "Please login to upvote projects",
      variant: "destructive"
    })
    return
  }

  try {
    const response = await axios.post(`/api/v1/projects/${project.id}/upvote`, {}, authStore.config)
    project.is_upvoted_by_user = response.data.data.is_upvoted
    project.upvotes_count = response.data.data.upvotes_count

    toast({
      title: "Success",
      description: response.data.message,
    })
  } catch (error) {
    console.error('Error upvoting project:', error)
    toast({
      title: "Error",
      description: "Failed to upvote project",
      variant: "destructive"
    })
  }
}

const fundProject = async (project, amount) => {
  if (!authStore.isAuthenticated) {
    toast({
      title: "Authentication Required",
      description: "Please login to fund projects",
      variant: "destructive"
    })
    return
  }

  try {
    const response = await axios.post(`/api/v1/projects/${project.id}/fund`, { amount }, authStore.config)
    toast({
      title: "Success",
      description: response.data.message,
    })
  } catch (error) {
    console.error('Error funding project:', error)
    toast({
      title: "Error",
      description: "Failed to fund project",
      variant: "destructive"
    })
  }
}

const handleSearch = () => {
  fetchProjects(true)
}

const handleFilterChange = () => {
  fetchProjects(true)
}

const handleSortChange = () => {
  fetchProjects(true)
}

const loadMore = () => {
  if (hasMore.value && !loading.value) {
    fetchProjects()
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays === 1) return '1 day ago'
  if (diffDays < 7) return `${diffDays} days ago`
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`
  if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`
  return `${Math.floor(diffDays / 365)} years ago`
}

// Watchers
watch([searchQuery, selectedFilter, selectedSort, selectedTechnology], () => {
  fetchProjects(true)
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchProjects(true)
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

          <h1 class="text-6xl font-extrabold tracking-tight mb-8">
            <span :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Discover </span>
            <span class="bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500 bg-clip-text text-transparent">
              Amazing Projects
            </span>
          </h1>

          <p class="text-xl mb-12 leading-relaxed max-w-3xl mx-auto"
            :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Explore thousands of high-quality projects from talented developers. Find the perfect solution
            for your next project or get inspired by innovative ideas from our community.
          </p>

          <!-- Search Bar -->
          <div class="relative max-w-2xl mx-auto mb-8">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <Search class="h-5 w-5 text-gray-400" />
            </div>
            <Input
              v-model="searchQuery"
              type="text"
              placeholder="Search projects, technologies, or authors..."
              class="pl-12 pr-4 py-4 text-lg rounded-xl border-0 shadow-xl backdrop-blur-sm transition-all duration-300 focus:ring-2 focus:ring-blue-500"
              :class="themeStore.isDark ? 'bg-gray-800/90 text-white' : 'bg-white/90'"
              @keyup.enter="handleSearch"
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
              class="text-2xl font-bold mb-1 bg-gradient-to-r from-blue-500 to-purple-500 bg-clip-text text-transparent">
              {{ stat.value }}
            </div>
            <div class="text-xs font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
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
            'transition-all duration-300',
            selectedFilter === 'all'
              ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400'
              : 'hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10'
          ]" @click="selectedFilter = 'all'">
            All Projects
          </Button>
          <Button variant="ghost" :class="[
            'transition-all duration-300',
            selectedFilter === 'sellable'
              ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400'
              : 'hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10'
          ]" @click="selectedFilter = 'sellable'">
            <Zap class="h-4 w-4 mr-2" />
            Premium
          </Button>
          <Button variant="ghost" :class="[
            'transition-all duration-300',
            selectedFilter === 'open'
              ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400'
              : 'hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10'
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
                class="hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10 transition-all duration-300">
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
                class="hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10 transition-all duration-300">
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
            class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
            @click="router.push('/add-project')"
          >
            <Plus class="h-4 w-4 mr-2" />
            Add Project
          </Button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && projects?.length === 0" class="text-center py-20">
        <Loader2 class="h-12 w-12 animate-spin mx-auto mb-4 text-blue-500" />
        <p class="text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
          Loading projects...
        </p>
      </div>

      <!-- Projects Grid -->
      <div v-else-if="projects?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Card v-for="project in filteredProjects" :key="project.id" :class="['group transition-all duration-300 hover:-translate-y-2 border-0 shadow-xl cursor-pointer overflow-hidden',
          themeStore.isDark ? 'bg-gray-800/90 hover:bg-gray-800' : 'bg-white/90 hover:bg-white'
        ]" @click="router.push(`/projects/${project.id}`)">
          <!-- Project Image -->
          <div class="relative overflow-hidden">
            <img
              :src="project.featured_image || 'https://picsum.photos/400/250?random=' + project.id"
              :alt="project.title"
              class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
            <div class="absolute top-4 right-4">
              <Badge :class="[
                'text-white font-medium shadow-lg',
                project.is_sellable
                  ? 'bg-gradient-to-r from-yellow-500 to-orange-500'
                  : 'bg-gradient-to-r from-green-500 to-emerald-500'
              ]">
                {{ project.is_sellable ? 'Premium' : 'Open Source' }}
              </Badge>
            </div>
          </div>

          <!-- Project Content -->
          <CardContent class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold mb-2 group-hover:text-blue-500 transition-colors duration-300"
                  :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  {{ project.title }}
                </h3>
                <div class="flex items-center gap-2 mb-3 text-sm text-muted-foreground">
                  <User class="h-4 w-4" />
                  <span>{{ project.user?.name }}</span>
                  <span>•</span>
                  <Calendar class="h-4 w-4" />
                  <span>{{ formatDate(project.created_at) }}</span>
                </div>
              </div>
              <div v-if="project.selling_price" class="text-lg font-bold text-yellow-500">
                ${{ project.selling_price }}
              </div>
            </div>

            <p class="text-sm mb-4 line-clamp-3 leading-relaxed"
              :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              {{ project.description }}
            </p>

            <!-- Tags -->
            <div class="flex flex-wrap gap-2 mb-4">
              <Badge v-for="tech in project.technologies?.slice(0, 3)" :key="tech.id" variant="secondary" class="text-xs">
                {{ tech.name }}
              </Badge>
              <Badge v-if="project.technologies?.length > 3" variant="secondary" class="text-xs">
                +{{ project.technologies.length - 3 }}
              </Badge>
            </div>

            <!-- Stats -->
            <div class="flex items-center justify-between pt-4 border-t"
              :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-200'">
              <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                <button
                  @click.stop="upvoteProject(project)"
                  class="flex items-center hover:text-blue-500 transition-colors"
                  :class="project.is_upvoted_by_user ? 'text-blue-500' : ''"
                >
                  <Star class="h-4 w-4 mr-1" :class="project.is_upvoted_by_user ? 'fill-current' : ''" />
                  <span>{{ project.upvotes_count || 0 }}</span>
                </button>
                <div class="flex items-center">
                  <Eye class="h-4 w-4 mr-1" />
                  <span>{{ project.views?.toLocaleString() || 0 }}</span>
                </div>
              </div>

              <ArrowRight
                class="h-4 w-4 text-muted-foreground group-hover:text-blue-500 group-hover:translate-x-1 transition-all duration-300" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading" class="text-center py-20">
        <div
          class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center mx-auto mb-6 opacity-50">
          <Search class="h-12 w-12 text-white" />
        </div>
        <h3 class="text-2xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
          No Projects Found
        </h3>
        <p class="text-lg mb-6" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
          Try adjusting your search criteria or filters to find more projects.
        </p>
        <Button @click="searchQuery = ''; selectedFilter = 'all'; selectedTechnology = ''"
          class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white">
          Clear Filters
        </Button>
      </div>

      <!-- Load More Button -->
      <div v-if="hasMore && projects?.length > 0" class="text-center mt-16">
        <Button
          variant="outline"
          :disabled="loading"
          class="px-8 py-3 text-lg font-medium border-2 hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10 transition-all duration-300"
          @click="loadMore"
        >
          <Loader2 v-if="loading" class="h-5 w-5 mr-2 animate-spin" />
          <ArrowRight v-else class="h-5 w-5 mr-2" />
          {{ loading ? 'Loading...' : 'Load More Projects' }}
        </Button>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-24">
      <Card :class="['relative overflow-hidden border-0 shadow-2xl',
        themeStore.isDark ? 'bg-gray-800' : 'bg-white'
      ]">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500">
        </div>

        <CardContent class="p-12 text-center">
          <h2 class="text-4xl font-bold mb-6" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Ready to Share Your Project?
          </h2>
          <p class="text-xl mb-8 max-w-2xl mx-auto" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Join thousands of developers who showcase their work on LaraVue.
            Upload your project today and reach a global audience of fellow developers.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Button
              class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-8 py-3 text-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300"
              @click="router.push('/add-project')">
              <Code2 class="h-5 w-5 mr-2" />
              Upload Project
            </Button>
            <Button variant="outline" class="px-8 py-3 text-lg font-medium border-2" @click="router.push('/about')">
              Learn More
              <ArrowRight class="h-5 w-5 ml-2" />
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
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
