<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePortfolioStore } from '@/stores/portfolioStore'
import { useThemeStore } from '@/stores/theme'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { toast } from '@/components/ui/toast'
import {
  Check, Loader2, Tag, ArrowLeft, Star, Sparkles, CreditCard, X, Zap, Shield
} from 'lucide-vue-next'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const portfolioStore = usePortfolioStore()
const themeStore = useThemeStore()

const plans = ref([])
const loading = ref(true)
const processingPlan = ref(null)

// Coupon state
const couponCode = ref('')
const couponResult = ref(null)
const validatingCoupon = ref(false)
const selectedPlanForCoupon = ref(null)

// Checkout modal
const showCheckout = ref(false)
const checkoutPlan = ref(null)

onMounted(async () => {
  try {
    const res = await axios.get('/api/v1/portfolio/plans')
    if (res.data.status === 'success') plans.value = res.data.data
  } catch (e) {
    toast({ title: 'Error', description: 'Failed to load plans', variant: 'destructive' })
  } finally {
    loading.value = false
  }
  await portfolioStore.fetchSubscription()
})

const openCheckout = (plan) => {
  checkoutPlan.value = plan
  selectedPlanForCoupon.value = plan.slug
  couponCode.value = ''
  couponResult.value = null
  showCheckout.value = true
}

const validateCoupon = async () => {
  if (!couponCode.value.trim() || !selectedPlanForCoupon.value) return
  validatingCoupon.value = true
  try {
    const res = await axios.post('/api/v1/portfolio/coupons/validate', {
      code: couponCode.value.trim(),
      plan_slug: selectedPlanForCoupon.value,
    }, authStore.config)
    couponResult.value = res.data.data
  } catch (e) {
    couponResult.value = { valid: false, error: e.response?.data?.message || 'Invalid coupon' }
  } finally {
    validatingCoupon.value = false
  }
}

const clearCoupon = () => {
  couponCode.value = ''
  couponResult.value = null
}

const finalPrice = computed(() => {
  if (!checkoutPlan.value) return 0
  if (couponResult.value?.valid) return couponResult.value.final_amount
  return parseFloat(checkoutPlan.value.price)
})

const subscribe = async () => {
  if (!authStore.isAuthenticated) { router.push('/login'); return }
  if (!checkoutPlan.value) return

  processingPlan.value = checkoutPlan.value.slug
  try {
    const res = await axios.post('/api/v1/portfolio/orders', {
      plan_slug: checkoutPlan.value.slug,
      coupon_code: couponResult.value?.valid ? couponCode.value.trim() : null,
    }, authStore.config)

    const orderData = res.data.data

    if (orderData.free_order) {
      toast({ title: 'Subscription activated!', description: 'Your portfolio plan is now active.' })
      showCheckout.value = false
      await portfolioStore.fetchSubscription()
      router.push('/portfolio')
      return
    }

    openRazorpayCheckout(orderData)
  } catch (e) {
    toast({ title: 'Error', description: e.response?.data?.message || 'Failed to create order', variant: 'destructive' })
    processingPlan.value = null
  }
}

const openRazorpayCheckout = (orderData) => {
  if (typeof window.Razorpay === 'undefined') {
    toast({ title: 'Error', description: 'Payment gateway not loaded. Please refresh.', variant: 'destructive' })
    processingPlan.value = null
    return
  }

  const rzp = new window.Razorpay({
    key: orderData.razorpay_key_id,
    amount: orderData.amount_in_paise,
    currency: 'INR',
    name: 'LaraVue',
    description: `${checkoutPlan.value.name} Plan`,
    order_id: orderData.razorpay_order_id,
    prefill: { name: authStore.user?.name || '', email: authStore.user?.email || '' },
    theme: { color: '#41B883' },
    handler: async (response) => {
      try {
        await axios.post('/api/v1/portfolio/orders/verify', {
          order_id: orderData.order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature,
        }, authStore.config)
        toast({ title: 'Payment successful!', description: 'Your subscription is now active.' })
        showCheckout.value = false
        await portfolioStore.fetchSubscription()
        router.push('/portfolio')
      } catch (e) {
        toast({ title: 'Verification failed', description: 'Contact support if payment was deducted.', variant: 'destructive' })
      } finally {
        processingPlan.value = null
      }
    },
    modal: { ondismiss: () => { processingPlan.value = null } },
  })
  rzp.open()
}

const planStyles = {
  starter: { gradient: 'from-blue-500 to-cyan-500', icon: CreditCard, ring: '' },
  pro: { gradient: 'from-vue to-emerald-500', icon: Star, ring: 'ring-2 ring-vue' },
  annual: { gradient: 'from-purple-500 to-indigo-500', icon: Sparkles, ring: '' },
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-8">
    <!-- Header -->
    <div class="text-center mb-10">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold mb-4"
        :class="themeStore.isDark ? 'bg-vue/10 text-vue' : 'bg-vue/10 text-vue'">
        <Sparkles class="w-3 h-3" /> Launch Offer
      </div>
      <h1 class="text-3xl md:text-4xl font-bold mb-3" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
        Simple, transparent pricing
      </h1>
      <p class="text-lg max-w-2xl mx-auto" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
        Get your portfolio live today. Use code <span class="font-mono font-bold text-vue">WELCOME40</span> for 40% off!
      </p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <Loader2 class="w-8 h-8 animate-spin text-vue" />
    </div>

    <!-- Plans Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div v-for="plan in plans" :key="plan.slug"
        class="relative rounded-2xl border p-6 transition-all duration-300 hover:-translate-y-1"
        :class="[
          themeStore.isDark ? 'bg-gray-800/80 border-gray-700' : 'bg-white border-gray-200',
          planStyles[plan.slug]?.ring || '',
          plan.slug === 'pro' ? 'md:-translate-y-2' : ''
        ]">
        <!-- Popular badge -->
        <div v-if="plan.slug === 'pro'" class="absolute -top-3 left-1/2 -translate-x-1/2">
          <span class="bg-vue text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg shadow-vue/30">
            Most Popular
          </span>
        </div>

        <!-- Plan icon -->
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center mb-4"
          :class="planStyles[plan.slug]?.gradient">
          <component :is="planStyles[plan.slug]?.icon || CreditCard" class="w-6 h-6 text-white" />
        </div>

        <h3 class="text-xl font-bold mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ plan.name }}</h3>

        <div class="flex items-baseline gap-1 mb-4">
          <span class="text-3xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">₹{{ plan.price }}</span>
          <span class="text-sm" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">/ {{ plan.duration_months }}mo</span>
        </div>

        <ul class="space-y-2.5 mb-6">
          <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2 text-sm"
            :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            <Check class="w-4 h-4 text-vue flex-shrink-0 mt-0.5" />
            {{ feature }}
          </li>
        </ul>

        <Button @click="openCheckout(plan)" :disabled="processingPlan === plan.slug"
          class="w-full rounded-xl py-5"
          :class="plan.slug === 'pro'
            ? 'bg-vue hover:bg-vue/90 text-white shadow-lg shadow-vue/20'
            : (themeStore.isDark ? 'bg-gray-700 hover:bg-gray-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-900')">
          <Loader2 v-if="processingPlan === plan.slug" class="w-4 h-4 mr-2 animate-spin" />
          Get {{ plan.name }}
        </Button>
      </div>
    </div>

    <!-- Checkout Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showCheckout && checkoutPlan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
          @click.self="showCheckout = false; processingPlan = null">
          <div class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden"
            :class="themeStore.isDark ? 'bg-gray-800' : 'bg-white'">
            <!-- Header -->
            <div class="p-6 pb-4">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Checkout</h2>
                <button @click="showCheckout = false; processingPlan = null"
                  class="p-1 rounded-lg" :class="themeStore.isDark ? 'hover:bg-gray-700 text-gray-400' : 'hover:bg-gray-100 text-gray-500'">
                  <X class="w-5 h-5" />
                </button>
              </div>

              <!-- Plan summary -->
              <div class="rounded-xl p-4 mb-4" :class="themeStore.isDark ? 'bg-gray-700/50' : 'bg-gray-50'">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-semibold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ checkoutPlan.name }} Plan</p>
                    <p class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">{{ checkoutPlan.duration_months }} months</p>
                  </div>
                  <p class="text-xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">₹{{ checkoutPlan.price }}</p>
                </div>
              </div>

              <!-- Coupon Input -->
              <div class="mb-4">
                <label class="text-sm font-medium mb-2 block" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                  <Tag class="w-4 h-4 inline mr-1" /> Have a coupon code?
                </label>
                <div class="flex gap-2">
                  <div class="flex-1 relative">
                    <Input v-model="couponCode" placeholder="Enter code" class="uppercase pr-8"
                      :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : ''"
                      :disabled="couponResult?.valid" />
                    <button v-if="couponResult?.valid" @click="clearCoupon"
                      class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                  <Button v-if="!couponResult?.valid" variant="outline" @click="validateCoupon"
                    :disabled="validatingCoupon || !couponCode.trim()"
                    :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                    <Loader2 v-if="validatingCoupon" class="w-4 h-4 animate-spin" />
                    <span v-else>Apply</span>
                  </Button>
                </div>
                <div class="mt-1.5 h-5">
                  <span v-if="couponResult?.valid" class="text-xs text-green-500 flex items-center gap-1">
                    <Check class="w-3 h-3" />
                    {{ couponResult.discount_type === 'percentage' ? couponResult.discount_value + '% off' : '₹' + couponResult.discount_amount + ' off' }}
                    — you save ₹{{ couponResult.discount_amount }}!
                  </span>
                  <span v-else-if="couponResult && !couponResult.valid" class="text-xs text-red-500">
                    {{ couponResult.error }}
                  </span>
                </div>
              </div>

              <!-- Price breakdown -->
              <div class="space-y-2 mb-6 text-sm">
                <div class="flex justify-between" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                  <span>Subtotal</span>
                  <span>₹{{ checkoutPlan.price }}</span>
                </div>
                <div v-if="couponResult?.valid" class="flex justify-between text-green-500">
                  <span>Discount ({{ couponCode.toUpperCase() }})</span>
                  <span>-₹{{ couponResult.discount_amount }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t font-bold text-lg"
                  :class="themeStore.isDark ? 'border-gray-600 text-white' : 'border-gray-200 text-gray-900'">
                  <span>Total</span>
                  <span>₹{{ finalPrice }}</span>
                </div>
              </div>

              <!-- Pay button -->
              <Button @click="subscribe" :disabled="processingPlan" class="w-full bg-vue hover:bg-vue/90 text-white py-5 rounded-xl text-base font-semibold">
                <Loader2 v-if="processingPlan" class="w-5 h-5 mr-2 animate-spin" />
                <Shield v-else class="w-5 h-5 mr-2" />
                {{ processingPlan ? 'Processing...' : (finalPrice <= 0 ? 'Activate Free' : 'Pay ₹' + finalPrice) }}
              </Button>

              <p class="text-xs text-center mt-3" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">
                🔒 Secured by Razorpay. One-time payment, no auto-renewal.
              </p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <div class="text-center">
      <Button variant="ghost" @click="router.push('/portfolio')" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
        <ArrowLeft class="w-4 h-4 mr-1" /> Back to Portfolio
      </Button>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
