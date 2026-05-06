<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePortfolioStore } from '@/stores/portfolioStore'
import { useThemeStore } from '@/stores/theme'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { toast } from '@/components/ui/toast'
import {
  Globe, Edit3, Eye, EyeOff, ExternalLink, Rocket, Layout, CreditCard,
  CheckCircle, AlertTriangle, Loader2, Sparkles, ArrowRight, Clock, Zap
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const portfolioStore = usePortfolioStore()
const themeStore = useThemeStore()

const subdomain = ref('')
const subdomainStatus = ref(null)
const checkingSubdomain = ref(false)
const creating = ref(false)

const hasPortfolio = computed(() => portfolioStore.hasPortfolio)
const portfolio = computed(() => portfolioStore.portfolio)
const subscription = computed(() => portfolioStore.subscription)

onMounted(async () => {
  await portfolioStore.fetchPortfolio()
  if (!portfolioStore.hasPortfolio) {
    await portfolioStore.fetchPlans()
  }
  await portfolioStore.fetchSubscription()
})

let checkTimeout = null
const checkSubdomain = () => {
  clearTimeout(checkTimeout)
  subdomainStatus.value = null
  if (subdomain.value.length < 3) return
  checkingSubdomain.value = true
  checkTimeout = setTimeout(async () => {
    subdomainStatus.value = await portfolioStore.checkSubdomain(subdomain.value.toLowerCase())
    checkingSubdomain.value = false
  }, 400)
}

const createPortfolio = async () => {
  if (!subdomainStatus.value?.available) return
  creating.value = true
  try {
    await portfolioStore.createPortfolio(subdomain.value.toLowerCase())
    toast({ title: 'Portfolio created!', description: 'Start building your portfolio.' })
  } catch (e) {
    toast({ title: 'Error', description: e.response?.data?.message || 'Failed to create portfolio', variant: 'destructive' })
  } finally {
    creating.value = false
  }
}

const togglePublish = async () => {
  try {
    if (portfolio.value.is_published) {
      await portfolioStore.unpublish()
      toast({ title: 'Portfolio unpublished' })
    } else {
      await portfolioStore.publish()
      toast({ title: 'Portfolio published!', description: 'Your portfolio is now live.' })
    }
  } catch (e) {
    toast({ title: 'Error', description: e.response?.data?.message || 'Action failed', variant: 'destructive' })
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-8 space-y-8">
    <!-- Loading -->
    <div v-if="portfolioStore.loading" class="flex justify-center py-20">
      <Loader2 class="w-8 h-8 animate-spin text-vue" />
    </div>

    <!-- ═══════ No Portfolio — Create ═══════ -->
    <template v-else-if="!hasPortfolio">
      <!-- Hero -->
      <div class="relative overflow-hidden rounded-2xl border p-8 md:p-12"
        :class="themeStore.isDark
          ? 'bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 border-gray-700'
          : 'bg-gradient-to-br from-white via-vue/5 to-white border-gray-200'">
        <div class="absolute top-0 right-0 w-64 h-64 bg-vue/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold mb-4"
            :class="themeStore.isDark ? 'bg-vue/10 text-vue' : 'bg-vue/10 text-vue'">
            <Sparkles class="w-3 h-3" /> New Feature
          </div>
          <h1 class="text-3xl md:text-4xl font-bold mb-3" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Build Your Portfolio Website
          </h1>
          <p class="text-lg mb-8 max-w-2xl" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
            Create a stunning portfolio at <strong class="text-vue">yourname.laravue.in</strong> — pick a template, add your work, and go live in minutes.
          </p>

          <!-- Subdomain Picker -->
          <div class="max-w-lg">
            <label class="text-sm font-medium mb-2 block" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
              Choose your URL
            </label>
            <div class="flex items-center gap-2">
              <div class="flex-1 flex items-center rounded-xl border overflow-hidden"
                :class="themeStore.isDark ? 'bg-gray-800 border-gray-600' : 'bg-white border-gray-300'">
                <span class="px-3 text-sm" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">https://</span>
                <input
                  v-model="subdomain"
                  @input="checkSubdomain"
                  placeholder="yourname"
                  class="flex-1 py-3 bg-transparent outline-none text-sm font-medium"
                  :class="themeStore.isDark ? 'text-white placeholder-gray-500' : 'text-gray-900 placeholder-gray-400'"
                />
                <span class="px-3 text-sm whitespace-nowrap" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">.laravue.in</span>
              </div>
              <Button
                @click="createPortfolio"
                :disabled="!subdomainStatus?.available || creating"
                class="bg-vue hover:bg-vue/90 text-white px-6 py-3 rounded-xl"
              >
                <Loader2 v-if="creating" class="w-4 h-4 animate-spin" />
                <Rocket v-else class="w-4 h-4" />
              </Button>
            </div>
            <div class="mt-2 h-5">
              <span v-if="checkingSubdomain" class="text-xs text-gray-500 flex items-center gap-1">
                <Loader2 class="w-3 h-3 animate-spin" /> Checking...
              </span>
              <span v-else-if="subdomainStatus?.available" class="text-xs text-green-500 flex items-center gap-1">
                <CheckCircle class="w-3 h-3" /> Available!
              </span>
              <span v-else-if="subdomainStatus" class="text-xs text-red-500 flex items-center gap-1">
                <AlertTriangle class="w-3 h-3" /> {{ subdomainStatus.reason }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Features -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-for="feature in [
          { icon: '🎨', title: 'Beautiful Templates', desc: 'Choose from professionally designed templates that make your work shine.' },
          { icon: '⚡', title: 'Live in Minutes', desc: 'Fill in your details, pick a template, and publish — no coding required.' },
          { icon: '🌐', title: 'Custom Domain', desc: 'Use your own domain or get a free yourname.laravue.in subdomain.' },
        ]" :key="feature.title"
          class="rounded-xl border p-5"
          :class="themeStore.isDark ? 'bg-gray-800/50 border-gray-700' : 'bg-white border-gray-200'">
          <span class="text-2xl">{{ feature.icon }}</span>
          <h3 class="font-semibold mt-3 mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ feature.title }}</h3>
          <p class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">{{ feature.desc }}</p>
        </div>
      </div>

      <!-- Plans CTA -->
      <div class="rounded-2xl border p-6 text-center"
        :class="themeStore.isDark ? 'bg-gradient-to-r from-vue/5 to-laravel/5 border-gray-700' : 'bg-gradient-to-r from-vue/5 to-laravel/5 border-vue/20'">
        <p class="text-lg font-semibold mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
          🎉 Launch Offer: <span class="text-vue">40% off</span> for new users!
        </p>
        <p class="text-sm mb-4" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
          Use code <span class="font-mono font-bold text-vue">WELCOME40</span> at checkout. Plans start at just ₹179.
        </p>
        <Button @click="router.push('/portfolio/plans')" class="bg-vue hover:bg-vue/90 text-white">
          View Plans <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>
    </template>

    <!-- ═══════ Has Portfolio — Dashboard ═══════ -->
    <template v-else>
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Your Portfolio</h1>
          <p class="text-sm mt-1" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
            <a :href="portfolioStore.subdomainUrl" target="_blank" class="text-vue hover:underline">
              {{ portfolio.subdomain }}.laravue.in
            </a>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 rounded-full text-xs font-semibold"
            :class="portfolio.is_published
              ? 'bg-green-500/10 text-green-500 border border-green-500/20'
              : 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20'">
            {{ portfolio.is_published ? '● Live' : '○ Draft' }}
          </span>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl border p-4 cursor-pointer hover:-translate-y-0.5 transition-all"
          :class="themeStore.isDark ? 'bg-gray-800/50 border-gray-700 hover:border-vue/40' : 'bg-white border-gray-200 hover:border-vue/40'"
          @click="router.push('/portfolio/editor')">
          <div class="flex items-center justify-between mb-2">
            <Edit3 class="w-5 h-5 text-vue" />
            <ArrowRight class="w-4 h-4" :class="themeStore.isDark ? 'text-gray-600' : 'text-gray-300'" />
          </div>
          <p class="font-semibold text-sm" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Edit Content</p>
          <p class="text-xs mt-0.5" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">Profile, skills, projects</p>
        </div>

        <div class="rounded-xl border p-4 cursor-pointer hover:-translate-y-0.5 transition-all"
          :class="themeStore.isDark ? 'bg-gray-800/50 border-gray-700 hover:border-vue/40' : 'bg-white border-gray-200 hover:border-vue/40'"
          @click="router.push('/portfolio/templates')">
          <div class="flex items-center justify-between mb-2">
            <Layout class="w-5 h-5 text-purple-500" />
            <ArrowRight class="w-4 h-4" :class="themeStore.isDark ? 'text-gray-600' : 'text-gray-300'" />
          </div>
          <p class="font-semibold text-sm" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Template</p>
          <p class="text-xs mt-0.5 capitalize" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">{{ portfolio.template_slug }}</p>
        </div>

        <div class="rounded-xl border p-4 cursor-pointer hover:-translate-y-0.5 transition-all"
          :class="themeStore.isDark ? 'bg-gray-800/50 border-gray-700 hover:border-vue/40' : 'bg-white border-gray-200 hover:border-vue/40'"
          @click="router.push('/portfolio/preview')">
          <div class="flex items-center justify-between mb-2">
            <Eye class="w-5 h-5 text-blue-500" />
            <ArrowRight class="w-4 h-4" :class="themeStore.isDark ? 'text-gray-600' : 'text-gray-300'" />
          </div>
          <p class="font-semibold text-sm" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Preview</p>
          <p class="text-xs mt-0.5" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">See how it looks</p>
        </div>

        <div class="rounded-xl border p-4 cursor-pointer hover:-translate-y-0.5 transition-all"
          :class="themeStore.isDark ? 'bg-gray-800/50 border-gray-700 hover:border-vue/40' : 'bg-white border-gray-200 hover:border-vue/40'"
          @click="router.push('/portfolio/plans')">
          <div class="flex items-center justify-between mb-2">
            <CreditCard class="w-5 h-5 text-orange-500" />
            <ArrowRight class="w-4 h-4" :class="themeStore.isDark ? 'text-gray-600' : 'text-gray-300'" />
          </div>
          <p class="font-semibold text-sm" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            {{ subscription ? subscription.plan?.name + ' Plan' : 'Get a Plan' }}
          </p>
          <p class="text-xs mt-0.5" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">
            {{ subscription ? 'Active' : 'Required to publish' }}
          </p>
        </div>
      </div>

      <!-- Actions Bar -->
      <div class="flex flex-wrap gap-3">
        <Button @click="togglePublish" :variant="portfolio.is_published ? 'outline' : 'default'"
          :class="portfolio.is_published ? (themeStore.isDark ? 'border-gray-600 text-gray-300' : '') : 'bg-vue hover:bg-vue/90 text-white'">
          <component :is="portfolio.is_published ? EyeOff : Eye" class="w-4 h-4 mr-2" />
          {{ portfolio.is_published ? 'Unpublish' : 'Publish Portfolio' }}
        </Button>
        <Button v-if="portfolio.is_published" variant="outline"
          @click="window.open(portfolioStore.subdomainUrl, '_blank')"
          :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
          <ExternalLink class="w-4 h-4 mr-2" /> View Live Site
        </Button>
      </div>

      <!-- No subscription warning -->
      <div v-if="!subscription" class="rounded-xl border-2 border-dashed p-5 text-center"
        :class="themeStore.isDark ? 'border-yellow-500/30 bg-yellow-500/5' : 'border-yellow-400/40 bg-yellow-50'">
        <Zap class="w-8 h-8 text-yellow-500 mx-auto mb-2" />
        <p class="font-semibold mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
          Subscribe to publish your portfolio
        </p>
        <p class="text-sm mb-3" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
          Use code <span class="font-mono font-bold text-vue">WELCOME40</span> for 40% off your first plan!
        </p>
        <Button @click="router.push('/portfolio/plans')" class="bg-vue hover:bg-vue/90 text-white">
          View Plans <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>
    </template>
  </div>
</template>
