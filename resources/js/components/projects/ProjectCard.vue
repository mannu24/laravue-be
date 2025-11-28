<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  User,
  Calendar,
  ArrowRight,
  Flame,
  Sparkles,
  CircleChevronUp,
  Share2,
  Download,
  ExternalLink
} from 'lucide-vue-next'
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

const upvoteProject = async (event) => {
  event?.stopPropagation?.()
  if (!authStore.isAuthenticated) {
    toast({
      title: 'Authentication Required',
      description: 'Please login to upvote projects',
      variant: 'destructive'
    })
    return
  }
  try {
    const response = await axios.post(`/api/v1/projects/${props.project.slug}/upvote`, {}, authStore.config)
    props.project.is_upvoted_by_user = response.data.data.is_upvoted
    props.project.upvotes_count = response.data.data.upvotes_count
    emit('upvoted', props.project)
    toast({
      title: 'Success',
      description: response.data.message
    })
  } catch (error) {
    console.error('Error upvoting project:', error)
    toast({
      title: 'Error',
      description: 'Failed to upvote project',
      variant: 'destructive'
    })
  }
}

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
  toast({
    title: 'Link Copied',
    description: 'Project link copied to clipboard'
  })
}

const shareProject = async (event) => {
  event?.stopPropagation?.()
  const url = `${window.location.origin}/projects/${props.project.slug}`
  if (navigator.share) {
    try {
      await navigator.share({
        title: props.project.title,
        text: props.project.description,
        url
      })
    } catch (error) {
      if (error.name !== 'AbortError') {
        copyToClipboard(url)
      }
    }
  } else {
    copyToClipboard(url)
  }
}

const navigateToProject = () => {
  router.push(`/projects/${props.project.slug}`)
  emit('viewed', props.project)
}

const isTrending = computed(() => props.project.trending_score > 50 || props.project.is_featured)
const isNew = computed(() => {
  const created = props.project.created_at ? new Date(props.project.created_at) : null
  if (!created) return false
  const diffDays = (new Date() - created) / (1000 * 60 * 60 * 24)
  return diffDays <= 7
})
const categoryName = computed(() => props.project.category?.name || props.project.industry || 'Project')
</script>

<template>
  <Card
    :class="[
      'group relative overflow-hidden rounded-3xl transition-all duration-500 hover:scale-[1.01] hover:shadow-2xl cursor-pointer',
      themeStore.isDark ? 'bg-gray-900/80 border border-white/5' : 'bg-white border border-gray-200'
    ]"
    @click="navigateToProject"
  >
    <div class="relative overflow-hidden">
      <img
        :src="project.featured_image || 'https://picsum.photos/400/250?random=' + project.id"
        :alt="project.title"
        class="w-full h-56 object-cover transition-transform duration-500 group-hover:scale-110"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>

      <div class="absolute top-4 left-4 flex gap-2">
        <Badge
          v-if="isTrending"
          class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white border-0 shadow-lg text-xs"
        >
          <Flame class="w-3 h-3 mr-1" />
          Trending
        </Badge>
        <Badge
          v-if="isNew"
          class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white border-0 shadow-lg text-xs"
        >
          <Sparkles class="w-3 h-3 mr-1" />
          New
        </Badge>
      </div>

      <div class="absolute top-4 right-4">
        <Badge
          :class="[
            'text-white font-semibold shadow-lg border-0 px-3 py-1',
            project.is_sellable
              ? 'bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500'
              : 'bg-gradient-to-r from-emerald-500 to-teal-500'
          ]"
        >
          {{ project.is_sellable ? 'Premium' : 'Open Source' }}
        </Badge>
      </div>

    </div>

    <CardContent class="p-6 space-y-5">
      <div class="flex items-center gap-3">
        <img
          :src="project.user?.profile_photo || `https://ui-avatars.com/api/?name=${project.user?.name}&background=random`"
          :alt="project.user?.name"
          class="w-10 h-10 rounded-full border border-gray-200 dark:border-white/10"
        />
        <div>
          <p class="text-sm font-medium text-gray-900 dark:text-white">{{ project.user?.name }}</p>
          <p class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
            <Calendar class="w-3 h-3" />
            {{ formatDate(project.created_at) }}
          </p>
        </div>
        <Badge variant="secondary" class="ml-auto text-xs">{{ categoryName }}</Badge>
      </div>

      <div>
        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-3 leading-tight transition line-clamp-2">
          {{ project.title }}
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
          {{ project.short_description || project.description }}
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <Badge
          v-for="tech in project.technologies?.slice(0, 3)"
          :key="tech.id"
          class="text-xs bg-gray-100 dark:bg-white/10 hover:bg-blue-500/25 dark:hover:bg-blue-400/50 text-gray-700 dark:text-white border border-gray-200 dark:border-white/10"
        >
          {{ tech.name }}
        </Badge>
        <Badge
          v-if="project.technologies?.length > 3"
          class="text-xs bg-gray-100 dark:bg-white/10 hover:bg-blue-500/25 dark:hover:bg-blue-400/50 text-gray-700 dark:text-white border border-gray-200 dark:border-white/10"
        >
          +{{ project.technologies.length - 3 }}
        </Badge>
        <Badge
          v-if="!project.technologies?.length"
          class="text-xs bg-gray-100 dark:bg-white/10 hover:bg-blue-500/25 dark:hover:bg-blue-400/50 text-gray-700 dark:text-white border border-gray-200 dark:border-white/10"
        >
          No technologies added
        </Badge>
      </div>

      <div
        v-if="showActions"
        class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-white/5 text-sm text-gray-600 dark:text-gray-300"
      >
        <div class="flex items-center gap-4">
          <button
            @click.stop="upvoteProject"
            class="flex items-center gap-1 transition-colors"
            :class="project.is_upvoted_by_user ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400'"
          >
            <CircleChevronUp class="w-4 h-4" />
            <span>{{ project.upvotes_count || 0 }}</span>
          </button>
          <button
            @click.stop="shareProject"
            class="flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            <Share2 class="w-4 h-4" />
            <span>Share</span>
          </button>
          <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
            <Download class="w-4 h-4 text-green-600 dark:text-green-400" />
            <span>{{ project.views?.toLocaleString() || 0 }}</span>
          </div>
        </div>
        <Button
          size="sm"
          class="bg-gradient-to-r from-blue-400 via-purple-400 to-indigo-400 hover:from-blue-500 hover:via-purple-500 hover:to-indigo-500 transition-all duration-300 text-white shadow-lg shadow-blue-400/30"
          @click.stop="navigateToProject"
        >
          {{ project.is_sellable && project.selling_price ? `Buy for $${project.selling_price}` : 'View Project' }}
          <ExternalLink class="w-4 h-4 ml-2" />
        </Button>
      </div>

    </CardContent>
  </Card>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
