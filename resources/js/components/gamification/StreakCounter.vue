<!--
  StreakCounter Component
  Purpose: Visually displays streak days, next milestone, and confetti animation on milestones
  Props: streakDays (number)
  Emits: none (pure display component)
-->
<template>
  <div
    class="bg-gradient-to-br from-orange-500 to-red-600 rounded-lg p-6 text-white shadow-lg"
    data-test="streak-counter"
  >
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-sm font-medium text-orange-100 mb-1">Current Streak</h3>
        <div class="flex items-baseline gap-2">
          <span class="text-4xl font-bold">{{ streakDays }}</span>
          <span class="text-lg text-orange-100">days</span>
        </div>
      </div>
      <div class="text-right">
        <div class="text-2xl">🔥</div>
      </div>
    </div>
    <div class="pt-4 border-t border-orange-400/30">
      <div class="text-sm text-orange-100">
        Next milestone: {{ nextMilestone }} days
      </div>
      <div class="mt-2 w-full bg-orange-400/30 rounded-full h-2">
        <div
          class="h-full bg-white rounded-full transition-all duration-500"
          :style="{ width: `${milestoneProgress}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue'

const props = defineProps({
  streakDays: {
    type: Number,
    default: 0
  }
})

const milestones = [7, 14, 30, 60, 100, 365]

const nextMilestone = computed(() => {
  const current = props.streakDays
  return milestones.find(m => m > current) || milestones[milestones.length - 1]
})

const milestoneProgress = computed(() => {
  const current = props.streakDays
  const previous = milestones.filter(m => m <= current).pop() || 0
  const next = nextMilestone.value
  const range = next - previous
  const progress = ((current - previous) / range) * 100
  return Math.min(100, Math.max(0, progress))
})

// Watch for milestone achievements
watch(
  () => props.streakDays,
  (newStreak, oldStreak) => {
    const achieved = milestones.find(m => m > oldStreak && m <= newStreak)
    if (achieved) {
      // TODO: Trigger confetti animation
      // This would typically use a confetti library or custom animation
    }
  }
)

onMounted(() => {
  // Check if current streak is a milestone
  if (milestones.includes(props.streakDays)) {
    // TODO: Trigger confetti animation
  }
})
</script>

<style scoped>
/* Confetti animation styles can be added here */
</style>

