<!--
  XpHistoryChart Component
  Purpose: Visual representation of XP progress over time using SVG
  Props: xpLogs (Array of XP log objects)
-->
<template>
  <div class="xp-history-chart bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">XP History</h3>
    
    <div v-if="chartData.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      No XP history available
    </div>

    <div v-else class="relative">
      <!-- SVG Chart -->
      <svg
        :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
        class="w-full h-64"
        preserveAspectRatio="xMidYMid meet"
      >
        <!-- Grid lines -->
        <defs>
          <linearGradient id="xpGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.3" />
            <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:0" />
          </linearGradient>
        </defs>

        <!-- Area fill -->
        <path
          :d="areaPath"
          fill="url(#xpGradient)"
          class="xp-area"
        />

        <!-- Line -->
        <path
          :d="linePath"
          fill="none"
          stroke="#3b82f6"
          stroke-width="2"
          class="xp-line"
        />

        <!-- Data points -->
        <circle
          v-for="(point, index) in chartData"
          :key="index"
          :cx="point.x"
          :cy="point.y"
          r="4"
          fill="#3b82f6"
          class="xp-point"
          @mouseenter="showTooltip(point, $event)"
          @mouseleave="hideTooltip"
        />
      </svg>

      <!-- Tooltip -->
      <div
        v-if="tooltip.visible"
        class="absolute bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg px-3 py-2 shadow-lg z-10 pointer-events-none"
        :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
      >
        <div class="font-semibold">+{{ tooltip.amount }} XP</div>
        <div class="text-gray-300">{{ tooltip.date }}</div>
      </div>

      <!-- X-axis labels -->
      <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
        <span>{{ formatAxisDate(chartData[0]?.date) }}</span>
        <span>{{ formatAxisDate(chartData[chartData.length - 1]?.date) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  xpLogs: {
    type: Array,
    default: () => []
  }
})

const chartWidth = 800
const chartHeight = 200
const padding = 40

const tooltip = ref({
  visible: false,
  x: 0,
  y: 0,
  amount: 0,
  date: ''
})

const chartData = computed(() => {
  if (!props.xpLogs || props.xpLogs.length === 0) return []

  // Group by date and sum XP
  const dailyXp = {}
  props.xpLogs.forEach(log => {
    const date = new Date(log.created_at).toDateString()
    if (!dailyXp[date]) {
      dailyXp[date] = { date: log.created_at, total: 0 }
    }
    dailyXp[date].total += log.xp_amount || 0
  })

  const sorted = Object.values(dailyXp)
    .sort((a, b) => new Date(a.date) - new Date(b.date))

  // Calculate cumulative XP
  let cumulative = 0
  const data = sorted.map((item, index) => {
    cumulative += item.total
    return {
      date: item.date,
      xp: cumulative,
      amount: item.total,
      x: padding + (index / (sorted.length - 1 || 1)) * (chartWidth - padding * 2),
      y: chartHeight - padding - (cumulative / maxXp.value) * (chartHeight - padding * 2)
    }
  })

  return data
})

const maxXp = computed(() => {
  if (chartData.value.length === 0) return 100
  return Math.max(...chartData.value.map(d => d.xp), 100)
})

const linePath = computed(() => {
  if (chartData.value.length === 0) return ''
  
  const points = chartData.value.map(d => `${d.x},${d.y}`).join(' ')
  return `M ${points}`
})

const areaPath = computed(() => {
  if (chartData.value.length === 0) return ''
  
  const points = chartData.value.map(d => `${d.x},${d.y}`).join(' ')
  const first = chartData.value[0]
  const last = chartData.value[chartData.value.length - 1]
  return `M ${first.x},${chartHeight - padding} ${points} L ${last.x},${chartHeight - padding} Z`
})

const showTooltip = (point, event) => {
  const rect = event.target.closest('svg').getBoundingClientRect()
  tooltip.value = {
    visible: true,
    x: event.clientX - rect.left - 50,
    y: event.clientY - rect.top - 60,
    amount: point.amount,
    date: formatTooltipDate(point.date)
  }
}

const hideTooltip = () => {
  tooltip.value.visible = false
}

const formatAxisDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const formatTooltipDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

watch(() => props.xpLogs, () => {
  // Trigger animation on data change
  const paths = document.querySelectorAll('.xp-line, .xp-area')
  paths.forEach(path => {
    if (path instanceof SVGPathElement) {
      const length = path.getTotalLength()
      path.style.strokeDasharray = `${length}`
      path.style.strokeDashoffset = `${length}`
      path.style.transition = 'stroke-dashoffset 2s ease-out'
      setTimeout(() => {
        path.style.strokeDashoffset = '0'
      }, 100)
    }
  })
}, { immediate: true })
</script>

<style scoped>
.xp-line {
  transition: stroke-dashoffset 2s ease-out;
}

.xp-point {
  cursor: pointer;
  transition: r 0.2s ease;
}

.xp-point:hover {
  r: 6;
  fill: #2563eb;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .xp-line {
    transition: none !important;
  }
  
  .xp-point {
    transition: none !important;
  }
}
</style>

