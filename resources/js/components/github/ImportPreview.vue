<script setup>
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, Github, Globe, Code2, Loader2, CheckCircle2, Star } from 'lucide-vue-next'
import { useThemeStore } from '@/stores/theme'
import CategorySelector from '@/components/projects/CategorySelector.vue'

const props = defineProps({
  repository: {
    type: Object,
    required: true
  },
  repoData: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['import', 'back'])

const themeStore = useThemeStore()

const formData = ref({
  title: '',
  description: '',
  short_description: '',
  long_description: '',
  demo_url: '',
  project_type: 'open',
  is_sellable: false,
  category_id: null,
  features: [],
  tags: [],
})

const initializeFormData = () => {
  const repo = props.repoData.repository
  const parsed = props.repoData.parsed || {}
  
  formData.value = {
    title: parsed.title || repo.name || '',
    description: parsed.description || repo.description || '',
    short_description: parsed.short_description || (repo.description ? repo.description.substring(0, 500) : ''),
    long_description: parsed.description || '',
    demo_url: repo.homepage || (parsed.links?.find(l => l.text?.toLowerCase().includes('demo'))?.url) || '',
    project_type: 'open',
    is_sellable: false,
    category_id: null,
    features: parsed.features || [],
    tags: props.repoData.topics || [],
  }
}

const handleSubmit = () => {
  emit('import', formData.value)
}

onMounted(() => {
  initializeFormData()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Repository Info -->
    <Card :class="['border',
      themeStore.isDark 
        ? 'bg-gray-900 border-gray-800' 
        : 'bg-white border-gray-200'
    ]">
      <CardHeader>
        <CardTitle class="flex items-center gap-2"
          :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
        >
          <Github class="h-5 w-5" />
          {{ repository.full_name }}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="flex items-center gap-4 text-sm"
          :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'"
        >
          <div class="flex items-center gap-1">
            <Code2 class="h-4 w-4" />
            <span>{{ repoData.repository.language || 'N/A' }}</span>
          </div>
          <div class="flex items-center gap-1">
            <Star class="h-4 w-4" />
            <span>{{ repoData.repository.stargazers_count || 0 }} stars</span>
          </div>
          <div v-if="repoData.repository.homepage" class="flex items-center gap-1">
            <Globe class="h-4 w-4" />
            <a :href="repoData.repository.homepage" target="_blank" class="hover:underline">
              Live Demo
            </a>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Form -->
    <div class="space-y-4">
      <!-- Title -->
      <div>
        <Label for="title">Project Title *</Label>
        <Input
          id="title"
          v-model="formData.title"
          class="mt-2"
          placeholder="Enter project title"
          required
        />
      </div>

      <!-- Short Description -->
      <div>
        <Label for="short_description">Short Description</Label>
        <Input
          id="short_description"
          v-model="formData.short_description"
          class="mt-2"
          placeholder="Brief description (max 500 chars)"
          maxlength="500"
        />
      </div>

      <!-- Description -->
      <div>
        <Label for="description">Description *</Label>
        <Textarea
          id="description"
          v-model="formData.description"
          class="mt-2"
          rows="4"
          placeholder="Project description"
          required
        />
      </div>

      <!-- Demo URL -->
      <div>
        <Label for="demo_url">Demo URL</Label>
        <Input
          id="demo_url"
          v-model="formData.demo_url"
          type="url"
          class="mt-2"
          placeholder="https://example.com"
        />
      </div>

      <!-- Category -->
      <div>
        <Label>Category</Label>
        <CategorySelector
          v-model="formData.category_id"
          class="mt-2"
        />
      </div>

      <!-- Technologies -->
      <div v-if="repoData.languages && repoData.languages.length > 0">
        <Label>Technologies</Label>
        <div class="flex flex-wrap gap-2 mt-2">
          <Badge
            v-for="lang in repoData.languages"
            :key="lang"
            variant="secondary"
            class="text-sm"
          >
            <Code2 class="h-3 w-3 mr-1" />
            {{ lang }}
          </Badge>
        </div>
      </div>

      <!-- Features Preview -->
      <div v-if="formData.features && formData.features.length > 0">
        <Label>Features (from README)</Label>
        <ul class="list-disc list-inside mt-2 space-y-1"
          :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'"
        >
          <li v-for="(feature, index) in formData.features.slice(0, 5)" :key="index" class="text-sm">
            {{ feature }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between pt-4 border-t"
      :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-200'"
    >
      <Button variant="ghost" @click="$emit('back')" :disabled="loading">
        <ArrowLeft class="h-4 w-4 mr-2" />
        Back
      </Button>
      
      <Button
        @click="handleSubmit"
        :disabled="loading || !formData.title || !formData.description"
        class="bg-vue hover:bg-vue/90"
      >
        <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
        <CheckCircle2 v-else class="h-4 w-4 mr-2" />
        Import Project
      </Button>
    </div>
  </div>
</template>

