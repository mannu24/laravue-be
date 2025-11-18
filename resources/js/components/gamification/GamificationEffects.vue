<!--
  GamificationEffects Component
  Purpose: Reusable animation effects for gamification (confetti, bursts, glows)
  Props: type ('confetti' | 'burst' | 'glow' | 'sparkle'), duration (number), intensity (number)
-->
<template>
  <div
    ref="containerRef"
    class="gamification-effects"
    :class="effectClass"
    aria-hidden="true"
  ></div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { useAnimation } from '@/composables/useAnimation'

const props = defineProps({
  type: {
    type: String,
    default: 'confetti',
    validator: (value) => ['confetti', 'burst', 'glow', 'sparkle'].includes(value)
  },
  duration: {
    type: Number,
    default: 3000
  },
  intensity: {
    type: Number,
    default: 50
  },
  trigger: {
    type: Boolean,
    default: false
  }
})

const containerRef = ref(null)
const { playConfetti, playParticleBurst, playGlowEffect, playSparkle } = useAnimation()

const effectClass = computed(() => {
  return `effect-${props.type}`
})

const playEffect = () => {
  if (!containerRef.value) return

  switch (props.type) {
    case 'confetti':
      playConfetti(containerRef.value, props.duration, props.intensity)
      break
    case 'burst':
      playParticleBurst(containerRef.value, props.intensity)
      break
    case 'glow':
      playGlowEffect(containerRef.value, '#3b82f6', props.duration)
      break
    case 'sparkle':
      playSparkle(containerRef.value, props.intensity)
      break
  }
}

watch(() => props.trigger, (newVal) => {
  if (newVal) {
    playEffect()
  }
})

onMounted(() => {
  if (props.trigger) {
    playEffect()
  }
})

onUnmounted(() => {
  // Cleanup if needed
})
</script>

<style scoped>
.gamification-effects {
  position: absolute;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 1;
}

@media (prefers-reduced-motion: reduce) {
  .gamification-effects {
    display: none;
  }
}
</style>

