<script setup>
import { ref, computed } from 'vue'
import { useThemeStore } from '../stores/theme'
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
  ArrowRight
} from 'lucide-vue-next'

const themeStore = useThemeStore()
const searchQuery = ref('')
const selectedFilter = ref('all')
const selectedSort = ref('popular')

const projects = ref([
  {
    id: 1,
    title: 'Vue Task Manager Pro',
    description: 'A comprehensive project management solution built with Vue 3, TypeScript, and modern development practices. Features include real-time collaboration, advanced filtering, and beautiful UI components.',
    type: 'Premium',
    price: '$49',
    stars: 2450,
    downloads: 5600,
    views: 12300,
    language: 'Vue.js',
    author: 'John Doe',
    updated: '2 days ago',
    image: 'https://picsum.photos/400/250?random=1',
    tags: ['Vue.js', 'TypeScript', 'Tailwind CSS', 'Pinia']
  },
  {
    id: 2,
    title: 'Laravel API Starter Kit',
    description: 'Modern content management system for developers with a clean, intuitive interface. Built with the latest Laravel features and includes comprehensive documentation.',
    type: 'Open Source',
    stars: 8900,
    downloads: 15000,
    views: 45600,
    language: 'PHP',
    author: 'Jane Smith',
    updated: '5 days ago',
    image: 'https://picsum.photos/400/251?random=2',
    tags: ['Laravel', 'PHP', 'MySQL', 'Docker']
  },
  {
    id: 3,
    title: 'E-commerce Platform',
    description: 'A scalable e-commerce solution with Vue.js frontend and Laravel backend. Includes payment integration, inventory management, and analytics dashboard.',
    type: 'Premium',
    price: '$99',
    stars: 1890,
    downloads: 3200,
    views: 8900,
    language: 'Full Stack',
    author: 'Alex Johnson',
    updated: '1 week ago',
    image: 'https://picsum.photos/400/252?random=3',
    tags: ['Vue.js', 'Laravel', 'Stripe', 'Redis']
  },
  {
    id: 4,
    title: 'Component Library',
    description: 'Reusable Vue components with Tailwind CSS styling. Perfect for rapid prototyping and maintaining design consistency across projects.',
    type: 'Open Source',
    stars: 3400,
    downloads: 8500,
    views: 18700,
    language: 'Vue.js',
    author: 'Sarah Wilson',
    updated: '3 days ago',
    image: 'https://picsum.photos/400/253?random=4',
    tags: ['Vue.js', 'Tailwind CSS', 'Storybook', 'TypeScript']
  },
  {
    id: 5,
    title: 'Authentication System',
    description: 'Complete authentication solution with JWT tokens, role-based permissions, and social login integration. Secure and production-ready.',
    type: 'Premium',
    price: '$29',
    stars: 1200,
    downloads: 2100,
    views: 5600,
    language: 'Laravel',
    author: 'Mike Brown',
    updated: '4 days ago',
    image: 'https://picsum.photos/400/254?random=5',
    tags: ['Laravel', 'JWT', 'OAuth', 'Security']
  },
  {
    id: 6,
    title: 'Admin Dashboard',
    description: 'Modern admin dashboard template with dark mode support, charts, and responsive design. Built with Vue 3 and includes multiple layout options.',
    type: 'Open Source',
    stars: 5600,
    downloads: 12000,
    views: 28900,
    language: 'Vue.js',
    author: 'Emily Davis',
    updated: '1 day ago',
    image: 'https://picsum.photos/400/255?random=6',
    tags: ['Vue.js', 'Charts', 'Dashboard', 'Responsive']
  }
])

const filteredProjects = computed(() => {
  let filtered = projects.value

  // Filter by search query
  if (searchQuery.value) {
    filtered = filtered.filter(project =>
      project.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      project.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      project.tags.some(tag => tag.toLowerCase().includes(searchQuery.value.toLowerCase()))
    )
  }

  // Filter by type
  if (selectedFilter.value !== 'all') {
    filtered = filtered.filter(project =>
      project.type.toLowerCase() === selectedFilter.value.toLowerCase()
    )
  }

  // Sort projects
  if (selectedSort.value === 'popular') {
    filtered = filtered.sort((a, b) => b.stars - a.stars)
  } else if (selectedSort.value === 'recent') {
    filtered = filtered.sort((a, b) => new Date(b.updated) - new Date(a.updated))
  } else if (selectedSort.value === 'downloads') {
    filtered = filtered.sort((a, b) => b.downloads - a.downloads)
  }

  return filtered
})

const stats = ref([
  { label: 'Total Projects', value: '15,000+', icon: Folder },
  { label: 'Downloads', value: '2.5M+', icon: Download },
  { label: 'Stars Given', value: '850K+', icon: Star },
  { label: 'Active Users', value: '50K+', icon: User },
])
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
            <Input v-model="searchQuery" type="text" placeholder="Search projects, technologies, or authors..."
              class="pl-12 pr-4 py-4 text-lg rounded-xl border-0 shadow-xl backdrop-blur-sm transition-all duration-300 focus:ring-2 focus:ring-blue-500"
              :class="themeStore.isDark ? 'bg-gray-800/90 text-white' : 'bg-white/90'" />
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
            selectedFilter === 'premium'
              ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400'
              : 'hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10'
          ]" @click="selectedFilter = 'premium'">
            <Zap class="h-4 w-4 mr-2" />
            Premium
          </Button>
          <Button variant="ghost" :class="[
            'transition-all duration-300',
            selectedFilter === 'open source'
              ? 'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400'
              : 'hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10'
          ]" @click="selectedFilter = 'open source'">
            <Heart class="h-4 w-4 mr-2" />
            Open Source
          </Button>
        </div>

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
            <DropdownMenuItem @click="selectedSort = 'popular'">Most Popular</DropdownMenuItem>
            <DropdownMenuItem @click="selectedSort = 'recent'">Most Recent</DropdownMenuItem>
            <DropdownMenuItem @click="selectedSort = 'downloads'">Most Downloaded</DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Projects Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Card v-for="project in filteredProjects" :key="project.id" :class="['group transition-all duration-300 hover:-translate-y-2 border-0 shadow-xl cursor-pointer overflow-hidden',
          themeStore.isDark ? 'bg-gray-800/90 hover:bg-gray-800' : 'bg-white/90 hover:bg-white'
        ]" @click="$router.push(`/projects/${project.id}`)">
          <!-- Project Image -->
          <div class="relative overflow-hidden">
            <img :src="project.image" :alt="project.title"
              class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
            <div class="absolute top-4 right-4">
              <Badge :class="[
                'text-white font-medium shadow-lg',
                project.type === 'Premium'
                  ? 'bg-gradient-to-r from-yellow-500 to-orange-500'
                  : 'bg-gradient-to-r from-green-500 to-emerald-500'
              ]">
                {{ project.type }}
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
                  <span>{{ project.author }}</span>
                  <span>•</span>
                  <Calendar class="h-4 w-4" />
                  <span>{{ project.updated }}</span>
                </div>
              </div>
              <div v-if="project.price" class="text-lg font-bold text-yellow-500">
                {{ project.price }}
              </div>
            </div>

            <p class="text-sm mb-4 line-clamp-3 leading-relaxed"
              :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              {{ project.description }}
            </p>

            <!-- Tags -->
            <div class="flex flex-wrap gap-2 mb-4">
              <Badge v-for="tag in project.tags.slice(0, 3)" :key="tag" variant="secondary" class="text-xs">
                {{ tag }}
              </Badge>
              <Badge v-if="project.tags.length > 3" variant="secondary" class="text-xs">
                +{{ project.tags.length - 3 }}
              </Badge>
            </div>

            <!-- Stats -->
            <div class="flex items-center justify-between pt-4 border-t"
              :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-200'">
              <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                <div class="flex items-center">
                  <Star class="h-4 w-4 mr-1 text-yellow-500 fill-current" />
                  <span>{{ project.stars.toLocaleString() }}</span>
                </div>
                <div class="flex items-center">
                  <Download class="h-4 w-4 mr-1" />
                  <span>{{ project.downloads.toLocaleString() }}</span>
                </div>
                <div class="flex items-center">
                  <Eye class="h-4 w-4 mr-1" />
                  <span>{{ project.views.toLocaleString() }}</span>
                </div>
              </div>

              <ArrowRight
                class="h-4 w-4 text-muted-foreground group-hover:text-blue-500 group-hover:translate-x-1 transition-all duration-300" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <div v-if="filteredProjects.length === 0" class="text-center py-20">
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
        <Button @click="searchQuery = ''; selectedFilter = 'all'"
          class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white">
          Clear Filters
        </Button>
      </div>

      <!-- Load More Button -->
      <div v-if="filteredProjects.length > 0" class="text-center mt-16">
        <Button variant="outline"
          class="px-8 py-3 text-lg font-medium border-2 hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10 transition-all duration-300">
          Load More Projects
          <ArrowRight class="h-5 w-5 ml-2" />
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
              @click="$router.push('/projects/upload')">
              <Code2 class="h-5 w-5 mr-2" />
              Upload Project
            </Button>
            <Button variant="outline" class="px-8 py-3 text-lg font-medium border-2" @click="$router.push('/about')">
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