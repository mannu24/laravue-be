<template>
  <Card
    ref="badgeRef"
    class="relative cursor-pointer transition-all duration-300 p-6 rounded-2xl border hover:-translate-y-1 text-nowrap"
    :class="{
      // Unlocked badge colors based on type (more vibrant)
      'bg-gradient-to-br from-blue-500/15 to-cyan-500/15 dark:from-blue-500/20 dark:to-cyan-500/20 border-blue-500/30 dark:border-blue-500/30 hover:border-blue-500/50 dark:hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-500/30 dark:hover:shadow-blue-500/30': badge.awarded_at && badgeType === 'quality',
      'bg-gradient-to-br from-green-500/15 to-emerald-500/15 dark:from-green-500/20 dark:to-emerald-500/20 border-green-500/30 dark:border-green-500/30 hover:border-green-500/50 dark:hover:border-green-500/50 hover:shadow-lg hover:shadow-green-500/30 dark:hover:shadow-green-500/30': badge.awarded_at && badgeType === 'contribution',
      'bg-gradient-to-br from-red-500/15 to-rose-500/15 dark:from-red-500/20 dark:to-rose-500/20 border-red-500/30 dark:border-red-500/30 hover:border-red-500/50 dark:hover:border-red-500/50 hover:shadow-lg hover:shadow-red-500/30 dark:hover:shadow-red-500/30': badge.awarded_at && badgeType === 'rare',
      'bg-gradient-to-br from-purple-500/15 to-violet-500/15 dark:from-purple-500/20 dark:to-violet-500/20 border-purple-500/30 dark:border-purple-500/30 hover:border-purple-500/50 dark:hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/30 dark:hover:shadow-purple-500/30': badge.awarded_at && badgeType === 'event',
      'bg-gradient-to-br from-yellow-500/15 to-amber-500/15 dark:from-yellow-500/20 dark:to-amber-500/20 border-yellow-500/30 dark:border-yellow-500/30 hover:border-yellow-500/50 dark:hover:border-yellow-500/50 hover:shadow-lg hover:shadow-yellow-500/30 dark:hover:shadow-yellow-500/30': badge.awarded_at && badgeType === 'participation',
      'bg-gradient-to-br from-green-700/15 to-emerald-800/15 dark:from-green-700/20 dark:to-emerald-800/20 border-green-700/30 dark:border-green-700/30 hover:border-green-700/50 dark:hover:border-green-700/50 hover:shadow-lg hover:shadow-green-700/30 dark:hover:shadow-green-700/30': badge.awarded_at && badgeType === 'consistency',
      'bg-gradient-to-br from-blue-500/15 to-purple-500/15 border-blue-500/30 dark:border-blue-500/30 hover:border-blue-500/50 dark:hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-500/30 dark:hover:shadow-blue-500/30': badge.awarded_at && !['quality', 'contribution', 'rare', 'event', 'participation', 'consistency'].includes(badgeType),
      // Locked badge colors based on type (softer)
      'bg-gradient-to-br from-blue-100/50 to-cyan-100/50 dark:from-blue-900/20 dark:to-cyan-900/20 border-blue-300/40 dark:border-blue-700/30 opacity-70 hover:opacity-90 hover:border-blue-400/50 dark:hover:border-blue-600/50': !badge.awarded_at && badgeType === 'quality',
      'bg-gradient-to-br from-green-100/50 to-emerald-100/50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-300/40 dark:border-green-700/30 opacity-70 hover:opacity-90 hover:border-green-400/50 dark:hover:border-green-600/50': !badge.awarded_at && badgeType === 'contribution',
      'bg-gradient-to-br from-red-100/50 to-rose-100/50 dark:from-red-900/20 dark:to-rose-900/20 border-red-300/40 dark:border-red-700/30 opacity-70 hover:opacity-90 hover:border-red-400/50 dark:hover:border-red-600/50': !badge.awarded_at && badgeType === 'rare',
      'bg-gradient-to-br from-purple-100/50 to-violet-100/50 dark:from-purple-900/20 dark:to-violet-900/20 border-purple-300/40 dark:border-purple-700/30 opacity-70 hover:opacity-90 hover:border-purple-400/50 dark:hover:border-purple-600/50': !badge.awarded_at && badgeType === 'event',
      'bg-gradient-to-br from-yellow-100/50 to-amber-100/50 dark:from-yellow-900/20 dark:to-amber-900/20 border-yellow-300/40 dark:border-yellow-700/30 opacity-70 hover:opacity-90 hover:border-yellow-400/50 dark:hover:border-yellow-600/50': !badge.awarded_at && badgeType === 'participation',
      'bg-gradient-to-br from-green-700/30 to-emerald-800/30 dark:from-green-900/20 dark:to-emerald-900/20 border-green-700/40 dark:border-green-800/30 opacity-70 hover:opacity-90 hover:border-green-700/50 dark:hover:border-green-800/50': !badge.awarded_at && badgeType === 'consistency',
      'bg-gradient-to-br from-gray-200/80 to-gray-300/80 dark:from-gray-700/50 dark:to-gray-800/50 border-gray-300 dark:border-gray-600/30 opacity-70 hover:opacity-90 hover:border-gray-400 dark:hover:border-gray-600/50': !badge.awarded_at && !['quality', 'contribution', 'rare', 'event', 'participation', 'consistency'].includes(badgeType)
    }"
  >
    <!-- Badge Card -->
    <div class="relative w-full h-full group">
      <!-- Badge Icon Container -->
      <div class="relative mx-auto mb-3">
        <!-- When badge is NOT awarded - Show lock icon with circular border and faded overlay based on badge type -->
        <div
          v-if="!badge.awarded_at"
          class="relative w-20 h-20 mx-auto rounded-full flex items-center justify-center transition-all duration-300 border-2 shadow-lg group-hover:scale-110"
          :class="{
            'bg-gradient-to-br from-blue-200/60 to-cyan-200/60 dark:from-blue-800/40 dark:to-cyan-800/40 border-blue-400/50 dark:border-blue-600/50': badgeType === 'quality',
            'bg-gradient-to-br from-green-200/60 to-emerald-200/60 dark:from-green-800/40 dark:to-emerald-800/40 border-green-400/50 dark:border-green-600/50': badgeType === 'contribution',
            'bg-gradient-to-br from-red-200/60 to-rose-200/60 dark:from-red-800/40 dark:to-rose-800/40 border-red-400/50 dark:border-red-600/50': badgeType === 'rare',
            'bg-gradient-to-br from-purple-200/60 to-violet-200/60 dark:from-purple-800/40 dark:to-violet-800/40 border-purple-400/50 dark:border-purple-600/50': badgeType === 'event',
            'bg-gradient-to-br from-yellow-200/60 to-amber-200/60 dark:from-yellow-800/40 dark:to-amber-800/40 border-yellow-400/50 dark:border-yellow-600/50': badgeType === 'participation',
            'bg-gradient-to-br from-green-700/50 to-emerald-800/50 dark:from-green-800/40 dark:to-emerald-900/40 border-green-700/50 dark:border-green-800/50': badgeType === 'consistency',
            'bg-gradient-to-br from-gray-300/70 to-gray-400/70 dark:from-gray-600/50 dark:to-gray-700/50 border-gray-400/60 dark:border-gray-500/50': !['quality', 'contribution', 'rare', 'event', 'participation', 'consistency'].includes(badgeType)
          }"
        >
          <!-- Faded overlay based on badge type -->
          <div 
            class="absolute inset-0 rounded-full backdrop-blur-sm"
            :class="{
              'bg-white/50 dark:bg-blue-900/40': badgeType === 'quality',
              'bg-white/50 dark:bg-green-900/40': badgeType === 'contribution',
              'bg-white/50 dark:bg-red-900/40': badgeType === 'rare',
              'bg-white/50 dark:bg-purple-900/40': badgeType === 'event',
              'bg-white/50 dark:bg-yellow-900/40': badgeType === 'participation',
              'bg-white/50 dark:bg-green-900/50': badgeType === 'consistency',
              'bg-white/60 dark:bg-gray-900/60': !['quality', 'contribution', 'rare', 'event', 'participation', 'consistency'].includes(badgeType)
            }"
          ></div>
          
          <!-- Lock icon with color based on badge type -->
          <svg 
            class="w-10 h-10 relative z-10" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
            :class="{
              'text-blue-500 dark:text-blue-400': badgeType === 'quality',
              'text-green-500 dark:text-green-400': badgeType === 'contribution',
              'text-red-500 dark:text-red-400': badgeType === 'rare',
              'text-purple-500 dark:text-purple-400': badgeType === 'event',
              'text-yellow-500 dark:text-yellow-400': badgeType === 'participation',
              'text-green-700 dark:text-green-600': badgeType === 'consistency',
              'text-gray-500 dark:text-gray-400': !['quality', 'contribution', 'rare', 'event', 'participation', 'consistency'].includes(badgeType)
            }"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        
        <!-- When badge IS awarded - Show image directly with shadow based on badge type -->
        <div
          v-else
          class="relative mx-auto flex items-center justify-center transition-all duration-300 group-hover:scale-110"
        >
          <!-- Badge Image/Icon - Direct display without circle -->
          <img
            :src="badge.icon_path"
            :alt="badge.name"
            class="w-20 h-20 object-contain z-10 transition-transform duration-300 group-hover:scale-110"
            :class="{
              'drop-shadow-[0_8px_16px_rgba(59,130,246,0.4)] dark:drop-shadow-[0_8px_16px_rgba(59,130,246,0.5)]': badgeType === 'quality',
              'drop-shadow-[0_8px_16px_rgba(34,197,94,0.4)] dark:drop-shadow-[0_8px_16px_rgba(34,197,94,0.5)]': badgeType === 'contribution',
              'drop-shadow-[0_8px_16px_rgba(239,68,68,0.4)] dark:drop-shadow-[0_8px_16px_rgba(239,68,68,0.5)]': badgeType === 'rare',
              'drop-shadow-[0_8px_16px_rgba(168,85,247,0.4)] dark:drop-shadow-[0_8px_16px_rgba(168,85,247,0.5)]': badgeType === 'event',
              'drop-shadow-[0_8px_16px_rgba(234,179,8,0.4)] dark:drop-shadow-[0_8px_16px_rgba(234,179,8,0.5)]': badgeType === 'participation',
              'drop-shadow-[0_8px_16px_rgba(21,128,61,0.4)] dark:drop-shadow-[0_8px_16px_rgba(21,128,61,0.5)]': badgeType === 'consistency'
            }"
            @error="handleImageError"
          />
        </div>
        
        <!-- Badge Type Indicator -->
        <div
          v-if="badgeType === 'rare' || badgeType === 'event'"
          class="absolute -top-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center shadow-lg border-[1px] border-slate-700 dark:order-slate-300 z-20"
          :class="{
            'bg-gradient-to-br from-red-400 to-rose-500': badgeType === 'rare',
            'bg-gradient-to-br from-purple-400 to-violet-500': badgeType === 'event'
          }"
          :title="badgeType === 'rare' ? 'Rare Badge' : 'Event Badge'"
        >
          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </div>
        <!-- Quality Badge Indicator -->
        <div
          v-if="badgeType === 'quality'"
          class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 flex items-center justify-center shadow-lg border-[1px] border-slate-700 dark:order-slate-300 z-20"
          title="Quality Badge"
        >
          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <!-- Contribution Badge Indicator -->
        <div
          v-if="badgeType === 'contribution'"
          class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg border-[1px] border-slate-700 dark:order-slate-300 z-20"
          title="Contribution Badge"
        >
          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
          </svg>
        </div>
        <!-- Participation Badge Indicator -->
        <div
          v-if="badgeType === 'participation'"
          class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-lg border-[1px] border-slate-700 dark:order-slate-300 z-20"
          title="Participation Badge"
        >
          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
          </svg>
        </div>
        <!-- Consistency Badge Indicator -->
        <div
          v-if="badgeType === 'consistency'"
          class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-br from-green-700 to-emerald-800 flex items-center justify-center shadow-lg border-[1px] border-slate-700 dark:order-slate-300 z-20"
          title="Consistency Badge"
        >
          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
      
      <!-- Badge Info -->
      <div class="text-center">
        <h4
          class="font-semibold text-sm mb-1 transition-colors"
          :class="badge.awarded_at ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-500'"
        >
          {{ badge.name }}
        </h4>
        <p
          class="text-xs mb-2 overflow-hidden line-clamp-2"
          :class="badge.awarded_at ? 'text-gray-600 dark:text-white/70' : 'text-gray-500/70 dark:text-gray-500/70'"
        >
          {{ badge.description }}
        </p>
        <div v-if="badge.awarded_at" class="flex items-center justify-center gap-1">
          <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Earned
          </span>
        </div>
        <div v-else class="text-xs text-red-600 dark:text-red-400 font-medium">
          Locked
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Card } from '../ui/card'
const props = defineProps({
  badge: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      name: '',
      slug: '',
      description: '',
      icon_path: null,
      awarded_at: null,
      type: 'contribution' // Can be: quality, contribution, rare, event
    })
  }
})

const emit = defineEmits(['click'])

const badgeRef = ref(null)
const imageError = ref(false)

const badgeType = computed(() => {
  return props.badge.type || 'contribution'
})

//   emit('click', props.badge)
// }

const handleImageError = () => {
  imageError.value = true
}

</script>

