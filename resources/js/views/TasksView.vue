<!--
  TasksView
  Purpose: Shows daily and weekly tasks with completion functionality
-->
<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Tasks</h1>
      <p class="mt-2 text-gray-600 dark:text-white/80">
        Complete daily and weekly tasks to earn XP and level up
      </p>
    </div>
    
    <div v-if="globalDataStore.loading" class="text-center py-12">
      <LoadingSpinner />
    </div>
    <div v-else>
      <TaskList
        :daily-tasks="dailyTasks"
        :weekly-tasks="weeklyTasks"
        :show-filter="true"
      />
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useGlobalDataStore } from '@/stores/globalData'
import TaskList from '@/components/gamification/TaskList.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const globalDataStore = useGlobalDataStore()

const dailyTasks = computed(() => globalDataStore.dailyTasks)
const weeklyTasks = computed(() => globalDataStore.weeklyTasks)
</script>