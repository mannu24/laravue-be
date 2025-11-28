<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
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
  Download,
  Heart,
  Code2,
  Zap,
  ChevronDown,
  User,
  Folder,
  Plus,
  Github
} from 'lucide-vue-next'
import axios from 'axios'
import GitHubImportModal from '@/components/github/GitHubImportModal.vue'
import { useNavbarSearch } from '@/composables/useNavbarSearch'
import InfiniteScroll from '@/components/elements/InfiniteScroll.vue'

const router = useRouter()
const authStore = useAuthStore()
const { openSearch } = useNavbarSearch()

const searchQuery = ref('')
const selectedFilter = ref('all')
const selectedSort = ref('recent')
const technologies = ref([])
const selectedTechnology = ref('')
const showGitHubModal = ref(false)

const projectListKey = ref(0)
const handleProjectImported = () => {
  projectListKey.value++
}

const stats = ref([
  { label: 'Projects launched', value: '0', icon: Folder },
  { label: 'Total installs', value: '0', icon: Download },
  { label: 'Community stars', value: '0', icon: Star },
  { label: 'Active makers', value: '0', icon: User }
])

const showcaseHighlights = [
  {
    title: 'Production-ready kits',
    description: 'Premium headless starters, dashboards, and SaaS templates built with Laravel + Vue.',
    icon: Zap,
    badge: 'Premium'
  },
  {
    title: 'Open-source labs',
    description: 'Cloneable examples with detailed READMEs, tests, and design tokens to remix freely.',
    icon: Heart,
    badge: 'OSS'
  },
  {
    title: 'Client delivery vault',
    description: 'Private repos for agencies to share deliverables with stakeholders securely.',
    icon: Folder,
    badge: 'Studios'
  }
]

const projectFilters = computed(() => {
  const filters = {
    sort: selectedSort.value
  }
  if (selectedFilter.value !== 'all') {
    filters.type = selectedFilter.value
  }
  if (selectedTechnology.value) {
    filters.technology = selectedTechnology.value
  }
  return filters
})

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
  <div class="min-h-screen space-y-10 py-5">
    <!-- Hero -->
    <section class="relative overflow-hidden rounded-lg bg-gradient-to-br from-gray-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-900/85 dark:to-indigo-900/40 border border-gray-200 dark:border-white/5 hover:shadow-[0_0_80px_rgba(30,41,59,0.15)] dark:hover:shadow-[0_0_80px_rgba(30,41,59,0.55)] transition-all duration-300">
      <div class="absolute inset-0 opacity-30 pointer-events-none">
        <div class="absolute -top-24 -right-12 w-80 h-80 bg-blue-500/20 dark:bg-blue-500/40 blur-[120px]"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-purple-500/20 dark:bg-purple-500/35 blur-[120px]"></div>
      </div>
      <div class="relative px-6 sm:px-10 lg:px-16 py-14 text-center max-w-5xl mx-auto space-y-8">
        <div>
          <h3 class="text-3xl lg:text-6xl font-black leading-tight mb-5">
            <span class="text-gray-900 dark:text-white">Discover, remix, and ship with </span>
            <span class="bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500 bg-clip-text text-transparent font-bold">
              LaraVue
            </span>
          </h3>
          <p class="lg:text-lg text-gray-600 dark:text-gray-300 py-5 leading-relaxed">
            Tap into battle-tested starters, premium SaaS kits, and open-source experiments curated by the community. Filters, reviews, and deployment notes make launching your next idea effortless.
          </p>
        </div>
        <div class="relative max-w-2xl mx-auto">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <Search class="h-5 w-5 text-gray-500 dark:text-gray-400" />
          </div>
          <Input
            v-model="searchQuery"
            type="text"
            placeholder="Search projects, stacks..."
            class="pl-12 pr-4 py-5 h-14 text-lg rounded-2xl border-0 shadow-[0_20px_70px_rgba(59,130,246,0.15)] dark:shadow-[0_20px_70px_rgba(59,130,246,0.35)] focus:ring-2 focus:ring-blue-500/70 cursor-pointer bg-white dark:bg-white/15 text-gray-900 dark:text-white placeholder:text-gray-500 dark:placeholder:text-gray-300"
            @click="openSearch(searchQuery)"
            readonly
          />
        </div>
        <div class="flex flex-wrap justify-center gap-3 text-sm text-gray-600 dark:text-gray-300">
          <span class="px-3 py-1 rounded-full border border-gray-300 dark:border-white/10 bg-gray-100 dark:bg-white/5">Laravel SaaS</span>
          <span class="px-3 py-1 rounded-full border border-gray-300 dark:border-white/10 bg-gray-100 dark:bg-white/5">Tailwind UI Kits</span>
          <span class="px-3 py-1 rounded-full border border-gray-300 dark:border-white/10 bg-gray-100 dark:bg-white/5">Nuxt Jamstack</span>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-5">
      <Card
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white dark:bg-card/80 backdrop-blur border border-gray-200 dark:border-white/5 shadow-xl hover:shadow-[0_15px_45px_rgba(79,70,229,0.25)] dark:hover:shadow-[0_15px_45px_rgba(79,70,229,0.45)] hover:-translate-y-1 transition"
      >
        <CardContent class="p-5 flex flex-col items-center text-center gap-2">
          <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500/35 via-purple-500/25 to-indigo-500/25 flex items-center justify-center">
            <component :is="stat.icon" class="w-5 h-5 text-white" />
          </div>
          <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-sm uppercase tracking-wide text-gray-600 dark:text-gray-400">{{ stat.label }}</p>
        </CardContent>
      </Card>
    </section>

    <!-- Highlights -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <Card
        v-for="highlight in showcaseHighlights"
        :key="highlight.title"
        class="bg-white dark:bg-card/70 backdrop-blur border border-gray-200 dark:border-white/5 shadow-xl hover:border-blue-500/40 transition"
      >
        <CardContent class="p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500/20 via-purple-500/20 to-indigo-500/20 text-white flex items-center justify-center">
              <component :is="highlight.icon" class="w-5 h-5" />
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-gray-100 dark:bg-white/10 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 tracking-wide">{{ highlight.badge }}</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ highlight.title }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ highlight.description }}</p>
        </CardContent>
      </Card>
    </section>

    <!-- Filters and Projects -->
    <div class="py-5 space-y-10">
      <Card class="bg-white dark:bg-card/70 border border-gray-200 dark:border-white/5 shadow-md">
        <CardContent class="p-5 flex flex-col gap-5">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-7 lg:gap-5">
            <div class="flex gap-3">
              <Button
                variant="ghost"
                :class="[selectedFilter === 'all' ? 'bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-indigo-500/20 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5']"
                  @click="selectedFilter = 'all'"
              >
                All
              </Button>
              <Button
                variant="ghost"
                :class="[selectedFilter === 'sellable' ? 'bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-indigo-500/20 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5']"
                  @click="selectedFilter = 'sellable'"
                >
                <Zap class="h-4 w-4 mr-2" /> Premium
              </Button>
              <Button
                variant="ghost"
                :class="[selectedFilter === 'open' ? 'bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-indigo-500/20 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5']"
                  @click="selectedFilter = 'open'"
                >
                <Heart class="h-4 w-4 mr-2" /> Open source
              </Button>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
              <DropdownMenu>
                <DropdownMenuTrigger as="div" class="w-full md:w-auto">
                  <Button class="w-full justify-between md:w-56 bg-gray-100 dark:bg-white/5 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-white/10">
                    <Code2 class="h-4 w-4 mr-2" />
                    {{ selectedTechnology || 'Technology' }}
                    <ChevronDown class="h-4 w-4 ml-auto" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-56 max-h-64 overflow-y-auto">
                  <DropdownMenuItem @click="selectedTechnology = ''">All technologies</DropdownMenuItem>
                  <DropdownMenuItem
                    v-for="tech in technologies"
                    :key="tech.id"
                    @click="selectedTechnology = tech.name"
                  >
                    {{ tech.name }}
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
              <DropdownMenu>
                <DropdownMenuTrigger as="div" class="w-full md:w-auto">
                  <Button class="w-full justify-between md:w-56 bg-gray-100 dark:bg-white/5 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-white/10">
                    <Filter class="h-4 w-4 mr-2" />
                    Sort by
                    <ChevronDown class="h-4 w-4 ml-auto" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-56">
                  <DropdownMenuItem @click="selectedSort = 'recent'">Most recent</DropdownMenuItem>
                  <DropdownMenuItem @click="selectedSort = 'popular'">Most popular</DropdownMenuItem>
                  <DropdownMenuItem @click="selectedSort = 'views'">Most viewed</DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
              <Button
                v-if="authStore.isAuthenticated"
                class="w-full md:w-auto bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500 text-white hover:shadow-[0_0_25px_rgba(79,70,229,0.45)] transition-all duration-300"
                @click="router.push('/projects/create')"
              >
                <Plus class="h-4 w-4 mr-2" />
                Add project
              </Button>
            </div>
            </div>
        </CardContent>
      </Card>
      <InfiniteScroll
        scrolling="project"
        :filters="projectFilters"
        :per-page="12"
        :fetch-key="projectListKey"
      />
    </div>

    <!-- CTA Section -->
    <section class="relative overflow-hidden rounded-lg bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-blue-600/25 dark:via-slate-900/90 dark:to-indigo-900 border border-blue-200 dark:border-blue-500/25 hover:shadow-[0_0_80px_rgba(30,41,59,0.15)] dark:hover:shadow-[0_0_80px_rgba(30,41,59,0.55)] transition-all duration-300 overflow-hidden">
      <CardContent class="p-10 sm:p-12 text-center space-y-6">
        <p class="text-sm uppercase tracking-[0.35em] text-blue-600 dark:text-blue-300">Launch on LaraVue</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Show the community what you're building</h2>
        <p class="text-lg text-gray-700 dark:text-gray-300 max-w-3xl mx-auto">
          Import directly from GitHub, add deployment notes, and earn marketplace badges for every version you ship.
          Prefer a guided experience? Our team can help polish your listing before launch.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Button
            class="bg-white dark:bg-white text-slate-900 px-8 py-3 text-base font-semibold shadow-xl hover:-translate-y-0.5"
            @click="showGitHubModal = true"
          >
            <Github class="h-5 w-5 mr-2" />
            Import from GitHub
          </Button>
          <Button
            variant="outline"
            class="border-gray-300 dark:border-white/40 text-gray-700 dark:text-white px-8 py-3 text-base font-semibold hover:bg-gray-100 dark:hover:bg-white/10"
            @click="router.push('/projects/create')"
          >
            <Code2 class="h-5 w-5 mr-2" />
            Publish manually
          </Button>
        </div>
      </CardContent>
    </section>

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
