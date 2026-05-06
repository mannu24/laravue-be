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

    <!-- Portfolio CTA -->
    <section class="py-16">
      <div class="container mx-auto px-4">
        <div class="relative overflow-hidden rounded-3xl"
          :class="themeStore.isDark ? 'bg-gradient-to-br from-gray-800 via-gray-900 to-gray-800' : 'bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900'">
          <!-- Decorative elements -->
          <div class="absolute top-0 left-0 w-72 h-72 bg-vue/20 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2"></div>
          <div class="absolute bottom-0 right-0 w-72 h-72 bg-laravel/20 rounded-full blur-[100px] translate-x-1/2 translate-y-1/2"></div>
          <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px]"></div>

          <div class="relative px-8 py-16 md:px-16 md:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
              <!-- Left: Content -->
              <div>
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-vue/10 border border-vue/20 text-vue text-sm font-semibold mb-6">
                  <Sparkles class="w-4 h-4" />
                  New: Portfolio Builder
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-white mb-4 leading-tight">
                  Your work deserves<br>
                  <span class="bg-gradient-to-r from-vue to-emerald-400 bg-clip-text text-transparent">its own website</span>
                </h2>
                <p class="text-lg text-gray-400 mb-6 leading-relaxed">
                  Build a stunning portfolio at <strong class="text-white">yourname.laravue.in</strong> — choose a template, add your projects, and go live in minutes. No coding needed.
                </p>

                <!-- Features mini-list -->
                <div class="grid grid-cols-2 gap-3 mb-8">
                  <div v-for="f in ['Beautiful templates', 'Custom domain', 'SEO optimized', 'Mobile responsive']" :key="f"
                    class="flex items-center gap-2 text-sm text-gray-300">
                    <div class="w-5 h-5 rounded-full bg-vue/20 flex items-center justify-center flex-shrink-0">
                      <svg class="w-3 h-3 text-vue" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    {{ f }}
                  </div>
                </div>

                <!-- CTA buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                  <Button @click="router.push(authStore.isAuthenticated ? '/portfolio' : '/login')"
                    class="bg-vue hover:bg-vue/90 text-white px-8 py-3 text-base font-semibold rounded-xl shadow-lg shadow-vue/25 hover:shadow-vue/40 transition-all">
                    <Rocket class="w-5 h-5 mr-2" />
                    Start Building Free
                  </Button>
                  <Button @click="router.push('/portfolio/plans')" variant="outline"
                    class="border-gray-600 text-gray-300 hover:bg-white/5 hover:border-gray-500 px-8 py-3 text-base rounded-xl">
                    View Plans
                    <ArrowRight class="w-4 h-4 ml-2" />
                  </Button>
                </div>

                <!-- Discount badge -->
                <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-yellow-500/10 to-orange-500/10 border border-yellow-500/20">
                  <span class="text-yellow-400 text-lg">🎉</span>
                  <span class="text-sm text-yellow-300/90">
                    Launch offer: Use <span class="font-mono font-bold text-yellow-300">WELCOME40</span> for <strong>40% off</strong>
                  </span>
                </div>
              </div>

              <!-- Right: Visual mockup -->
              <div class="hidden lg:block">
                <div class="relative">
                  <!-- Browser mockup -->
                  <div class="rounded-xl border border-gray-700 bg-gray-900 shadow-2xl overflow-hidden">
                    <!-- Browser bar -->
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-700 bg-gray-800/50">
                      <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                      </div>
                      <div class="flex-1 mx-4">
                        <div class="bg-gray-700 rounded-md px-3 py-1 text-xs text-gray-400 text-center">
                          yourname.laravue.in
                        </div>
                      </div>
                    </div>
                    <!-- Content mockup -->
                    <div class="p-6 space-y-4">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-vue to-emerald-500"></div>
                        <div>
                          <div class="h-3 w-28 bg-gray-700 rounded"></div>
                          <div class="h-2 w-20 bg-gray-800 rounded mt-1.5"></div>
                        </div>
                      </div>
                      <div class="h-2 w-full bg-gray-800 rounded"></div>
                      <div class="h-2 w-3/4 bg-gray-800 rounded"></div>
                      <div class="flex gap-2 mt-2">
                        <div class="h-6 w-16 bg-vue/20 rounded-md"></div>
                        <div class="h-6 w-20 bg-vue/20 rounded-md"></div>
                        <div class="h-6 w-14 bg-vue/20 rounded-md"></div>
                      </div>
                      <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="h-24 bg-gray-800 rounded-lg"></div>
                        <div class="h-24 bg-gray-800 rounded-lg"></div>
                      </div>
                    </div>
                  </div>
                  <!-- Floating badge -->
                  <div class="absolute -bottom-3 -right-3 bg-vue text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-lg shadow-vue/30 rotate-3">
                    ✨ Live in 5 min
                  </div>
                </div>
              </div>
            </div>
          </div>
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
