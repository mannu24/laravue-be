
<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  Star,
  Eye,
  Calendar,
  User,
  Code2,
  ArrowRight,
  Flame,
  Sparkles,
  Heart,
  Share2,
  Download,
  ExternalLink
} from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const props = defineProps({
  project: {
    type: Object,
    required: true
  }
})

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

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

const upvoteProject = async (event) => {
  event.stopPropagation()
  
  if (!authStore.isAuthenticated) {
    toast({
      title: "Authentication Required",
      description: "Please login to upvote projects",
      variant: "destructive"
    })
    return
  }

  try {
    const response = await axios.post(`/api/v1/projects/${props.project.slug}/upvote`, {}, authStore.config)
    props.project.is_upvoted_by_user = response.data.data.is_upvoted
    props.project.upvotes_count = response.data.data.upvotes_count
    
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

const shareProject = async (event) => {
  event.stopPropagation()
  
  const url = `${window.location.origin}/projects/${props.project.slug}`
  
  if (navigator.share) {
    try {
      await navigator.share({
        title: props.project.title,
        text: props.project.description,
        url: url
      })
    } catch (error) {
      // User cancelled or error occurred
      if (error.name !== 'AbortError') {
        copyToClipboard(url)
      }
    }
  } else {
    copyToClipboard(url)
  }
}

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
  toast({
    title: "Link Copied",
    description: "Project link copied to clipboard",
  })
}

const navigateToProject = () => {
  router.push(`/projects/${props.project.slug}`)
}

const handleQuickAction = (action, event) => {
  event.stopPropagation()
  
  if (action === 'view') {
    navigateToProject()
  } else if (action === 'upvote') {
    upvoteProject(event)
  } else if (action === 'share') {
    shareProject(event)
  }
}

// Computed properties for UI
const isTrending = computed(() => {
  return props.project.trending_score > 50 || props.project.is_featured
})

const isNew = computed(() => {
  const daysSinceCreation = Math.floor((new Date() - new Date(props.project.created_at)) / (1000 * 60 * 60 * 24))
  return daysSinceCreation <= 7
})

const categoryName = computed(() => {
  return props.project.category?.name || props.project.industry || 'Project'
})
</script>

<template>
  <div 
    :class="['group relative overflow-hidden rounded-3xl transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl cursor-pointer',
      themeStore.isDark ? 'bg-gray-800 border border-gray-700' : 'bg-white border border-gray-200'
    ]" 
    @click="navigateToProject"
  >
    <!-- Project Image -->
    <div class="relative overflow-hidden">
      <img 
        :src="project.featured_image || 'https://picsum.photos/400/250?random=' + project.id" 
        :alt="project.title"
        class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110" 
      />

      <!-- Overlay Badges -->
      <div class="absolute top-4 left-4 flex gap-2">
        <Badge v-if="isTrending" class="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground border-0">
          <Flame class="w-3 h-3 mr-1" />
          Trending
        </Badge>
        <Badge v-if="isNew" class="bg-gradient-to-r from-secondary to-secondary/80 text-secondary-foreground border-0">
          <Sparkles class="w-3 h-3 mr-1" />
          New
        </Badge>
      </div>

      <div class="absolute top-4 right-4">
        <Badge :class="project.is_sellable 
          ? 'bg-gradient-to-r from-primary to-primary/80 text-primary-foreground border-0' 
          : 'bg-gradient-to-r from-secondary to-secondary/80 text-secondary-foreground border-0'">
          {{ project.is_sellable ? 'Premium' : 'Open Source' }}
        </Badge>
      </div>

      <!-- Quick Actions Overlay -->
      <div
        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
        <div class="flex gap-3">
          <Button 
            size="sm" 
            class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30"
            @click="handleQuickAction('view', $event)"
          >
            <Eye class="w-4 h-4" />
          </Button>
          <Button 
            size="sm" 
            class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30"
            @click="handleQuickAction('upvote', $event)"
          >
            <Heart class="w-4 h-4" :class="project.is_upvoted_by_user ? 'fill-current' : ''" />
          </Button>
          <Button 
            size="sm" 
            class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30"
            @click="handleQuickAction('share', $event)"
          >
            <Share2 class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </div>

    <!-- Project Content -->
    <div class="p-6">
      <!-- Author Info -->
      <div class="flex items-center mb-4">
        <img 
          :src="project.user?.profile_photo || `https://ui-avatars.com/api/?name=${project.user?.name}&background=random`" 
          :alt="project.user?.name" 
          class="w-8 h-8 rounded-full mr-3" 
        />
        <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
          {{ project.user?.name }}
        </span>
        <Badge variant="secondary" class="ml-auto text-xs">
          {{ categoryName }}
        </Badge>
      </div>

      <!-- Project Title & Description -->
      <h3 class="text-xl font-bold mb-3" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
        {{ project.title }}
      </h3>
      <p class="text-sm leading-relaxed mb-4 line-clamp-3" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
        {{ project.description || project.short_description }}
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
        <Badge 
          v-if="project.technologies?.length > 3" 
          variant="secondary" 
          class="text-xs"
        >
          +{{ project.technologies.length - 3 }}
        </Badge>
      </div>

      <!-- Stats -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4 text-sm">
          <button 
            @click="upvoteProject"
            class="flex items-center transition-colors"
            :class="project.is_upvoted_by_user 
              ? 'text-yellow-500' 
              : (themeStore.isDark ? 'text-gray-300' : 'text-gray-600')"
          >
            <Star class="w-4 h-4 mr-1" :class="project.is_upvoted_by_user ? 'fill-current text-yellow-500' : ''" />
            <span>{{ project.upvotes_count || 0 }}</span>
          </button>
          <div class="flex items-center" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            <Download class="w-4 h-4 text-green-500 mr-1" />
            <span>{{ project.views?.toLocaleString() || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Action Button -->
      <Button 
        class="w-full" 
        :class="project.is_sellable
          ? 'bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground'
          : 'bg-gradient-to-r from-secondary to-secondary/80 hover:from-secondary/90 hover:to-secondary/70 text-secondary-foreground'
        "
        @click.stop="navigateToProject"
      >
        {{ project.is_sellable && project.selling_price ? `Buy for $${project.selling_price}` : 'View Project' }}
        <ExternalLink class="w-4 h-4 ml-2" />
      </Button>
    </div>
  </div>
</template>

<style scoped>
/* Line clamp utility */
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>