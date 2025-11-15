<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import ProjectCard from './ProjectCard.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { Loader2, Code2 } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const props = defineProps({
  userId: {
    type: Number,
    default: null
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  perPage: {
    type: Number,
    default: 12
  }
})

const emit = defineEmits(['loaded'])

const authStore = useAuthStore()
const themeStore = useThemeStore()

const projects = ref([])
const loading = ref(false)
const currentPage = ref(1)
const hasMore = ref(true)
const observerTarget = ref(null)
const observer = ref(null)

const fetchProjects = async (reset = false) => {
  if (loading.value || (!hasMore.value && !reset)) return

  loading.value = true

  try {
    const params = {
      page: reset ? 1 : currentPage.value,
      per_page: props.perPage,
      ...props.filters
    }

    // If userId is provided, add user filter
    if (props.userId) {
      params.user_id = props.userId
    }

    const response = await axios.get('/api/v1/projects', {
      params,
      ...(authStore.isAuthenticated ? authStore.config : {})
    })

    // Laravel ResourceCollection returns data in response.data.data
    // Pagination info is in response.data (current_page, last_page, etc.)
    const newProjects = response.data.data || []
    const pagination = response.data

    if (reset) {
      projects.value = newProjects
      currentPage.value = 1
    } else {
      projects.value = [...projects.value, ...newProjects]
    }

    // Check if there are more pages
    hasMore.value = pagination.current_page < pagination.last_page
    if (!reset && hasMore.value) {
      currentPage.value = pagination.current_page + 1
    } else if (reset && hasMore.value) {
      currentPage.value = 2
    }

    emit('loaded', projects.value)
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

const setupIntersectionObserver = () => {
  if (!observerTarget.value) return

  const options = {
    root: null,
    rootMargin: '200px',
    threshold: 0.1
  }

  observer.value = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && hasMore.value && !loading.value) {
        fetchProjects()
      }
    })
  }, options)

  observer.value.observe(observerTarget.value)
}

const handleUpvoted = (project) => {
  const index = projects.value.findIndex(p => p.id === project.id)
  if (index !== -1) {
    projects.value[index] = { ...project }
  }
}

onMounted(async () => {
  await fetchProjects(true)
  await nextTick()
  // Setup observer after initial load and DOM update
  setTimeout(() => {
    setupIntersectionObserver()
  }, 300)
})

onUnmounted(() => {
  if (observer.value && observerTarget.value) {
    observer.value.unobserve(observerTarget.value)
  }
})

// Watch for filter changes
watch(() => props.filters, () => {
  if (observer.value && observerTarget.value) {
    observer.value.unobserve(observerTarget.value)
  }
  fetchProjects(true)
  setTimeout(() => {
    setupIntersectionObserver()
  }, 300)
}, { deep: true })

// Watch for userId changes
watch(() => props.userId, () => {
  if (observer.value && observerTarget.value) {
    observer.value.unobserve(observerTarget.value)
  }
  fetchProjects(true)
  setTimeout(() => {
    setupIntersectionObserver()
  }, 300)
})
</script>

<template>
  <div class="w-full">
    <!-- Projects Grid -->
    <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <ProjectCard
        v-for="project in projects"
        :key="project.id"
        :project="project"
        @upvoted="handleUpvoted"
      />
    </div>

    <!-- Loading State -->
    <div v-if="loading && projects.length === 0" class="text-center py-20">
      <Loader2 class="h-12 w-12 animate-spin mx-auto mb-4 text-blue-500" />
      <p class="text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
        Loading projects...
      </p>
    </div>

    <!-- Load More Indicator & Observer Target -->
    <div v-if="projects.length > 0" class="text-center py-8">
      <div ref="observerTarget" class="h-1 w-full"></div>
      <Loader2 v-if="loading" class="h-8 w-8 animate-spin mx-auto text-blue-500 mt-4" />
      <p v-else-if="!hasMore" class="text-sm mt-4" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
        No more projects to load
      </p>
    </div>

    <!-- Empty State -->
    <EmptyState
      v-else-if="!loading && projects.length === 0"
      icon="Code2"
      title="No Projects Found"
      :subtitle="userId ? 'This user hasn\'t shared any projects yet.' : 'No projects match your criteria.'"
    />
  </div>
</template>

