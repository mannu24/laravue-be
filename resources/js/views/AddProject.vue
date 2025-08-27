<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import TechnologyInput from '@/components/elements/TechnologyInput.vue'
import {
  ArrowLeft,
  Upload,
  X,
  Code2,
  DollarSign,
  Globe,
  Github,
  Loader2
} from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const technologies = ref([])
const selectedTechnologies = ref([])

const projectData = ref({
  title: '',
  description: '',
  project_type: 'open',
  github_url: '',
  demo_url: '',
  is_sellable: false,
  original_price: '',
  selling_price: '',
  featured_image: null
})

const errors = ref({})

// Methods
const fetchTechnologies = async () => {
  try {
    const response = await axios.get('/api/v1/projects/technologies')
    technologies.value = response.data.data
  } catch (error) {
    console.error('Error fetching technologies:', error)
  }
}



const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) { // 2MB limit
      toast({
        title: "Error",
        description: "Image size must be less than 2MB",
        variant: "destructive"
      })
      return
    }
    projectData.value.featured_image = file
  }
}

const validateForm = () => {
  errors.value = {}

  if (!projectData.value.title.trim()) {
    errors.value.title = 'Project title is required'
  }

  if (!projectData.value.description.trim()) {
    errors.value.description = 'Project description is required'
  } else if (projectData.value.description.length < 10) {
    errors.value.description = 'Description must be at least 10 characters'
  }

  if (projectData.value.is_sellable) {
    if (!projectData.value.original_price) {
      errors.value.original_price = 'Original price is required for sellable projects'
    }
    if (!projectData.value.selling_price) {
      errors.value.selling_price = 'Selling price is required for sellable projects'
    }
    if (parseFloat(projectData.value.selling_price) > parseFloat(projectData.value.original_price)) {
      errors.value.selling_price = 'Selling price must be less than or equal to original price'
    }
  }

  if (projectData.value.github_url && !isValidUrl(projectData.value.github_url)) {
    errors.value.github_url = 'Please provide a valid GitHub URL'
  }

  if (projectData.value.demo_url && !isValidUrl(projectData.value.demo_url)) {
    errors.value.demo_url = 'Please provide a valid demo URL'
  }

  return Object.keys(errors.value).length === 0
}

const isValidUrl = (string) => {
  try {
    new URL(string)
    return true
  } catch (_) {
    return false
  }
}

const handleSubmit = async () => {
  if (!validateForm()) {
    toast({
      title: "Validation Error",
      description: "Please fix the errors in the form",
      variant: "destructive"
    })
    return
  }

  loading.value = true

  try {
    const formData = new FormData()

    // Add basic fields
    formData.append('title', projectData.value.title)
    formData.append('description', projectData.value.description)
    formData.append('project_type', projectData.value.project_type)
    formData.append('is_sellable', projectData.value.is_sellable ? 1 : 0)

    if (projectData.value.github_url) {
      formData.append('github_url', projectData.value.github_url)
    }

    if (projectData.value.demo_url) {
      formData.append('demo_url', projectData.value.demo_url)
    }

    if (projectData.value.is_sellable) {
      formData.append('original_price', projectData.value.original_price)
      formData.append('selling_price', projectData.value.selling_price)
    }

    if (selectedTechnologies.value.length > 0) {
      selectedTechnologies.value.forEach(tech => {
        formData.append('technologies[]', tech.name || tech)
      })
    }

    if (projectData.value.featured_image) {
      formData.append('featured_image', projectData.value.featured_image)
    }

    const response = await axios.post('/api/v1/projects', formData, {
      ...authStore.config,
      headers: {
        ...authStore.config.headers,
        'Content-Type': 'multipart/form-data'
      }
    })

    toast({
      title: "Success",
      description: "Project created successfully!",
    })

    router.push(`/projects/${response.data.data.id}`)

  } catch (error) {
    console.error('Error creating project:', error)

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }

    toast({
      title: "Error",
      description: error.response?.data?.message || "Failed to create project",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchTechnologies()
})
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <Button
          variant="ghost"
          class="mb-4"
          @click="router.back()"
        >
          <ArrowLeft class="h-4 w-4 mr-2" />
          Back
        </Button>

        <div class="text-center">
          <h1 class="text-4xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Add Your Project
          </h1>
          <p class="text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Share your amazing project with the Laravel and Vue.js community
          </p>
        </div>
      </div>

      <!-- Form -->
      <div class="max-w-4xl mx-auto">
        <Card :class="['border-0 shadow-xl',
          themeStore.isDark ? 'bg-gray-800' : 'bg-white'
        ]">
          <CardHeader>
            <CardTitle class="flex items-center">
              <Code2 class="h-5 w-5 mr-2" />
              Project Information
            </CardTitle>
            <CardDescription>
              Fill in the details about your project. Be descriptive to help others understand what you've built.
            </CardDescription>
          </CardHeader>

          <CardContent>
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Basic Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <Label for="title">Project Title *</Label>
                  <Input
                    id="title"
                    v-model="projectData.title"
                    placeholder="Enter your project title"
                    :class="errors.title ? 'border-red-500' : ''"
                  />
                  <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
                </div>

                <div class="md:col-span-2">
                  <Label for="description">Description *</Label>
                  <Textarea
                    id="description"
                    v-model="projectData.description"
                    placeholder="Describe your project, its features, and what makes it special..."
                    rows="6"
                    :class="errors.description ? 'border-red-500' : ''"
                  />
                  <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
                </div>

                <div>
                  <Label for="project_type">Project Type *</Label>
                  <select id="project_type" v-model="projectData.project_type" class="w-full rounded-md border px-3 py-2"
                    :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900'"
                  >
                    <option value="open">Open Source</option>
                    <option value="closed">Closed Source</option>
                  </select>
                </div>

                <div class="flex items-center space-x-2">
                  <input
                    id="is_sellable"
                    v-model="projectData.is_sellable"
                    type="checkbox"
                    class="rounded border-gray-300"
                  />
                  <Label for="is_sellable">This project is for sale</Label>
                </div>
              </div>

              <!-- URLs -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label for="github_url">
                    <Github class="h-4 w-4 inline mr-2" />
                    GitHub URL
                  </Label>
                  <Input
                    id="github_url"
                    v-model="projectData.github_url"
                    placeholder="https://github.com/username/repo"
                    :class="errors.github_url ? 'border-red-500' : ''"
                  />
                  <p v-if="errors.github_url" class="text-red-500 text-sm mt-1">{{ errors.github_url }}</p>
                </div>

                <div>
                  <Label for="demo_url">
                    <Globe class="h-4 w-4 inline mr-2" />
                    Demo URL
                  </Label>
                  <Input
                    id="demo_url"
                    v-model="projectData.demo_url"
                    placeholder="https://demo.example.com"
                    :class="errors.demo_url ? 'border-red-500' : ''"
                  />
                  <p v-if="errors.demo_url" class="text-red-500 text-sm mt-1">{{ errors.demo_url }}</p>
                </div>
              </div>

              <!-- Pricing (if sellable) -->
              <div v-if="projectData.is_sellable" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label for="original_price">
                    <DollarSign class="h-4 w-4 inline mr-2" />
                    Original Price *
                  </Label>
                  <Input
                    id="original_price"
                    v-model="projectData.original_price"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="99.99"
                    :class="errors.original_price ? 'border-red-500' : ''"
                  />
                  <p v-if="errors.original_price" class="text-red-500 text-sm mt-1">{{ errors.original_price }}</p>
                </div>

                <div>
                  <Label for="selling_price">
                    <DollarSign class="h-4 w-4 inline mr-2" />
                    Selling Price *
                  </Label>
                  <Input
                    id="selling_price"
                    v-model="projectData.selling_price"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="79.99"
                    :class="errors.selling_price ? 'border-red-500' : ''"
                  />
                  <p v-if="errors.selling_price" class="text-red-500 text-sm mt-1">{{ errors.selling_price }}</p>
                </div>
              </div>

              <!-- Technologies -->
              <div>
                <Label>Technologies Used</Label>
                <TechnologyInput
                  v-model="selectedTechnologies"
                  :available-technologies="technologies"
                  placeholder="Search technologies (e.g., Vue.js, Laravel)"
                />
              </div>

              <!-- Featured Image -->
              <div>
                <Label for="featured_image">
                  <Upload class="h-4 w-4 inline mr-2" />
                  Featured Image
                </Label>
                <Input
                  id="featured_image"
                  type="file"
                  accept="image/*"
                  @change="handleImageUpload"
                />
                <p class="text-sm text-gray-500 mt-1">
                  Upload a screenshot or preview image (max 2MB)
                </p>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <Button
                  type="submit"
                  :disabled="loading"
                  class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-8 py-3"
                >
                  <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
                  <Code2 v-else class="h-4 w-4 mr-2" />
                  {{ loading ? 'Creating Project...' : 'Create Project' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom checkbox styling */
input[type="checkbox"] {
  @apply w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600;
}
</style>
