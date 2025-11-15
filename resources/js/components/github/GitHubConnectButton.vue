<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Github, Loader2, CheckCircle2 } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const emit = defineEmits(['connected'])

const authStore = useAuthStore()
const themeStore = useThemeStore()

const connecting = ref(false)
const isConnected = ref(false)

const connectGitHub = async () => {
  connecting.value = true
  
  try {
    const response = await axios.get('/api/v1/github/authorize', authStore.config)
    
    if (response.data.status === 'success') {
      // Open GitHub OAuth in new window
      const width = 600
      const height = 700
      const left = (window.screen.width - width) / 2
      const top = (window.screen.height - height) / 2
      
      const popup = window.open(
        response.data.data.authorization_url,
        'GitHub Authorization',
        `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`
      )
      
      // Poll for window close or message
      const pollTimer = setInterval(() => {
        if (popup.closed) {
          clearInterval(pollTimer)
          connecting.value = false
          checkConnection()
        }
      }, 500)
      
      // Listen for message from popup (if using postMessage)
      window.addEventListener('message', handleMessage)
      
      function handleMessage(event) {
        if (event.data.type === 'github-connected') {
          clearInterval(pollTimer)
          if (popup && !popup.closed) {
            popup.close()
          }
          window.removeEventListener('message', handleMessage)
          connecting.value = false
          isConnected.value = true
          emit('connected')
          toast({
            title: "Success",
            description: "GitHub account connected successfully!",
          })
        } else if (event.data.type === 'github-error') {
          clearInterval(pollTimer)
          if (popup && !popup.closed) {
            popup.close()
          }
          window.removeEventListener('message', handleMessage)
          connecting.value = false
          toast({
            title: "Error",
            description: event.data.message || "Failed to connect GitHub account",
            variant: "destructive"
          })
        }
      }
    }
  } catch (error) {
    console.error('Error connecting GitHub:', error)
    toast({
      title: "Error",
      description: error.response?.data?.message || "Failed to connect GitHub account",
      variant: "destructive"
    })
    connecting.value = false
  }
}

const checkConnection = async () => {
  try {
    const response = await axios.get('/api/v1/github/status', authStore.config)
    if (response.data.status === 'success') {
      isConnected.value = response.data.data.connected && response.data.data.valid
      if (isConnected.value) {
        emit('connected')
      }
    }
  } catch (error) {
    console.error('Error checking connection:', error)
  }
}

// Check on mount
import { onMounted } from 'vue'
onMounted(() => {
  checkConnection()
})
</script>

<template>
  <Card :class="['border',
    themeStore.isDark 
      ? 'bg-gray-900 border-gray-800' 
      : 'bg-white border-gray-200'
  ]">
    <CardContent class="p-8 text-center">
      <div class="mb-6">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
          :class="themeStore.isDark ? 'bg-gray-800' : 'bg-gray-100'"
        >
          <Github class="h-8 w-8" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'" />
        </div>
        
        <h3 class="text-xl font-semibold mb-2"
          :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
        >
          Connect Your GitHub Account
        </h3>
        <p class="text-sm"
          :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'"
        >
          Connect your GitHub account to import repositories and automatically create projects
        </p>
      </div>

      <div v-if="isConnected" class="flex items-center justify-center gap-2 text-vue mb-4">
        <CheckCircle2 class="h-5 w-5" />
        <span class="text-sm font-medium">GitHub Connected</span>
      </div>

      <Button
        v-if="!isConnected"
        @click="connectGitHub"
        :disabled="connecting"
        class="w-full bg-gray-900 hover:bg-gray-800 text-white"
      >
        <Loader2 v-if="connecting" class="h-4 w-4 mr-2 animate-spin" />
        <Github v-else class="h-4 w-4 mr-2" />
        {{ connecting ? 'Connecting...' : 'Connect with GitHub' }}
      </Button>
    </CardContent>
  </Card>
</template>

