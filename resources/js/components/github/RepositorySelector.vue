<script setup>
import { ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { Github, Search, Star, Code2, ArrowLeft, Loader2 } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const props = defineProps({
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['selected', 'back'])

const authStore = useAuthStore()
const themeStore = useThemeStore()

const repositories = ref([])
const filteredRepos = ref([])
const searchQuery = ref('')
const loadingRepos = ref(false)
const selectedRepo = ref(null)

const fetchRepositories = async () => {
  loadingRepos.value = true
  try {
    const response = await axios.get('/api/v1/github/repositories', {
      ...authStore.config,
      params: {
        exclude_forks: true,
        sort: 'updated',
        per_page: 100
      }
    })
    
    if (response.data.status === 'success') {
      repositories.value = response.data.data.repositories
      filteredRepos.value = repositories.value
    }
  } catch (error) {
    console.error('Error fetching repositories:', error)
    toast({
      title: "Error",
      description: error.response?.data?.message || "Failed to fetch repositories",
      variant: "destructive"
    })
  } finally {
    loadingRepos.value = false
  }
}

const filterRepositories = () => {
  if (!searchQuery.value.trim()) {
    filteredRepos.value = repositories.value
    return
  }
  
  const query = searchQuery.value.toLowerCase()
  filteredRepos.value = repositories.value.filter(repo => 
    repo.name.toLowerCase().includes(query) ||
    (repo.description && repo.description.toLowerCase().includes(query)) ||
    repo.full_name.toLowerCase().includes(query)
  )
}

const selectRepository = (repo) => {
  selectedRepo.value = repo
  emit('selected', repo)
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchRepositories()
})
</script>

<template>
  <div class="space-y-4">
    <!-- Search -->
    <div class="relative">
      <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4"
        :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
      />
      <Input
        v-model="searchQuery"
        @input="filterRepositories"
        placeholder="Search repositories..."
        class="pl-10"
      />
    </div>

    <!-- Loading State -->
    <div v-if="loadingRepos" class="flex items-center justify-center py-12">
      <Loader2 class="h-8 w-8 animate-spin text-laravel" />
    </div>

    <!-- Repositories List -->
    <div v-else-if="filteredRepos.length > 0" class="space-y-2 max-h-[500px] overflow-y-auto">
      <Card
        v-for="repo in filteredRepos"
        :key="repo.id"
        @click="selectRepository(repo)"
        class="cursor-pointer transition-colors border"
        :class="[
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800 hover:bg-gray-800' 
            : 'bg-white border-gray-200 hover:bg-gray-50',
          selectedRepo?.id === repo.id 
            ? themeStore.isDark 
              ? 'border-vue bg-gray-800' 
              : 'border-vue bg-vue/5'
            : ''
        ]"
      >
        <CardContent class="p-4">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-2">
                <Github class="h-4 w-4 flex-shrink-0"
                  :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
                />
                <h4 class="font-semibold truncate"
                  :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
                >
                  {{ repo.name }}
                </h4>
                <Badge v-if="repo.is_imported" variant="secondary" class="text-xs">
                  Imported
                </Badge>
              </div>
              
              <p v-if="repo.description" class="text-sm mb-2 line-clamp-2"
                :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'"
              >
                {{ repo.description }}
              </p>
              
              <div class="flex items-center gap-4 text-xs"
                :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-500'"
              >
                <div class="flex items-center gap-1">
                  <Code2 class="h-3 w-3" />
                  <span>{{ repo.language || 'N/A' }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <Star class="h-3 w-3" />
                  <span>{{ repo.stargazers_count || 0 }}</span>
                </div>
                <span>Updated {{ formatDate(repo.updated_at) }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <p class="text-sm"
        :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'"
      >
        {{ searchQuery ? 'No repositories found' : 'No repositories available' }}
      </p>
    </div>

    <!-- Back Button -->
    <div class="flex justify-end pt-4 border-t"
      :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-200'"
    >
      <Button variant="ghost" @click="$emit('back')">
        <ArrowLeft class="h-4 w-4 mr-2" />
        Back
      </Button>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

