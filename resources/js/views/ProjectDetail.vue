<script setup>
import { ref, onMounted, computed } from 'vue'
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
  ExternalLink
} from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

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
    const response = await axios.get(`/api/v1/projects/${route.params.id}`)
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
    const response = await axios.post(`/api/v1/projects/${project.value.id}/upvote`, {}, authStore.config)
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
    const response = await axios.post(`/api/v1/projects/${project.value.id}/fund`, {
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

const deleteProject = async () => {
  if (!confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
    return
  }

  try {
    await axios.delete(`/api/v1/projects/${project.value.id}`, authStore.config)
    
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

// Lifecycle
onMounted(() => {
  fetchProject()
})
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <Loader2 class="h-12 w-12 animate-spin text-blue-500" />
    </div>

    <!-- Project Content -->
    <div v-else-if="project" class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
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
      <Card :class="['border-0 shadow-xl overflow-hidden mb-8',
        themeStore.isDark ? 'bg-gray-800' : 'bg-white'
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

          <!-- Action Buttons -->
          <div class="absolute bottom-4 right-4 flex gap-2">
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
              @click="router.push(`/projects/${project.id}/edit`)"
            >
              <Edit class="h-4 w-4 mr-1" />
              Edit
            </Button>
            
            <Button 
              v-if="isOwner"
              variant="destructive"
              size="sm"
              @click="deleteProject"
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
                <h1 class="text-3xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  {{ project.title }}
                </h1>
                
                <div v-if="project.selling_price" class="text-2xl font-bold text-yellow-500">
                  {{ formatPrice(project.selling_price) }}
                </div>
              </div>

              <!-- Author Info -->
              <div class="flex items-center gap-3 mb-6">
                <div class="flex items-center gap-2">
                  <User class="h-4 w-4 text-gray-500" />
                  <span class="text-sm text-gray-500">by</span>
                  <router-link 
                    :to="`/@${project.user?.username}`"
                    class="font-medium hover:text-blue-500 transition-colors"
                  >
                    {{ project.user?.name }}
                  </router-link>
                </div>
                
                <span class="text-gray-500">•</span>
                
                <div class="flex items-center gap-2">
                  <Calendar class="h-4 w-4 text-gray-500" />
                  <span class="text-sm text-gray-500">{{ formatDate(project.created_at) }}</span>
                </div>
              </div>

              <!-- Description -->
              <p class="text-lg leading-relaxed mb-6" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                {{ project.description }}
              </p>

              <!-- Technologies -->
              <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
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
                  class="flex items-center gap-2 hover:text-blue-500 transition-colors"
                  :class="project.is_upvoted_by_user ? 'text-blue-500' : 'text-gray-500'"
                >
                  <Star class="h-5 w-5" :class="project.is_upvoted_by_user ? 'fill-current' : ''" />
                  <span class="font-medium">{{ project.upvotes_count || 0 }} upvotes</span>
                </button>
                
                <div class="flex items-center gap-2 text-gray-500">
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
                  @click="window.open(project.github_url, '_blank')"
                >
                  <Github class="h-4 w-4 mr-2" />
                  View on GitHub
                  <ExternalLink class="h-4 w-4 ml-2" />
                </Button>
                
                <Button 
                  v-if="project.demo_url"
                  class="w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600"
                  @click="window.open(project.demo_url, '_blank')"
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
              <Card :class="['border-0 shadow-lg',
                themeStore.isDark ? 'bg-gray-700' : 'bg-gray-50'
              ]">
                <CardHeader>
                  <CardTitle class="text-lg flex items-center">
                    <Heart class="h-5 w-5 mr-2 text-red-500" />
                    Support this Project
                  </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <p class="text-sm" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                    Help the developer continue working on this project by making a contribution.
                  </p>
                  
                  <Dialog v-model:open="showFundingDialog">
                    <DialogTrigger as="div">
                      <Button class="w-full bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600">
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
              <Card :class="['border-0 shadow-lg',
                themeStore.isDark ? 'bg-gray-700' : 'bg-gray-50'
              ]">
                <CardHeader>
                  <CardTitle class="text-lg">Project Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Type</span>
                    <span class="text-sm font-medium">{{ project.project_type === 'open' ? 'Open Source' : 'Closed Source' }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="text-sm font-medium">{{ project.is_active ? 'Active' : 'Inactive' }}</span>
                  </div>
                  
                  <div v-if="project.is_sellable" class="flex justify-between">
                    <span class="text-sm text-gray-500">Original Price</span>
                    <span class="text-sm font-medium">{{ formatPrice(project.original_price) }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Created</span>
                    <span class="text-sm font-medium">{{ formatDate(project.created_at) }}</span>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Funding History -->
      <Card v-if="project.funds && project.funds.length > 0" :class="['border-0 shadow-xl mb-8',
        themeStore.isDark ? 'bg-gray-800' : 'bg-white'
      ]">
        <CardHeader>
          <CardTitle class="flex items-center">
            <Heart class="h-5 w-5 mr-2 text-red-500" />
            Recent Funding
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div 
              v-for="fund in project.funds.slice(0, 5)" 
              :key="fund.id"
              class="flex items-center justify-between p-3 rounded-lg"
              :class="themeStore.isDark ? 'bg-gray-700' : 'bg-gray-50'"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-red-500 to-pink-500 flex items-center justify-center">
                  <Heart class="h-4 w-4 text-white" />
                </div>
                <div>
                  <p class="font-medium">{{ fund.user?.name }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(fund.created_at) }}</p>
                </div>
              </div>
              <div class="text-lg font-bold text-green-500">
                {{ formatPrice(fund.amount) }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>