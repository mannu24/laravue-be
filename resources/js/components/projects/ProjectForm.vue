<script setup>
import { ref, computed, watch } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Switch } from '@/components/ui/switch'
import CategorySelector from './CategorySelector.vue'
import TechnologyInput from '@/components/elements/TechnologyInput.vue'
import {
  Upload,
  X,
  Code2,
  DollarSign,
  Globe,
  Github,
  Loader2,
  Save,
  Send,
  Search,
  FileText,
  Shield,
  BookOpen,
  Wrench,
  Package,
  Plus,
  Link as LinkIcon
} from 'lucide-vue-next'

const props = defineProps({
  project: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  submitLabel: {
    type: String,
    default: 'Create Project'
  }
})

const emit = defineEmits(['submit', 'cancel'])

// Initialize technologies from project if editing
const initializeTechnologies = () => {
  if (props.project?.technologies && props.project.technologies.length > 0) {
    return props.project.technologies.map(tech => {
      // Handle both object and string formats
      if (typeof tech === 'string') {
        return { name: tech }
      }
      return {
        id: tech.id,
        name: tech.name || tech
      }
    })
  }
  return []
}

// Initialize features array from project if editing
const initializeFeatures = () => {
  if (props.project?.features) {
    if (Array.isArray(props.project.features)) {
      return props.project.features
    }
    // If it's a JSON string, parse it
    try {
      return JSON.parse(props.project.features)
    } catch {
      return []
    }
  }
  return []
}

const formData = ref({
  title: props.project?.title || '',
  description: props.project?.description || '',
  short_description: props.project?.short_description || '',
  excerpt: props.project?.excerpt || '',
  project_type: props.project?.project_type || 'open',
  github_url: props.project?.github_url || '',
  demo_url: props.project?.demo_url || '',
  is_sellable: props.project?.is_sellable || false,
  original_price: props.project?.original_price || '',
  selling_price: props.project?.selling_price || '',
  category_id: props.project?.category_id || null,
  difficulty_level: props.project?.difficulty_level || '',
  industry: props.project?.industry || '',
  tags: props.project?.tags || [],
  currency: props.project?.currency || 'USD',
  discount_percentage: props.project?.discount_percentage || '',
  discount_start_date: props.project?.discount_start_date || '',
  discount_end_date: props.project?.discount_end_date || '',
  stock_quantity: props.project?.stock_quantity || '',
  is_digital: props.project?.is_digital !== undefined ? props.project.is_digital : true,
  featured_image: null,
  technologies: initializeTechnologies(),
  // SEO
  meta_title: props.project?.meta_title || '',
  meta_description: props.project?.meta_description || '',
  meta_keywords: props.project?.meta_keywords || '',
  // Extended Content
  long_description: props.project?.long_description || '',
  features: initializeFeatures(),
  requirements: props.project?.requirements || '',
  installation_guide: props.project?.installation_guide || '',
  // Version & License
  version: props.project?.version || '',
  changelog: props.project?.changelog || '',
  license_type: props.project?.license_type || '',
  license_url: props.project?.license_url || '',
  // Documentation
  documentation_url: props.project?.documentation_url || '',
  support_url: props.project?.support_url || '',
  // Maintenance
  estimated_build_time: props.project?.estimated_build_time || '',
  language: props.project?.language || 'en',
  update_frequency: props.project?.update_frequency || '',
  is_maintained: props.project?.is_maintained !== undefined ? props.project.is_maintained : true,
  maintenance_status: props.project?.maintenance_status || '',
  deprecation_notice: props.project?.deprecation_notice || '',
  migration_guide_url: props.project?.migration_guide_url || '',
  current_version: props.project?.current_version || '',
  // Commerce Advanced
  delivery_method: props.project?.delivery_method || '',
  affiliate_enabled: props.project?.affiliate_enabled || false,
  affiliate_commission: props.project?.affiliate_commission || ''
})

const errors = ref({})

// Initialize image preview - handle both URL string and object
const getImageUrl = () => {
  if (!props.project?.featured_image) return null
  if (typeof props.project.featured_image === 'string') {
    return props.project.featured_image
  }
  return props.project.featured_image?.url || props.project.featured_image?.original_url || null
}

const imagePreview = ref(getImageUrl())

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      errors.value.featured_image = 'Image size must be less than 2MB'
      return
    }
    formData.value.featured_image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    errors.value.featured_image = null
  }
}

const removeImage = () => {
  formData.value.featured_image = null
  imagePreview.value = null
}

const handleSubmit = () => {
  emit('submit', formData.value)
}

const handleCancel = () => {
  emit('cancel')
}

// Watch for sellable changes
watch(() => formData.value.is_sellable, (newValue) => {
  if (!newValue) {
    formData.value.original_price = ''
    formData.value.selling_price = ''
  }
})

// Features management
const newFeature = ref('')
const addFeature = () => {
  const feature = newFeature.value.trim()
  if (feature && !formData.value.features.includes(feature)) {
    formData.value.features.push(feature)
    newFeature.value = ''
  }
}
const removeFeature = (index) => {
  formData.value.features.splice(index, 1)
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Basic Information -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Code2 class="h-5 w-5" />
          Basic Information
        </CardTitle>
        <CardDescription>
          Essential details about your project
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <!-- Title -->
        <div>
          <Label for="title">Project Title *</Label>
          <Input
            id="title"
            class="mt-2"
            v-model="formData.title"
            placeholder="Enter your project title"
            :class="errors.title ? 'border-red-500' : ''"
            required
          />
          <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
        </div>

        <!-- Short Description -->
        <div>
          <Label for="short_description">Short Description</Label>
          <Input
            id="short_description"
            class="mt-2"
            v-model="formData.short_description"
            placeholder="Brief description for cards and previews (max 500 chars)"
            maxlength="500"
          />
          <p class="text-xs text-muted-foreground mt-1">
            {{ formData.short_description.length }}/500 characters
          </p>
        </div>

        <!-- Excerpt -->
        <div>
          <Label for="excerpt">Excerpt</Label>
          <Textarea
            id="excerpt"
            class="mt-2"
            v-model="formData.excerpt"
            placeholder="SEO-friendly excerpt (max 500 chars)"
            rows="2"
            maxlength="500"
          />
          <p class="text-xs text-muted-foreground mt-1">
            {{ formData.excerpt.length }}/500 characters
          </p>
        </div>

        <!-- Full Description -->
        <div>
          <Label for="description">Full Description *</Label>
          <Textarea
            id="description"
            class="mt-2"
            v-model="formData.description"
            placeholder="Describe your project in detail..."
            rows="6"
            :class="errors.description ? 'border-red-500' : ''"
            required
          />
          <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
        </div>

        <!-- Project Type & Sellable -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="project_type">Project Type *</Label>
            <select 
              id="project_type" 
              v-model="formData.project_type"
              class="w-full rounded-md border px-3 py-2 bg-background mt-2"
              required
            >
              <option value="open">Open Source</option>
              <option value="closed">Closed Source</option>
            </select>
          </div>

          <div class="flex items-center space-x-2 pt-8">
            <Switch
              id="is_sellable"
              v-model:checked="formData.is_sellable"
            />
            <Label for="is_sellable" class="cursor-pointer">This project is for sale</Label>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Categorization -->
    <Card>
      <CardHeader>
        <CardTitle>Categorization</CardTitle>
        <CardDescription>
          Help users discover your project
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <CategorySelector v-model="formData.category_id" />
          
          <div>
            <Label for="difficulty_level">Difficulty Level</Label>
            <select 
              id="difficulty_level" 
              v-model="formData.difficulty_level"
              class="w-full rounded-md border px-3 py-2 bg-background mt-2"
            >
              <option value="">Select difficulty</option>
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
              <option value="expert">Expert</option>
            </select>
          </div>
        </div>

        <div>
          <Label for="industry">Industry</Label>
          <Input
            id="industry"
            v-model="formData.industry"
            placeholder="e.g., E-commerce, Healthcare, Education"
            class="mt-2"
          />
        </div>

        <!-- Technologies -->
        <div>
          <Label>Technologies Used</Label>
          <TechnologyInput
            v-model="formData.technologies"
            placeholder="Search and add technologies"
          />
        </div>
      </CardContent>
    </Card>

    <!-- SEO Settings -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Search class="h-5 w-5" />
          SEO Settings
        </CardTitle>
        <CardDescription>
          Optimize your project for search engines
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div>
          <Label for="meta_title">Meta Title</Label>
          <Input
            id="meta_title"
            v-model="formData.meta_title"
            placeholder="Custom SEO title (defaults to project title)"
            maxlength="255"
            class="mt-2"
          />
          <p class="text-xs text-muted-foreground mt-1">
            {{ formData.meta_title.length }}/255 characters
          </p>
        </div>

        <div>
          <Label for="meta_description">Meta Description</Label>
          <Textarea
            id="meta_description"
            v-model="formData.meta_description"
            placeholder="SEO-friendly description (defaults to excerpt)"
            rows="3"
            maxlength="500"
            class="mt-2"
          />
          <p class="text-xs text-muted-foreground mt-1">
            {{ formData.meta_description.length }}/500 characters
          </p>
        </div>

        <div>
          <Label for="meta_keywords">Meta Keywords</Label>
          <Input
            id="meta_keywords"
            v-model="formData.meta_keywords"
            placeholder="Comma-separated keywords (e.g., laravel, vue, api)"
            maxlength="500"
            class="mt-2"
          />
          <p class="text-xs text-muted-foreground mt-1">
            Separate keywords with commas
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Extended Content -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <FileText class="h-5 w-5" />
          Extended Content
        </CardTitle>
        <CardDescription>
          Detailed information about your project
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div>
          <Label for="long_description">Long Description</Label>
          <Textarea
            id="long_description"
            v-model="formData.long_description"
            placeholder="Extended detailed description (supports markdown)"
            rows="8"
            class="mt-2"
          />
          <p class="text-xs text-muted-foreground mt-1">
            Use markdown for formatting
          </p>
        </div>

        <div>
          <Label>Features</Label>
          <div class="mt-2 space-y-2">
            <div v-if="formData.features.length > 0" class="flex flex-wrap gap-2 mb-2">
              <Badge
                v-for="(feature, index) in formData.features"
                :key="index"
                variant="secondary"
                class="text-sm"
              >
                {{ feature }}
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-4 w-4 p-0 ml-1"
                  @click="removeFeature(index)"
                >
                  <X class="h-3 w-3" />
                </Button>
              </Badge>
            </div>
            <div class="flex gap-2">
              <Input
                v-model="newFeature"
                placeholder="Add a feature (press Enter)"
                @keydown.enter.prevent="addFeature"
              />
              <Button
                type="button"
                variant="outline"
                size="sm"
                @click="addFeature"
              >
                <Plus class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>

        <div>
          <Label for="requirements">Requirements</Label>
          <Textarea
            id="requirements"
            v-model="formData.requirements"
            placeholder="System requirements, dependencies, etc."
            rows="4"
            class="mt-2"
          />
        </div>

        <div>
          <Label for="installation_guide">Installation Guide</Label>
          <Textarea
            id="installation_guide"
            v-model="formData.installation_guide"
            placeholder="Step-by-step installation instructions"
            rows="6"
            class="mt-2"
          />
        </div>
      </CardContent>
    </Card>

    <!-- Version & License -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Package class="h-5 w-5" />
          Version & License
        </CardTitle>
        <CardDescription>
          Version information and licensing details
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="version">Version</Label>
            <Input
              id="version"
              v-model="formData.version"
              placeholder="e.g., 1.0.0"
              maxlength="50"
              class="mt-2"
            />
          </div>

          <div>
            <Label for="current_version">Current Version</Label>
            <Input
              id="current_version"
              v-model="formData.current_version"
              placeholder="e.g., 1.2.3"
              maxlength="50"
              class="mt-2"
            />
          </div>
        </div>

        <div>
          <Label for="changelog">Changelog</Label>
          <Textarea
            id="changelog"
            v-model="formData.changelog"
            placeholder="Version history and changes"
            rows="5"
            class="mt-2"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="license_type">License Type</Label>
            <Input
              id="license_type"
              v-model="formData.license_type"
              placeholder="e.g., MIT, GPL-3.0, Apache-2.0"
              maxlength="100"
              class="mt-2"
            />
          </div>

          <div>
            <Label for="license_url">License URL</Label>
            <Input
              id="license_url"
              v-model="formData.license_url"
              type="url"
              placeholder="https://example.com/license"
              class="mt-2"
            />
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Documentation Links -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <BookOpen class="h-5 w-5" />
          Documentation & Support
        </CardTitle>
        <CardDescription>
          Links to documentation and support resources
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="documentation_url">
              <LinkIcon class="h-4 w-4 inline mr-2" />
              Documentation URL
            </Label>
            <Input
              id="documentation_url"
              v-model="formData.documentation_url"
              type="url"
              placeholder="https://docs.example.com"
              class="mt-2"
            />
          </div>

          <div>
            <Label for="support_url">
              <LinkIcon class="h-4 w-4 inline mr-2" />
              Support URL
            </Label>
            <Input
              id="support_url"
              v-model="formData.support_url"
              type="url"
              placeholder="https://support.example.com"
              class="mt-2"
            />
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Links -->
    <Card>
      <CardHeader>
        <CardTitle>Links & Resources</CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="github_url">
              <Github class="h-4 w-4 inline mr-2" />
              GitHub URL
            </Label>
            <Input
              id="github_url"
              v-model="formData.github_url"
              type="url"
              placeholder="https://github.com/username/repo"
              class="mt-2"
            />
          </div>

          <div>
            <Label for="demo_url">
              <Globe class="h-4 w-4 inline mr-2" />
              Demo URL
            </Label>
            <Input
              id="demo_url"
              v-model="formData.demo_url"
              type="url"
              placeholder="https://demo.example.com"
              class="mt-2"
            />
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Pricing (if sellable) -->
    <Card v-if="formData.is_sellable">
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <DollarSign class="h-5 w-5" />
          Pricing
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <Label for="currency">Currency</Label>
            <Input
              id="currency"
              v-model="formData.currency"
              placeholder="USD"
              maxlength="3"
              class="mt-2"
            />
          </div>
          <div>
            <Label for="original_price">Original Price *</Label>
            <Input
              id="original_price"
              v-model="formData.original_price"
              type="number"
              min="0"
              step="0.01"
              placeholder="99.99"
              required
              class="mt-2"
            />
          </div>
          <div>
            <Label for="selling_price">Selling Price *</Label>
            <Input
              id="selling_price"
              v-model="formData.selling_price"
              type="number"
              min="0"
              step="0.01"
              placeholder="79.99"
              required
              class="mt-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <Label for="discount_percentage">Discount %</Label>
            <Input
              id="discount_percentage"
              v-model="formData.discount_percentage"
              type="number"
              min="0"
              max="100"
              placeholder="20"
              class="mt-2"
            />
          </div>
          <div>
            <Label for="discount_start_date">Discount Start</Label>
            <Input
              id="discount_start_date"
              v-model="formData.discount_start_date"
              type="date"
              class="mt-2"
            />
          </div>
          <div>
            <Label for="discount_end_date">Discount End</Label>
            <Input
              id="discount_end_date"
              v-model="formData.discount_end_date"
              type="date"
              class="mt-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="stock_quantity">Stock Quantity</Label>
            <Input
              id="stock_quantity"
              v-model="formData.stock_quantity"
              type="number"
              min="0"
              placeholder="Leave empty for unlimited"
              class="mt-2"
            />
          </div>
          <div class="flex items-center space-x-2 pt-8">
            <Switch
              id="is_digital"
              v-model:checked="formData.is_digital"
            />
            <Label for="is_digital" class="cursor-pointer">Digital Product</Label>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="delivery_method">Delivery Method</Label>
            <select
              id="delivery_method"
              v-model="formData.delivery_method"
              class="w-full rounded-md border px-3 py-2 bg-background mt-2"
            >
              <option value="">Select delivery method</option>
              <option value="Digital Download">Digital Download</option>
              <option value="Email">Email</option>
              <option value="CDN">CDN</option>
              <option value="Physical">Physical</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-center space-x-2">
            <Switch
              id="affiliate_enabled"
              v-model:checked="formData.affiliate_enabled"
            />
            <Label for="affiliate_enabled" class="cursor-pointer">Enable Affiliate Program</Label>
          </div>
          <div v-if="formData.affiliate_enabled">
            <Label for="affiliate_commission">Affiliate Commission (%)</Label>
            <Input
              id="affiliate_commission"
              v-model="formData.affiliate_commission"
              type="number"
              min="0"
              max="100"
              step="0.01"
              placeholder="10"
              class="mt-2"
            />
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Maintenance Info -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Wrench class="h-5 w-5" />
          Maintenance & Updates
        </CardTitle>
        <CardDescription>
          Information about project maintenance and updates
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="estimated_build_time">Estimated Build Time</Label>
            <Input
              id="estimated_build_time"
              v-model="formData.estimated_build_time"
              placeholder="e.g., 2-4 weeks, 1 month"
              maxlength="50"
              class="mt-2"
            />
          </div>

          <div>
            <Label for="language">Primary Language</Label>
            <Input
              id="language"
              v-model="formData.language"
              placeholder="en"
              maxlength="10"
              class="mt-2"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="update_frequency">Update Frequency</Label>
            <select
              id="update_frequency"
              v-model="formData.update_frequency"
              class="w-full rounded-md border px-3 py-2 bg-background mt-2"
            >
              <option value="">Select frequency</option>
              <option value="Daily">Daily</option>
              <option value="Weekly">Weekly</option>
              <option value="Monthly">Monthly</option>
              <option value="Quarterly">Quarterly</option>
              <option value="As Needed">As Needed</option>
              <option value="No Updates">No Updates</option>
            </select>
          </div>

          <div class="flex items-center space-x-2 pt-8">
            <Switch
              id="is_maintained"
              v-model:checked="formData.is_maintained"
            />
            <Label for="is_maintained" class="cursor-pointer">Project is actively maintained</Label>
          </div>
        </div>

        <div>
          <Label for="maintenance_status">Maintenance Status</Label>
          <select
            id="maintenance_status"
            v-model="formData.maintenance_status"
            class="w-full rounded-md border px-3 py-2 bg-background mt-2"
          >
            <option value="">Select status</option>
            <option value="Active">Active</option>
            <option value="Maintenance Mode">Maintenance Mode</option>
            <option value="Deprecated">Deprecated</option>
            <option value="Archived">Archived</option>
          </select>
        </div>

        <div>
          <Label for="deprecation_notice">Deprecation Notice</Label>
          <Textarea
            id="deprecation_notice"
            v-model="formData.deprecation_notice"
            placeholder="Notice if this project is deprecated"
            rows="3"
            class="mt-2"
          />
        </div>

        <div>
          <Label for="migration_guide_url">Migration Guide URL</Label>
          <Input
            id="migration_guide_url"
            v-model="formData.migration_guide_url"
            type="url"
            placeholder="https://example.com/migration-guide"
            class="mt-2"
          />
        </div>
      </CardContent>
    </Card>

    <!-- Featured Image -->
    <Card>
      <CardHeader>
        <CardTitle>Featured Image</CardTitle>
        <CardDescription>
          Upload a preview image for your project (max 2MB)
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="imagePreview" class="mb-4 relative">
          <img 
            :src="imagePreview" 
            alt="Preview" 
            class="w-full h-48 object-cover rounded-lg"
          />
          <Button
            type="button"
            variant="destructive"
            size="sm"
            class="absolute top-2 right-2"
            @click="removeImage"
          >
            <X class="h-4 w-4" />
          </Button>
        </div>
        <div>
          <Label for="featured_image" class="cursor-pointer">
            <Upload class="h-4 w-4 inline mr-2" />
            Choose Image
          </Label>
          <Input
            id="featured_image"
            type="file"
            accept="image/*"
            @change="handleImageUpload"
            class="mt-2"
          />
          <p v-if="errors.featured_image" class="text-red-500 text-sm mt-1">
            {{ errors.featured_image }}
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Actions -->
    <div class="flex justify-end gap-3">
      <Button
        type="button"
        variant="outline"
        @click="handleCancel"
        :disabled="loading"
      >
        Cancel
      </Button>
      <Button
        class="bg-[#a855f7cc] hover:bg-[#a855f7]"
        type="submit"
        :disabled="loading"
      >
        <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
        <Save v-else class="h-4 w-4 mr-2" />
        {{ submitLabel }}
      </Button>
    </div>
  </form>
</template>

