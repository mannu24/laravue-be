<script setup>
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  ArrowLeft,
  Star,
  Eye,
  Github,
  Globe,
  DollarSign,
  Heart,
  Share2,
  Edit,
  Trash2,
  Calendar,
  User,
  Code2,
  Loader2,
  ExternalLink,
  FileText,
  Package,
  BookOpen,
  Wrench,
  CheckCircle2,
  AlertCircle,
  Link as LinkIcon,
  Tag,
  Clock,
  Languages
} from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'

const route = useRoute()
const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

// Reactive data
const project = ref(null)
const loading = ref(true)
const fundingAmount = ref('')
const showFundingDialog = ref(false)
const fundingLoading = ref(false)
const showDeleteDialog = ref(false)

// Computed
const isOwner = computed(() => {
  return authStore.isAuthenticated && project.value?.user?.id === authStore.user?.id
})

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price)
}

// Methods
const fetchProject = async () => {
  try {
    const response = await axios.get(
      `/api/v1/projects/${route.params.slug}`,
      authStore.isAuthenticated ? authStore.config : {}
    )
    project.value = response.data.data
  } catch (error) {
    console.error('Error fetching project:', error)
    toast({
      title: "Error",
      description: "Failed to load project",
      variant: "destructive"
    })
    router.push('/projects')
  } finally {
    loading.value = false
  }
}

const upvoteProject = async () => {
  if (!authStore.isAuthenticated) {
    toast({
      title: "Authentication Required",
      description: "Please login to upvote projects",
      variant: "destructive"
    })
    return
  }

  try {
    const response = await axios.post(`/api/v1/projects/${project.value.slug}/upvote`, {}, authStore.config)
    project.value.is_upvoted_by_user = response.data.data.is_upvoted
    project.value.upvotes_count = response.data.data.upvotes_count
    
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

const fundProject = async () => {
  if (!authStore.isAuthenticated) {
    toast({
      title: "Authentication Required",
      description: "Please login to fund projects",
      variant: "destructive"
    })
    return
  }

  if (!fundingAmount.value || parseFloat(fundingAmount.value) <= 0) {
    toast({
      title: "Error",
      description: "Please enter a valid amount",
      variant: "destructive"
    })
    return
  }

  fundingLoading.value = true

  try {
    const response = await axios.post(`/api/v1/projects/${project.value.slug}/fund`, {
      amount: parseFloat(fundingAmount.value)
    }, authStore.config)
    
    toast({
      title: "Success",
      description: response.data.message,
    })
    
    showFundingDialog.value = false
    fundingAmount.value = ''
    
    // Refresh project data to show updated funding
    await fetchProject()
  } catch (error) {
    console.error('Error funding project:', error)
    toast({
      title: "Error",
      description: error.response?.data?.message || "Failed to fund project",
      variant: "destructive"
    })
  } finally {
    fundingLoading.value = false
  }
}

const showDeleteConfirmation = () => {
  showDeleteDialog.value = true
}

const deleteProject = async () => {
  try {
    await axios.delete(`/api/v1/projects/${project.value.slug}`, authStore.config)
    
    toast({
      title: "Success",
      description: "Project deleted successfully",
    })
    
    router.push('/projects')
  } catch (error) {
    console.error('Error deleting project:', error)
    toast({
      title: "Error",
      description: "Failed to delete project",
      variant: "destructive"
    })
  }
}

const shareProject = () => {
  if (navigator.share) {
    navigator.share({
      title: project.value.title,
      text: project.value.description,
      url: window.location.href,
    })
  } else {
    navigator.clipboard.writeText(window.location.href)
    toast({
      title: "Link Copied",
      description: "Project link copied to clipboard",
    })
  }
}

const openNewTab = (url) => {
  window.open(url, '_blank')
}

// SEO Meta Tags Management
const updateMetaTags = () => {
  if (!project.value) return

  // Update or create meta title
  let titleTag = document.querySelector('title')
  if (!titleTag) {
    titleTag = document.createElement('title')
    document.head.appendChild(titleTag)
  }
  titleTag.textContent = project.value.meta_title || project.value.title

  // Update or create meta description
  let metaDescription = document.querySelector('meta[name="description"]')
  if (!metaDescription) {
    metaDescription = document.createElement('meta')
    metaDescription.setAttribute('name', 'description')
    document.head.appendChild(metaDescription)
  }
  metaDescription.setAttribute('content', project.value.meta_description || project.value.short_description || project.value.description?.substring(0, 160) || '')

  // Update or create meta keywords
  if (project.value.meta_keywords) {
    let metaKeywords = document.querySelector('meta[name="keywords"]')
    if (!metaKeywords) {
      metaKeywords = document.createElement('meta')
      metaKeywords.setAttribute('name', 'keywords')
      document.head.appendChild(metaKeywords)
    }
    metaKeywords.setAttribute('content', project.value.meta_keywords)
  }

  // Update Open Graph tags
  let ogTitle = document.querySelector('meta[property="og:title"]')
  if (!ogTitle) {
    ogTitle = document.createElement('meta')
    ogTitle.setAttribute('property', 'og:title')
    document.head.appendChild(ogTitle)
  }
  ogTitle.setAttribute('content', project.value.meta_title || project.value.title)

  let ogDescription = document.querySelector('meta[property="og:description"]')
  if (!ogDescription) {
    ogDescription = document.createElement('meta')
    ogDescription.setAttribute('property', 'og:description')
    document.head.appendChild(ogDescription)
  }
  ogDescription.setAttribute('content', project.value.meta_description || project.value.short_description || project.value.description?.substring(0, 160) || '')

  let ogImage = document.querySelector('meta[property="og:image"]')
  if (project.value.featured_image) {
    if (!ogImage) {
      ogImage = document.createElement('meta')
      ogImage.setAttribute('property', 'og:image')
      document.head.appendChild(ogImage)
    }
    ogImage.setAttribute('content', project.value.featured_image)
  }

  let ogUrl = document.querySelector('meta[property="og:url"]')
  if (!ogUrl) {
    ogUrl = document.createElement('meta')
    ogUrl.setAttribute('property', 'og:url')
    document.head.appendChild(ogUrl)
  }
  ogUrl.setAttribute('content', window.location.href)
}

// Watch for project changes to update meta tags
watch(() => project.value, () => {
  if (project.value) {
    updateMetaTags()
  }
}, { immediate: true })

// Cleanup meta tags on unmount (optional, restore defaults)
onBeforeUnmount(() => {
  // Optionally restore default meta tags here if needed
})

// Lifecycle
onMounted(() => {
  fetchProject()
})
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-200',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-white text-gray-900'
  ]">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <Loader2 class="h-12 w-12 animate-spin text-laravel" />
    </div>

    <!-- Project Content -->
    <div v-else-if="project" class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-5">
        <Button 
          variant="ghost" 
          class="mb-4"
          @click="router.back()"
        >
          <ArrowLeft class="h-4 w-4 mr-2" />
          Back to Projects
        </Button>
      </div>

      <!-- Project Hero -->
      <Card :class="['overflow-hidden mb-8 border transition-colors',
        themeStore.isDark 
          ? 'bg-gray-900 border-gray-800' 
          : 'bg-white border-gray-200'
      ]">
        <!-- Featured Image -->
        <div class="relative h-64 md:h-96">
          <img 
            :src="project.featured_image || 'https://picsum.photos/1200/400?random=' + project.id" 
            :alt="project.title"
            class="w-full h-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
          
          <!-- Project Badge -->
          <div class="absolute top-4 right-4 z-10">
            <Badge :class="[
              'text-white font-medium',
              project.is_sellable
                ? 'bg-orange-500'
                : 'bg-vue'
            ]">
              {{ project.is_sellable ? 'Premium' : 'Open Source' }}
            </Badge>
          </div>

          <!-- Action Buttons -->
          <div class="absolute bottom-4 right-4 flex gap-2 z-10">
            <Button 
              variant="secondary"
              size="sm"
              @click="shareProject"
            >
              <Share2 class="h-4 w-4 mr-1" />
              Share
            </Button>
            
            <Button 
              v-if="isOwner"
              variant="secondary"
              size="sm"
              @click="router.push(`/projects/${project.slug}/edit`)"
            >
              <Edit class="h-4 w-4 mr-1" />
              Edit
            </Button>
            
            <Button 
              v-if="isOwner"
              variant="destructive"
              size="sm"
              @click="showDeleteConfirmation"
            >
              <Trash2 class="h-4 w-4 mr-1" />
              Delete
            </Button>
          </div>
        </div>

        <!-- Project Info -->
        <CardContent class="p-8">
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <!-- Main Content -->
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-4">
                <h1 class="text-3xl font-bold"
                  :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
                >
                  {{ project.title }}
                </h1>
                
                <div v-if="project.selling_price" 
                  class="text-2xl font-semibold text-orange-500">
                  {{ formatPrice(project.selling_price) }}
                </div>
              </div>

              <!-- Author Info -->
              <div class="flex items-center gap-3 mb-6 text-sm"
                :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
              >
                <div class="flex items-center gap-2">
                  <User class="h-4 w-4" />
                  <span>by</span>
                  <router-link 
                    :to="`/@${project.user?.username}`"
                    class="font-medium hover:underline transition-colors"
                    :class="themeStore.isDark ? 'text-gray-200 hover:text-white' : 'text-gray-900 hover:text-gray-700'"
                  >
                    {{ project.user?.name }}
                  </router-link>
                </div>
                
                <span>•</span>
                
                <div class="flex items-center gap-2">
                  <Calendar class="h-4 w-4" />
                  <span>{{ formatDate(project.created_at) }}</span>
                </div>
              </div>

              <!-- Description -->
              <p class="text-lg leading-relaxed mb-6" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                {{ project.description }}
              </p>

              <!-- Technologies -->
              <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3"
                  :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
                >
                  Technologies Used
                </h3>
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="tech in project.technologies" 
                    :key="tech.id"
                    variant="secondary"
                    class="text-sm"
                  >
                    <Code2 class="h-3 w-3 mr-1" />
                    {{ tech.name }}
                  </Badge>
                </div>
              </div>

              <!-- Stats -->
              <div class="flex items-center gap-6 mb-6">
                <button 
                  @click="upvoteProject"
                  class="flex items-center gap-2 transition-colors"
                  :class="project.is_upvoted_by_user 
                    ? 'text-laravel' 
                    : themeStore.isDark 
                      ? 'text-gray-400 hover:text-laravel' 
                      : 'text-gray-500 hover:text-laravel'"
                >
                  <Star class="h-5 w-5" :class="project.is_upvoted_by_user ? 'fill-current' : ''" />
                  <span class="font-medium">{{ project.upvotes_count || 0 }} upvotes</span>
                </button>
                
                <div class="flex items-center gap-2"
                  :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
                >
                  <Eye class="h-5 w-5" />
                  <span class="font-medium">{{ project.views?.toLocaleString() || 0 }} views</span>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:w-80 space-y-4">
              <!-- Action Buttons -->
              <div class="space-y-3">
                <Button 
                  v-if="project.github_url"
                  variant="outline"
                  class="w-full"
                  @click="openNewTab(project.github_url)"
                >
                  <Github class="h-4 w-4 mr-2" />
                  View on GitHub
                  <ExternalLink class="h-4 w-4 ml-2" />
                </Button>
                
                <Button 
                  v-if="project.demo_url"
                  class="w-full bg-vue hover:bg-vue/90"
                  @click="openNewTab(project.demo_url)"
                >
                  <Globe class="h-4 w-4 mr-2" />
                  Live Demo
                  <ExternalLink class="h-4 w-4 ml-2" />
                </Button>
                
                <Button 
                  v-if="project.is_sellable"
                  variant="outline"
                  class="w-full"
                >
                  <DollarSign class="h-4 w-4 mr-2" />
                  Buy Now - {{ formatPrice(project.selling_price) }}
                </Button>
              </div>

              <!-- Funding Section -->
              <Card :class="['border',
                themeStore.isDark 
                  ? 'bg-gray-900 border-gray-800' 
                  : 'bg-white border-gray-200'
              ]">
                <CardHeader>
                  <CardTitle class="text-lg flex items-center"
                    :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
                  >
                    <Heart class="h-5 w-5 mr-2 text-laravel" />
                    Support this Project
                  </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <p class="text-sm" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                    Help the developer continue working on this project by making a contribution.
                  </p>
                  
                  <Dialog :open="showFundingDialog" @update:open="showFundingDialog = $event">
                    <DialogTrigger as="div">
                      <Button class="w-full bg-laravel hover:bg-laravel/90">
                        <Heart class="h-4 w-4 mr-2" />
                        Fund Project
                      </Button>
                    </DialogTrigger>
                    <DialogContent>
                      <DialogHeader>
                        <DialogTitle>Fund {{ project.title }}</DialogTitle>
                        <DialogDescription>
                          Enter the amount you'd like to contribute to support this project.
                        </DialogDescription>
                      </DialogHeader>
                      
                      <div class="space-y-4">
                        <div>
                          <label class="text-sm font-medium">Amount (USD)</label>
                          <Input
                            v-model="fundingAmount"
                            type="number"
                            min="1"
                            step="0.01"
                            placeholder="10.00"
                            class="mt-1"
                          />
                        </div>
                      </div>
                      
                      <DialogFooter>
                        <Button variant="outline" @click="showFundingDialog = false">
                          Cancel
                        </Button>
                        <Button 
                          @click="fundProject"
                          :disabled="fundingLoading"
                          class="bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600"
                        >
                          <Loader2 v-if="fundingLoading" class="h-4 w-4 mr-2 animate-spin" />
                          <Heart v-else class="h-4 w-4 mr-2" />
                          {{ fundingLoading ? 'Processing...' : 'Fund Project' }}
                        </Button>
                      </DialogFooter>
                    </DialogContent>
                  </Dialog>
                </CardContent>
              </Card>

              <!-- Project Details -->
              <!-- <Card :class="['border',
                themeStore.isDark 
                  ? 'bg-gray-900 border-gray-800' 
                  : 'bg-white border-gray-200'
              ]">
                <CardHeader>
                  <CardTitle class="text-lg"
                    :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
                  >
                    Project Details
                  </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                  <div class="flex justify-between items-center py-2 border-b"
                    :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-100'"
                  >
                    <span class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Type</span>
                    <span class="text-sm font-medium"
                      :class="themeStore.isDark ? 'text-gray-200' : 'text-gray-900'"
                    >
                      {{ project.project_type === 'open' ? 'Open Source' : 'Closed Source' }}
                    </span>
                  </div>
                  
                  <div class="flex justify-between items-center py-2 border-b"
                    :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-100'"
                  >
                    <span class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Status</span>
                    <span class="text-sm font-medium"
                      :class="project.is_active 
                        ? 'text-vue' 
                        : themeStore.isDark 
                          ? 'text-gray-400' 
                          : 'text-gray-500'"
                    >
                      {{ project.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  
                  <div v-if="project.is_sellable" class="flex justify-between items-center py-2 border-b"
                    :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-100'"
                  >
                    <span class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Original Price</span>
                    <span class="text-sm font-medium text-orange-500">
                      {{ formatPrice(project.original_price) }}
                    </span>
                  </div>
                  
                  <div class="flex justify-between items-center py-2">
                    <span class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Created</span>
                    <span class="text-sm font-medium"
                      :class="themeStore.isDark ? 'text-gray-200' : 'text-gray-900'"
                    >
                      {{ formatDate(project.created_at) }}
                    </span>
                  </div>
                </CardContent>
              </Card> -->
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Extended Content Section -->
      <div v-if="project.long_description || project.features || project.requirements || project.installation_guide" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Long Description -->
        <Card v-if="project.long_description" :class="['border',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <FileText class="h-5 w-5" />
              Detailed Description
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="whitespace-pre-wrap text-sm leading-relaxed"
              :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'"
            >
              {{ project.long_description }}
            </div>
          </CardContent>
        </Card>

        <!-- Features -->
        <Card v-if="project.features && Array.isArray(project.features) && project.features.length > 0" :class="['border',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <CheckCircle2 class="h-5 w-5" />
              Features
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-2">
              <li v-for="(feature, index) in project.features" :key="index" 
                class="flex items-start gap-2"
                :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'"
              >
                <CheckCircle2 class="h-4 w-4 mt-0.5 text-vue flex-shrink-0" />
                <span>{{ feature }}</span>
              </li>
            </ul>
          </CardContent>
        </Card>

        <!-- Requirements -->
        <Card v-if="project.requirements" :class="['border',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <Code2 class="h-5 w-5" />
              Requirements
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="whitespace-pre-wrap text-sm leading-relaxed"
              :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'"
            >
              {{ project.requirements }}
            </div>
          </CardContent>
        </Card>

        <!-- Installation Guide -->
        <Card v-if="project.installation_guide" :class="['border',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <Code2 class="h-5 w-5" />
              Installation Guide
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="whitespace-pre-wrap text-sm leading-relaxed"
              :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'"
            >
              {{ project.installation_guide }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Version & License Section -->
      <Card v-if="project.version || project.current_version || project.changelog || project.license_type || project.license_url" :class="['border mb-8',
        themeStore.isDark 
          ? 'bg-gray-900 border-gray-800' 
          : 'bg-white border-gray-200'
      ]">
        <CardHeader>
          <CardTitle class="flex items-center gap-2"
            :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
          >
            <Package class="h-5 w-5" />
            Version & License
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="project.version">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Version</span>
              <p class="text-base font-semibold mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.version }}
              </p>
            </div>
            <div v-if="project.current_version">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Current Version</span>
              <p class="text-base font-semibold mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.current_version }}
              </p>
            </div>
            <div v-if="project.license_type" class="md:col-span-2">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">License</span>
              <div class="flex items-center gap-2 mt-1">
                <Badge variant="secondary">{{ project.license_type }}</Badge>
                <Button 
                  v-if="project.license_url"
                  variant="ghost"
                  size="sm"
                  @click="openNewTab(project.license_url)"
                >
                  <LinkIcon class="h-4 w-4 mr-1" />
                  View License
                  <ExternalLink class="h-4 w-4 ml-1" />
                </Button>
              </div>
            </div>
          </div>
          <div v-if="project.changelog" class="mt-4">
            <span class="text-sm font-medium mb-2 block" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Changelog</span>
            <div class="whitespace-pre-wrap text-sm leading-relaxed p-4 rounded-lg"
              :class="themeStore.isDark 
                ? 'bg-gray-800 text-gray-300' 
                : 'bg-gray-50 text-gray-700'"
            >
              {{ project.changelog }}
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Documentation & Support Links -->
      <Card v-if="project.documentation_url || project.support_url" :class="['border mb-8',
        themeStore.isDark 
          ? 'bg-gray-900 border-gray-800' 
          : 'bg-white border-gray-200'
      ]">
        <CardHeader>
          <CardTitle class="flex items-center gap-2"
            :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
          >
            <BookOpen class="h-5 w-5" />
            Documentation & Support
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-col sm:flex-row gap-3">
            <Button 
              v-if="project.documentation_url"
              variant="outline"
              class="flex-1"
              @click="openNewTab(project.documentation_url)"
            >
              <BookOpen class="h-4 w-4 mr-2" />
              Documentation
              <ExternalLink class="h-4 w-4 ml-2" />
            </Button>
            <Button 
              v-if="project.support_url"
              variant="outline"
              class="flex-1"
              @click="openNewTab(project.support_url)"
            >
              <LinkIcon class="h-4 w-4 mr-2" />
              Get Support
              <ExternalLink class="h-4 w-4 ml-2" />
            </Button>
          </div>
        </CardContent>
      </Card>


      <!-- Three Column Grid: Categorization, Recent Funding, Maintenance -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Categorization -->
        <Card v-if="project.category || project.difficulty_level || project.industry || (project.tags && project.tags.length > 0)" :class="['border',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <Tag class="h-5 w-5" />
              Categorization
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div v-if="project.category">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Category</span>
              <p class="text-base mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.category.name }}
              </p>
            </div>
            <div v-if="project.difficulty_level" class="flex justify-between items-center">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Difficulty Level</span>
              <Badge variant="secondary" class="mt-1 capitalize">
                {{ project.difficulty_level }}
              </Badge>
            </div>
            <div v-if="project.industry">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Industry</span>
              <p class="text-base mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.industry }}
              </p>
            </div>
            <div v-if="project.tags && project.tags.length > 0">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Tags</span>
              <div class="flex flex-wrap gap-2 mt-2">
                <Badge 
                  v-for="tag in project.tags" 
                  :key="tag"
                  variant="outline"
                  class="text-xs"
                >
                  {{ tag }}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Funding -->
        <Card v-if="project.funds && project.funds.length > 0" :class="['border flex flex-col',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <Heart class="h-5 w-5 mr-2 text-laravel" />
              Recent Funding
            </CardTitle>
          </CardHeader>
          <CardContent class="flex-1 overflow-hidden flex flex-col">
            <div class="flex-1 overflow-y-auto max-h-[210px] pr-2 space-y-3 custom-scrollbar"
            >
              <div 
                v-for="fund in project.funds" 
                :key="fund.id"
                class="flex items-center justify-between p-3 rounded-lg transition-colors"
                :class="themeStore.isDark 
                  ? 'bg-gray-800/50 hover:bg-gray-800' 
                  : 'bg-gray-50 hover:bg-gray-100'"
              >
                <div class="flex items-center gap-3 flex-1 min-w-0">
                  <div class="w-8 h-8 rounded-full bg-laravel flex items-center justify-center flex-shrink-0">
                    <Heart class="h-4 w-4 text-white" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm truncate"
                      :class="themeStore.isDark ? 'text-gray-100' : 'text-gray-900'"
                    >
                      {{ fund.user?.name }}
                    </p>
                    <p class="text-xs flex items-center gap-1"
                      :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'"
                    >
                      <Calendar class="h-3 w-3" />
                      {{ formatDate(fund.created_at) }}
                    </p>
                  </div>
                </div>
                <div class="text-base font-semibold text-vue ml-2 flex-shrink-0">
                  {{ formatPrice(fund.amount) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Maintenance & Updates Section -->
        <Card v-if="project.estimated_build_time || project.language || project.update_frequency || project.is_maintained !== undefined || project.maintenance_status || project.deprecation_notice || project.migration_guide_url" :class="['border',
          themeStore.isDark 
            ? 'bg-gray-900 border-gray-800' 
            : 'bg-white border-gray-200'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center gap-2"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
            >
              <Wrench class="h-5 w-5" />
              Maintenance & Updates
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-if="project.estimated_build_time">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Estimated Build Time</span>
              <p class="text-base mt-1 flex items-center gap-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                <Clock class="h-4 w-4" />
                {{ project.estimated_build_time }}
              </p>
            </div>
            <div v-if="project.language">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Primary Language</span>
              <p class="text-base mt-1 flex items-center gap-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                <Languages class="h-4 w-4" />
                {{ project.language.toUpperCase() }}
              </p>
            </div>
            <div v-if="project.update_frequency">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Update Frequency</span>
              <p class="text-base mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.update_frequency }}
              </p>
            </div>
            <div v-if="project.is_maintained !== undefined">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Actively Maintained</span>
              <div class="mt-1 flex items-center gap-2">
                <Badge :class="project.is_maintained 
                  ? 'bg-green-500 text-white' 
                  : 'bg-gray-500 text-white'"
                >
                  {{ project.is_maintained ? 'Yes' : 'No' }}
                </Badge>
              </div>
            </div>
            <div v-if="project.maintenance_status">
              <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Maintenance Status</span>
              <p class="text-base mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.maintenance_status }}
              </p>
            </div>
            <div v-if="project.deprecation_notice" class="mt-4 p-3 rounded-lg border"
              :class="themeStore.isDark 
                ? 'bg-yellow-900/20 border-yellow-800 text-yellow-200' 
                : 'bg-yellow-50 border-yellow-200 text-yellow-800'"
            >
              <div class="flex items-start gap-2">
                <AlertCircle class="h-4 w-4 flex-shrink-0 mt-0.5" />
                <div>
                  <p class="font-semibold text-xs mb-1">Deprecation Notice</p>
                  <p class="text-xs whitespace-pre-wrap line-clamp-3">{{ project.deprecation_notice }}</p>
                </div>
              </div>
            </div>
            <div v-if="project.migration_guide_url" class="mt-2">
              <Button 
                variant="outline"
                size="sm"
                class="w-full"
                @click="openNewTab(project.migration_guide_url)"
              >
                <LinkIcon class="h-3 w-3 mr-1" />
                Migration Guide
                <ExternalLink class="h-3 w-3 ml-1" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Commerce Details (if sellable) -->
      <Card v-if="project.is_sellable && (project.delivery_method || project.affiliate_enabled)" :class="['border mb-8',
        themeStore.isDark 
          ? 'bg-gray-900 border-gray-800' 
          : 'bg-white border-gray-200'
      ]">
        <CardHeader>
          <CardTitle class="flex items-center gap-2"
            :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
          >
            <DollarSign class="h-5 w-5" />
            Commerce Details
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <div v-if="project.delivery_method">
            <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Delivery Method</span>
            <p class="text-base mt-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              {{ project.delivery_method }}
            </p>
          </div>
          <div v-if="project.affiliate_enabled">
            <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">Affiliate Program</span>
            <div class="mt-1">
              <Badge class="bg-green-500 text-white">Enabled</Badge>
              <span v-if="project.affiliate_commission" class="text-sm ml-2"
                :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'"
              >
                {{ project.affiliate_commission }}% commission
              </span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model:open="showDeleteDialog"
      title="Delete Project"
      description="Are you sure you want to delete this project? This action cannot be undone."
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="destructive"
      @confirm="deleteProject"
    />
  </div>
</template>

<style scoped>
/* Custom Scrollbar Styles */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark .custom-scrollbar {
  scrollbar-color: rgba(75, 85, 99, 0.5) transparent;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}
</style>