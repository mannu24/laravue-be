<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePortfolioStore } from '@/stores/portfolioStore'
import { useThemeStore } from '@/stores/theme'
import { Button } from '@/components/ui/button'
import { ArrowLeft, Monitor, Smartphone, Tablet, ExternalLink } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const portfolioStore = usePortfolioStore()
const themeStore = useThemeStore()

const previewWidth = ref('100%')
const iframeKey = ref(0)

onMounted(async () => {
  await portfolioStore.fetchPortfolio()
  if (!portfolioStore.hasPortfolio) {
    router.push('/portfolio')
  }
})

const setDevice = (device) => {
  if (device === 'mobile') previewWidth.value = '375px'
  else if (device === 'tablet') previewWidth.value = '768px'
  else previewWidth.value = '100%'
  iframeKey.value++
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <Button variant="ghost" size="icon" @click="router.push('/portfolio')">
          <ArrowLeft class="w-5 h-5" />
        </Button>
        <h1 class="text-xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Preview</h1>
      </div>
      <div class="flex items-center gap-2">
        <div class="flex border rounded-lg overflow-hidden" :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-200'">
          <button @click="setDevice('desktop')" class="px-3 py-1.5 text-sm" :class="previewWidth === '100%' ? 'bg-vue text-white' : (themeStore.isDark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-900')">
            <Monitor class="w-4 h-4" />
          </button>
          <button @click="setDevice('tablet')" class="px-3 py-1.5 text-sm" :class="previewWidth === '768px' ? 'bg-vue text-white' : (themeStore.isDark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-900')">
            <Tablet class="w-4 h-4" />
          </button>
          <button @click="setDevice('mobile')" class="px-3 py-1.5 text-sm" :class="previewWidth === '375px' ? 'bg-vue text-white' : (themeStore.isDark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-900')">
            <Smartphone class="w-4 h-4" />
          </button>
        </div>
        <Button v-if="portfolioStore.portfolio?.is_published" variant="outline" size="sm"
          @click="window.open(portfolioStore.subdomainUrl, '_blank')"
          :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
          <ExternalLink class="w-4 h-4 mr-1" /> Live Site
        </Button>
      </div>
    </div>

    <div class="flex justify-center">
      <div class="border rounded-xl overflow-hidden shadow-2xl transition-all duration-300"
        :class="themeStore.isDark ? 'border-gray-700 bg-gray-900' : 'border-gray-200 bg-white'"
        :style="{ width: previewWidth, maxWidth: '100%' }">
        <iframe
          :key="iframeKey"
          :src="'/portfolio-preview?token=' + encodeURIComponent(authStore.token || '')"
          class="w-full border-0"
          style="height: 80vh;"
        ></iframe>
      </div>
    </div>
  </div>
</template>
