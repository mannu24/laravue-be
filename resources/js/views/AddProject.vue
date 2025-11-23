<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import ProjectForm from '@/components/projects/ProjectForm.vue'
import { ArrowLeft, Code2 } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

const loading = ref(false)
const formErrors = ref({})

const handleSubmit = async (formData) => {
  loading.value = true
  formErrors.value = {}

  try {
    const data = new FormData()

    // Basic fields
    data.append('title', formData.title)
    data.append('description', formData.description)
    data.append('project_type', formData.project_type)
    data.append('is_sellable', formData.is_sellable ? 1 : 0)
    data.append('status', 'draft') // Default to draft

    // Optional fields
    if (formData.short_description) data.append('short_description', formData.short_description)
    if (formData.excerpt) data.append('excerpt', formData.excerpt)
    if (formData.github_url) data.append('github_url', formData.github_url)
    if (formData.demo_url) data.append('demo_url', formData.demo_url)
    if (formData.category_id) data.append('category_id', formData.category_id)
    if (formData.difficulty_level) data.append('difficulty_level', formData.difficulty_level)
    if (formData.industry) data.append('industry', formData.industry)
    if (formData.currency) data.append('currency', formData.currency)

    // Pricing
    if (formData.is_sellable) {
      data.append('original_price', formData.original_price)
      data.append('selling_price', formData.selling_price)
      if (formData.discount_percentage) data.append('discount_percentage', formData.discount_percentage)
      if (formData.discount_start_date) data.append('discount_start_date', formData.discount_start_date)
      if (formData.discount_end_date) data.append('discount_end_date', formData.discount_end_date)
      if (formData.stock_quantity) data.append('stock_quantity', formData.stock_quantity)
      data.append('is_digital', formData.is_digital ? 1 : 0)
    }

    // Technologies
    if (formData.technologies && formData.technologies.length > 0) {
      formData.technologies.forEach(tech => {
        data.append('technologies[]', tech.name || tech)
      })
    }

    // Featured image
    if (formData.featured_image) {
      data.append('featured_image', formData.featured_image)
    }

    const response = await axios.post('/api/v1/projects', data, {
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

    router.push(`/projects/${response.data.data.slug}`)

  } catch (error) {
    console.error('Error creating project:', error)

    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors
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

const handleCancel = () => {
  router.back()
}
</script>

<template>
  <div class="min-h-screen">
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
            Share your amazing project with the community
          </p>
        </div>
      </div>

      <!-- Form -->
      <div class="max-w-4xl mx-auto">
        <ProjectForm
          :loading="loading"
          submit-label="Create Project"
          @submit="handleSubmit"
          @cancel="handleCancel"
                />
      </div>
    </div>
  </div>
</template>
