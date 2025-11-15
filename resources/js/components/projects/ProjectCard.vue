<script setup>
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Star, Eye, User, Calendar, ArrowRight } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { useThemeStore } from '@/stores/theme'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const props = defineProps({
  project: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['upvoted', 'viewed'])

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
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
    const response = await axios.post(`/api/v1/projects/${project.slug}/upvote`, {}, authStore.config)
    project.is_upvoted_by_user = response.data.data.is_upvoted
    project.upvotes_count = response.data.data.upvotes_count

    emit('upvoted', project)

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

const handleCardClick = () => {
  router.push(`/projects/${props.project.slug}`)
  emit('viewed', props.project)
}
</script>

<template>
  <Card 
    :class="['group transition-all duration-300 hover:-translate-y-2 border-0 shadow-xl cursor-pointer overflow-hidden',
      themeStore.isDark ? 'bg-gray-800/90 hover:bg-gray-800' : 'bg-white/90 hover:bg-white'
    ]" 
    @click="handleCardClick"
  >
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
          <h3 class="text-xl font-semibold mb-2 group-hover:text-blue-500 transition-colors duration-300"
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
        <div v-if="project.selling_price" class="text-lg font-semibold text-yellow-500">
          ${{ project.selling_price }}
        </div>
      </div>

      <p class="text-sm mb-4 line-clamp-3 leading-relaxed"
        :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
        {{ project.description }}
      </p>

      <!-- Tags -->
      <div class="flex flex-wrap gap-2 mb-4">
        <Badge 
          v-for="tech in project.technologies?.slice(0, 3)" 
          :key="tech.id" 
          variant="secondary" 
          class="text-xs"
        >
          {{ tech.name }}
        </Badge>
        <Badge v-if="project.technologies?.length > 3" variant="secondary" class="text-xs">
          +{{ project.technologies.length - 3 }}
        </Badge>
      </div>

      <!-- Stats -->
      <div v-if="showActions" class="flex items-center justify-between pt-4 border-t"
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
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
