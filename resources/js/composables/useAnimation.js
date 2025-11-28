/**
 * useAnimation Composable
 * Provides animation control functions for gamification effects
 */
import { ref } from 'vue'

export function useAnimation() {
  const isAnimating = ref(false)

  /**
   * Play confetti animation
   * @param {HTMLElement} container - Container element for confetti
   * @param {number} duration - Animation duration in ms
   * @param {number} intensity - Number of particles
   */
  const playConfetti = (container, duration = 3000, intensity = 50) => {
    if (!container) return

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return

    isAnimating.value = true
    const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#ef4444']
    const particles = []

    for (let i = 0; i < intensity; i++) {
      const particle = document.createElement('div')
      particle.className = 'confetti-particle'
      particle.style.cssText = `
        position: absolute;
        width: 8px;
        height: 8px;
        background: ${colors[Math.floor(Math.random() * colors.length)]};
        left: ${Math.random() * 100}%;
        top: -10px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
      `
      container.appendChild(particle)
      particles.push(particle)

      const angle = Math.random() * Math.PI * 2
      const velocity = 2 + Math.random() * 3
      const xVelocity = Math.cos(angle) * velocity
      let yVelocity = Math.sin(angle) * velocity + 2

      let x = Math.random() * container.offsetWidth
      let y = -10
      let rotation = 0

      const animate = () => {
        x += xVelocity
        y += yVelocity
        rotation += 5
        yVelocity += 0.1 // gravity

        particle.style.transform = `translate(${x}px, ${y}px) rotate(${rotation}deg)`
        particle.style.opacity = Math.max(0, 1 - (y / container.offsetHeight))

        if (y < container.offsetHeight + 50) {
          requestAnimationFrame(animate)
        } else {
          particle.remove()
        }
      }

      setTimeout(() => animate(), i * 10)
    }

    setTimeout(() => {
      isAnimating.value = false
    }, duration)
  }

  /**
   * Play particle burst animation
   * @param {HTMLElement} element - Element to burst from
   * @param {number} count - Number of particles
   */
  const playParticleBurst = (element, count = 20) => {
    if (!element) return

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return

    const rect = element.getBoundingClientRect()
    const centerX = rect.left + rect.width / 2
    const centerY = rect.top + rect.height / 2
    const colors = ['#fbbf24', '#f59e0b', '#d97706']

    for (let i = 0; i < count; i++) {
      const particle = document.createElement('div')
      particle.className = 'burst-particle'
      const color = colors[Math.floor(Math.random() * colors.length)]
      particle.style.cssText = `
        position: fixed;
        width: 6px;
        height: 6px;
        background: ${color};
        left: ${centerX}px;
        top: ${centerY}px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
      `
      document.body.appendChild(particle)

      const angle = (Math.PI * 2 * i) / count
      const distance = 50 + Math.random() * 50
      const x = Math.cos(angle) * distance
      const y = Math.sin(angle) * distance

      particle.animate(
        [
          { transform: 'translate(0, 0) scale(1)', opacity: 1 },
          { transform: `translate(${x}px, ${y}px) scale(0)`, opacity: 0 }
        ],
        {
          duration: 800,
          easing: 'ease-out'
        }
      ).onfinish = () => particle.remove()
    }
  }

  /**
   * Play glow effect
   * @param {HTMLElement} element - Element to glow
   * @param {string} color - Glow color
   * @param {number} duration - Duration in ms
   */
  const playGlowEffect = (element, color = '#3b82f6', duration = 2000) => {
    if (!element) return

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return

    const originalBoxShadow = element.style.boxShadow
    element.style.transition = 'box-shadow 0.3s ease'
    element.style.boxShadow = `0 0 20px ${color}, 0 0 40px ${color}, 0 0 60px ${color}`

    setTimeout(() => {
      element.style.boxShadow = originalBoxShadow
      setTimeout(() => {
        element.style.transition = ''
      }, 300)
    }, duration)
  }

  /**
   * Play level-up sequence
   * @param {HTMLElement} container - Container element
   */
  const playLevelUpSequence = (container) => {
    if (!container) return
    playConfetti(container, 3000, 60)
  }

  /**
   * Play badge unlock sequence
   * @param {HTMLElement} badgeElement - Badge element
   */
  const playBadgeUnlockSequence = (badgeElement) => {
    if (!badgeElement) return
    playParticleBurst(badgeElement, 30)
    playGlowEffect(badgeElement, '#fbbf24', 2000)
  }

  /**
   * Play sparkle animation
   * @param {HTMLElement} element - Element to sparkle
   * @param {number} count - Number of sparkles
   */
  const playSparkle = (element, count = 5) => {
    if (!element) return

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return

    const rect = element.getBoundingClientRect()

    for (let i = 0; i < count; i++) {
      const sparkle = document.createElement('div')
      sparkle.className = 'sparkle'
      sparkle.style.cssText = `
        position: fixed;
        width: 4px;
        height: 4px;
        background: white;
        left: ${rect.left + Math.random() * rect.width}px;
        top: ${rect.top + Math.random() * rect.height}px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        box-shadow: 0 0 6px white;
      `
      document.body.appendChild(sparkle)

      const x = (Math.random() - 0.5) * 100
      const y = (Math.random() - 0.5) * 100

      sparkle.animate(
        [
          { transform: 'translate(0, 0) scale(1)', opacity: 1 },
          { transform: `translate(${x}px, ${y}px) scale(0)`, opacity: 0 }
        ],
        {
          duration: 600,
          easing: 'ease-out'
        }
      ).onfinish = () => sparkle.remove()
    }
  }

  return {
    isAnimating,
    playConfetti,
    playParticleBurst,
    playGlowEffect,
    playLevelUpSequence,
    playBadgeUnlockSequence,
    playSparkle
  }
}

