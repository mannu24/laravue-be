/**
 * useGamification Composable
 * Helper functions for gamification calculations and formatting
 */

/**
 * Calculate XP percentage progress
 * @param {number} currentXp - Current XP amount
 * @param {number} xpForNext - XP required for next level
 * @returns {number} Percentage (0-100)
 */
export function calculateXpPercentage(currentXp, xpForNext) {
  if (!xpForNext || xpForNext === 0) return 100
  const percent = Math.min(100, Math.round((currentXp / xpForNext) * 100))
  return Math.max(0, percent)
}

/**
 * Check if user leveled up
 * @param {number} currentLevelXp - XP required for current level
 * @param {number} newTotalXp - New total XP after action
 * @param {number} xpRequired - XP required for next level
 * @returns {boolean} True if leveled up
 */
export function isLevelUp(currentLevelXp, newTotalXp, xpRequired) {
  if (!currentLevelXp || !xpRequired) return false
  return newTotalXp >= xpRequired && currentLevelXp < xpRequired
}

/**
 * Format badge object for display
 * @param {object} badge - Badge object
 * @returns {object} Formatted badge
 */
export function formatBadge(badge) {
  if (!badge) return null
  
  return {
    id: badge.id,
    name: badge.name || 'Unknown Badge',
    slug: badge.slug || '',
    description: badge.description || '',
    icon_path: badge.icon_path || null,
    type: badge.type || 'participation',
    xp_reward: badge.xp_reward || 0,
    awarded_at: badge.awarded_at || badge.pivot?.awarded_at || null,
    is_earned: !!badge.awarded_at || !!badge.pivot?.awarded_at
  }
}

/**
 * Group badges by type
 * @param {Array} badges - Array of badge objects
 * @returns {object} Grouped badges by type
 */
export function groupBadges(badges) {
  if (!Array.isArray(badges)) return {}
  
  return badges.reduce((groups, badge) => {
    const type = badge.type || 'participation'
    if (!groups[type]) {
      groups[type] = []
    }
    groups[type].push(formatBadge(badge))
    return groups
  }, {})
}

/**
 * Calculate level progress
 * @param {number} currentXp - Current XP
 * @param {object} currentLevel - Current level object
 * @param {object} nextLevel - Next level object
 * @returns {object} Progress data
 */
export function calculateLevelProgress(currentXp, currentLevel, nextLevel) {
  if (!currentLevel) {
    return {
      xp_current: currentXp,
      xp_required: 0,
      progress_percent: 0
    }
  }
  
  const xpRequired = nextLevel ? nextLevel.xp_required - currentLevel.xp_required : 0
  const xpCurrent = currentXp - (currentLevel.xp_required || 0)
  const progressPercent = xpRequired > 0 
    ? Math.min(100, Math.max(0, Math.round((xpCurrent / xpRequired) * 100)))
    : 100
  
  return {
    xp_current: Math.max(0, xpCurrent),
    xp_required: xpRequired,
    progress_percent: progressPercent
  }
}

/**
 * Get tier color class
 * @param {string} tier - Tier name
 * @returns {string} Tailwind color classes
 */
export function getTierColor(tier) {
  const colors = {
    beginner: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    intermediate: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    advanced: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    expert: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    legend: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
  }
  return colors[tier] || colors.beginner
}

