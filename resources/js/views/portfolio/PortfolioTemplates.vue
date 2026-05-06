<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePortfolioStore } from '@/stores/portfolioStore'
import { useThemeStore } from '@/stores/theme'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { toast } from '@/components/ui/toast'
import { Check, ArrowLeft, Layout, Eye, Loader2, Lock } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const portfolioStore = usePortfolioStore()
const themeStore = useThemeStore()

const switching = ref(null)

onMounted(async () => {
  await portfolioStore.fetchTemplates()
  await portfolioStore.fetchPortfolio()
})

const currentTemplate = computed(() => portfolioStore.portfolio?.template_slug)

const selectTemplate = async (slug) => {
  if (!portfolioStore.hasPortfolio) {
    toast({ title: 'Create a portfolio first', variant: 'destructive' })
    return
  }
  if (slug === currentTemplate.value) return

  switching.value = slug
  try {
    await portfolioStore.updateTemplate(slug)
    toast({ title: 'Template updated!', description: `Switched to ${slug} template.` })
  } catch (e) {
    toast({ title: 'Error', description: e.response?.data?.message || 'Failed to switch template', variant: 'destructive' })
  } finally {
    switching.value = null
  }
}

const previewDescriptions = {
  minimal: 'Clean, white-space-heavy, typography-focused. A single-page scroll layout that lets your work speak for itself.',
  developer: 'Dark theme with terminal-inspired aesthetics. Monospace fonts, code-block styling, and a hacker-friendly vibe.',
}
</script>

<template>
  <div class="max-w-4xl mx-auto py-8">
    <div class="flex items-center gap-4 mb-8">
      <Button variant="ghost" size="icon" @click="router.push('/portfolio')">
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <div>
        <h1 class="text-3xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
          Templates
        </h1>
        <p class="text-sm mt-1" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
          Choose a look for your portfolio
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <Card v-for="template in portfolioStore.templates" :key="template.slug"
        class="overflow-hidden transition-all duration-300 hover:-translate-y-1"
        :class="[
          themeStore.isDark ? 'bg-gray-800 border-gray-700' : '',
          currentTemplate === template.slug ? 'ring-2 ring-vue' : ''
        ]">
        <!-- Preview area -->
        <div class="h-48 flex items-center justify-center relative"
          :class="template.slug === 'developer' ? 'bg-[#0a0a0f]' : 'bg-gray-50'">
          <div v-if="template.slug === 'minimal'" class="text-center px-6">
            <div class="w-12 h-12 rounded-full bg-gray-200 mx-auto mb-2"></div>
            <div class="h-3 w-32 bg-gray-300 rounded mx-auto mb-1"></div>
            <div class="h-2 w-24 bg-gray-200 rounded mx-auto"></div>
            <div class="flex gap-2 justify-center mt-3">
              <div class="h-2 w-16 bg-gray-200 rounded"></div>
              <div class="h-2 w-16 bg-gray-200 rounded"></div>
            </div>
          </div>
          <div v-else-if="template.slug === 'developer'" class="text-center px-6">
            <div class="bg-[#161b22] border border-[#30363d] rounded-lg p-3 max-w-[200px] mx-auto">
              <div class="flex gap-1 mb-2">
                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
              </div>
              <div class="h-2 w-20 bg-[#41B883] rounded mb-1"></div>
              <div class="h-2 w-28 bg-[#30363d] rounded mb-1"></div>
              <div class="h-2 w-16 bg-[#30363d] rounded"></div>
            </div>
          </div>

          <!-- Current badge -->
          <div v-if="currentTemplate === template.slug"
            class="absolute top-3 right-3 bg-vue text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
            <Check class="w-3 h-3" /> Active
          </div>

          <!-- Premium badge -->
          <div v-if="template.is_premium"
            class="absolute top-3 left-3 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
            <Lock class="w-3 h-3" /> Pro
          </div>
        </div>

        <CardContent class="pt-4 space-y-3">
          <h3 class="font-semibold text-lg" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            {{ template.name }}
          </h3>
          <p class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
            {{ template.description || previewDescriptions[template.slug] || '' }}
          </p>
          <div class="flex gap-2">
            <Button
              @click="selectTemplate(template.slug)"
              :disabled="currentTemplate === template.slug || switching === template.slug"
              :variant="currentTemplate === template.slug ? 'secondary' : 'default'"
              class="flex-1"
              :class="currentTemplate !== template.slug ? 'bg-vue hover:bg-vue/90 text-white' : ''"
            >
              <Loader2 v-if="switching === template.slug" class="w-4 h-4 mr-2 animate-spin" />
              {{ currentTemplate === template.slug ? 'Current' : 'Use Template' }}
            </Button>
            <Button variant="outline" @click="router.push('/portfolio/preview')"
              :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
              <Eye class="w-4 h-4" />
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
