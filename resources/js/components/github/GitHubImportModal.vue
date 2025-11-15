<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import GitHubConnectButton from './GitHubConnectButton.vue'
import RepositorySelector from './RepositorySelector.vue'
import ImportPreview from './ImportPreview.vue'
import { X } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'
import { useRouter } from 'vue-router'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:open', 'imported'])

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const isOpen = ref(props.open)
const step = ref('connect') // 'connect' | 'select' | 'preview'
const isConnected = ref(false)
const selectedRepo = ref(null)
const repoData = ref(null)
const loading = ref(false)

watch(() => props.open, (newValue) => {
  isOpen.value = newValue
  if (newValue) {
    checkConnection()
  }
})

watch(isOpen, (newValue) => {
  emit('update:open', newValue)
  if (!newValue) {
    resetModal()
  }
})

const checkConnection = async () => {
  try {
    const response = await axios.get('/api/v1/github/status', authStore.config)
    if (response.data.status === 'success') {
      isConnected.value = response.data.data.connected && response.data.data.valid
      if (isConnected.value) {
        step.value = 'select'
      }
    }
  } catch (error) {
    console.error('Error checking GitHub connection:', error)
    isConnected.value = false
  }
}

const handleConnected = () => {
  isConnected.value = true
  step.value = 'select'
}

const handleRepositorySelected = async (repo) => {
  selectedRepo.value = repo
  loading.value = true
  
  try {
    const [owner, repoName] = repo.full_name.split('/')
    const response = await axios.get(
      `/api/v1/github/repositories/${owner}/${repoName}`,
      authStore.config
    )
    
    if (response.data.status === 'success') {
      repoData.value = response.data.data
      step.value = 'preview'
    }
  } catch (error) {
    console.error('Error fetching repository:', error)
    toast({
      title: "Error",
      description: error.response?.data?.message || "Failed to fetch repository details",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const handleImport = async (formData) => {
  loading.value = true
  
  try {
    const [owner, repoName] = selectedRepo.value.full_name.split('/')
    const response = await axios.post(
      '/api/v1/github/import',
      {
        owner,
        repo: repoName,
        ...formData
      },
      authStore.config
    )
    
    if (response.data.status === 'success') {
      toast({
        title: "Success",
        description: "Project imported successfully!",
      })
      
      emit('imported', response.data.data)
      isOpen.value = false
      router.push(`/projects/${response.data.data.slug}/edit`)
    }
  } catch (error) {
    console.error('Error importing repository:', error)
    toast({
      title: "Error",
      description: error.response?.data?.message || "Failed to import repository",
      variant: "destructive"
    })
  } finally {
    loading.value = false
  }
}

const handleBack = () => {
  if (step.value === 'preview') {
    step.value = 'select'
    repoData.value = null
  } else if (step.value === 'select') {
    step.value = 'connect'
  }
}

const resetModal = () => {
  step.value = 'connect'
  selectedRepo.value = null
  repoData.value = null
  isConnected.value = false
}

onMounted(() => {
  if (props.open) {
    checkConnection()
  }
})
</script>

<template>
  <Dialog :open="isOpen" @update:open="isOpen = $event">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="flex items-center justify-between">
          <span>Import from GitHub</span>
          <button
            @click="isOpen = false"
            class="rounded-sm opacity-70 hover:opacity-100 transition-opacity"
          >
            <X class="h-4 w-4" />
          </button>
        </DialogTitle>
        <DialogDescription>
          Import your GitHub repositories as projects
        </DialogDescription>
      </DialogHeader>

      <div class="mt-6">
        <!-- Step 1: Connect GitHub -->
        <div v-if="step === 'connect'">
          <GitHubConnectButton @connected="handleConnected" />
        </div>

        <!-- Step 2: Select Repository -->
        <div v-else-if="step === 'select'">
          <RepositorySelector
            :loading="loading"
            @selected="handleRepositorySelected"
            @back="handleBack"
          />
        </div>

        <!-- Step 3: Preview and Import -->
        <div v-else-if="step === 'preview' && repoData">
          <ImportPreview
            :repository="selectedRepo"
            :repo-data="repoData"
            :loading="loading"
            @import="handleImport"
            @back="handleBack"
          />
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

