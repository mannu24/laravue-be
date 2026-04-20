<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  ArrowRight, Star, Users, FolderGit2, Code2,
  Eye, Rocket, Sparkles, Plus, ExternalLink
} from 'lucide-vue-next'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import axios from 'axios'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

// Real data from API
const stats = ref([
  { label: 'Contributors', value: '—', icon: Users, color: 'from-vue to-vue/80' },
  { label: 'Projects', value: '—', icon: FolderGit2, color: 'from-vue/80 to-vue/60' },
  { label: 'Upvotes', value: '—', icon: Star, color: 'from-laravel to-laravel/80' },
  { label: 'Technologies', value: '—', icon: Code2, color: 'from-laravel/80 to-laravel/60' },
])

const trendingProjects = ref([])
const loadingProjects = ref(true)

const fetchHomeData = async () => {
  try {
    const [projectsRes, techRes] = await Promise.allSettled([
      axios.get('/api/v1/projects/stats'),
      axios.get('/api/v1/projects/technologies'),
    ])

    const pStats = projectsRes.status === 'fulfilled' ? projectsRes.value.data?.data : {}
    const tData = techRes.status === 'fulfilled' ? techRes.value.data?.data : []

    stats.value = [
      { label: 'Contributors', value: String(pStats.total_contributors || 0), icon: Users, color: 'from-vue to-vue/80' },
      { label: 'Projects', value: String(pStats.total_projects || 0), icon: FolderGit2, color: 'from-vue/80 to-vue/60' },
      { label: 'Upvotes', value: String(pStats.total_upvotes || 0), icon: Star, color: 'from-laravel to-laravel/80' },
      { label: 'Technologies', value: String(tData.length || 0), icon: Code2, color: 'from-laravel/80 to-laravel/60' },
    ]
  } catch (e) {
    // Non-critical, keep defaults
  }

  // Fetch trending projects
  try {
    const res = await axios.get('/api/v1/projects/trending')
    trendingProjects.value = (res.data?.data?.data || res.data?.data || []).slice(0, 6)
  } catch (e) {
    // Non-critical
  } finally {
    loadingProjects.value = false
  }
}

onMounted(fetchHomeData)
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero -->
    <section class="relative overflow-hidden">
      <div class="container mx-auto px-4 py-20 sm:py-28 lg:py-32">
        <div class="text-center max-w-5xl mx-auto">
          <div class="mb-8 inline-flex items-center">
            <Badge class="bg-gradient-to-r from-vue to-vue/80 text-white border-0 px-6 py-2 text-sm font-medium">
              <Sparkles class="w-4 h-4 mr-2" />
              Developer Community Platform
            </Badge>
          </div>

          <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight mb-8">
            <span :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Build Amazing</span>
            <br>
            <span class="bg-gradient-to-r from-vue via-vue/80 to-laravel bg-clip-text text-transparent">
              Laravel Projects
            </span>
          </h1>

          <p class="text-xl sm:text-2xl font-medium mb-12 max-w-4xl mx-auto leading-relaxed"
             :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            A community for Laravel and Vue.js developers to share projects,
            solve problems, and grow together.
          </p>

          <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
            <Button @click="router.push('/projects')"
                    class="bg-gradient-to-r from-vue to-vue/80 hover:from-vue/90 hover:to-vue/70 text-white px-10 py-4 text-lg font-semibold rounded-full shadow-2xl transform hover:scale-105 transition-all duration-300">
              <Rocket class="w-5 h-5 mr-2" />
              Explore Projects
            </Button>
            <Button @click="router.push(authStore.isAuthenticated ? '/projects/create' : '/login')" variant="outline"
                    :class="['border-2 px-10 py-4 text-lg font-semibold rounded-full transition-all duration-300 hover:scale-105',
                      themeStore.isDark
                        ? 'border-gray-600 text-gray-300 hover:bg-gray-800 hover:border-vue hover:text-vue'
                        : 'border-gray-300 text-gray-700 hover:bg-vue/10 hover:border-vue hover:text-vue'
                    ]">
              <Plus class="w-5 h-5 mr-2" />
              Share Your Project
            </Button>
          </div>

          <!-- Real Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-3xl mx-auto">
            <div v-for="stat in stats" :key="stat.label" class="text-center">
              <div :class="['w-14 h-14 mx-auto mb-3 rounded-2xl bg-gradient-to-r flex items-center justify-center', stat.color]">
                <component :is="stat.icon" class="w-7 h-7 text-white" />
              </div>
              <div class="text-2xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ stat.value }}</div>
              <div class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Trending Projects (real data) -->
    <section class="py-16" v-if="trendingProjects.length > 0 || loadingProjects">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-3xl font-bold mb-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Trending Projects</h2>
            <p class="text-lg" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">Popular projects from the community</p>
          </div>
          <Button variant="outline" @click="router.push('/projects')"
                  :class="themeStore.isDark ? 'border-gray-600 text-gray-300 hover:bg-gray-800' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
            View All <ArrowRight class="w-4 h-4 ml-2" />
          </Button>
        </div>

        <div v-if="loadingProjects" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="h-[400px] rounded-2xl animate-pulse" :class="themeStore.isDark ? 'bg-gray-800' : 'bg-gray-100'"></div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <ProjectCard v-for="project in trendingProjects" :key="project.id" :project="project" />
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="relative overflow-hidden rounded-3xl p-12 lg:p-20 text-center"
             :class="themeStore.isDark ? 'bg-gray-800/50' : 'bg-gray-50'">
          <h2 class="text-4xl sm:text-5xl font-black mb-6" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Ready to Build Something Amazing?
          </h2>
          <p class="text-xl mb-12" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Join the community. Share your work. Grow as a developer.
          </p>
          <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <Button @click="router.push(authStore.isAuthenticated ? '/dashboard' : '/login')"
                    class="bg-gradient-to-r from-vue to-vue/80 hover:from-vue/90 hover:to-vue/70 text-white px-8 py-3 text-base font-semibold rounded-full">
              <Rocket class="w-5 h-5 mr-2" />
              {{ authStore.isAuthenticated ? 'Go to Dashboard' : 'Get Started Free' }}
            </Button>
            <Button variant="outline" @click="router.push('/projects')"
                    :class="themeStore.isDark ? 'border-gray-600 text-gray-300 hover:bg-gray-800' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
              <Eye class="w-5 h-5 mr-2" />
              Browse Projects
            </Button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
