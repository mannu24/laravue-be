<!--
  AchievementPopups Component
  Purpose: Shows level up and badge unlock modals sequentially
  Uses: useAchievementPopups composable
-->
<template>
  <div>
    <!-- Level Up Modal -->
    <LevelUpModal
      v-if="currentAchievement?.type === 'level_up'"
      :visible="isShowing"
      :level="currentAchievement.level"
      @close="closeCurrent"
    />

    <!-- Badge Unlock Modal -->
    <AchievementUnlockModal
      v-if="currentAchievement?.type === 'badge_unlocked'"
      :visible="isShowing"
      :badge="currentAchievement.badge"
      @close="closeCurrent"
    />
  </div>
</template>

<script setup>
import { useAchievementPopups } from '@/composables/useAchievementPopups'
import LevelUpModal from './LevelUpModal.vue'
import AchievementUnlockModal from './AchievementUnlockModal.vue'

const {
  currentAchievement,
  isShowing,
  closeCurrent,
} = useAchievementPopups()
</script>

