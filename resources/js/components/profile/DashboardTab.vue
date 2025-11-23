<template>
    <div class="flex flex-col gap-10">
        <Card class="border border-gray-200 dark:border-white/10 shadow-2xl overflow-hidden relative rounded-3xl bg-gradient-to-br from-sky-100 via-blue-50 to-emerald-100 dark:from-sky-500/20 dark:via-blue-900/30 dark:to-emerald-500/20 p-8 sm:p-10 shadow-2xl">
            <!-- Radial Gradient Overlay -->
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.15),_transparent_55%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.35),_transparent_55%)]"></div>
                <div class="relative z-10 flex flex-col gap-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                            Level up your learning journey
                        </h1>
                        <p class="mt-3 text-base text-gray-700 dark:text-white/80">
                            Track your XP, discover new badges, and keep your momentum going with curated tasks.
                        </p>

                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <!-- <button
                            class="inline-flex items-center rounded-full bg-sky-500 dark:bg-sky-400 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:-translate-y-0.5"
                            @click="handleOpenProfile">
                            View Public Profile
                        </button> -->
                        <span class="rounded-full bg-gray-100 dark:bg-slate-900/60 px-4 py-1 text-sm font-semibold text-gray-800 dark:text-white/80">
                            {{ levelName }} • {{ levelProgress }}%
                        </span>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-[1.6fr_1fr]">
                    <!-- Progress Stats -->
                    <div class="rounded-2xl border border-gray-200 dark:border-white/15 bg-white/80 dark:bg-white/10 p-6 backdrop-blur">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600 dark:text-white/70">
                                    Overview
                                </p>
                                <h4 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                                    Your Progress
                                </h4>
                            </div>
                            <button class="text-sm font-semibold text-sky-600 dark:text-sky-200 transition hover:text-sky-700 dark:hover:text-white"
                                @click="handleOpenProfile">
                                View Profile →
                            </button>
                        </div>
                        <div class="relative grid gap-6 sm:grid-cols-2">
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ gamificationSummary.xp_total || 0 }}
                                </div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-white/60">
                                    Total XP
                                </p>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ levelName }}
                                </div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-white/60">
                                    Current Level
                                </p>
                            </div>
                            <div
                                class="h-px w-full bg-gray-300 dark:bg-white/10 sm:absolute sm:left-1/2 sm:top-2 sm:bottom-2 sm:h-auto sm:w-px">
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ gamificationSummary.badges_count || 0 }}
                                </div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-white/60">
                                    Badges
                                </p>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ gamificationSummary.tasks_completed || 0 }}
                                </div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-white/60">
                                    Tasks Done
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- XP Tracker -->
                    <div class="rounded-2xl border border-gray-200 dark:border-white/15 bg-gray-50 dark:bg-slate-900/50 p-6 backdrop-blur">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600 dark:text-white/60">
                                    XP Tracker
                                </p>
                                <h3 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                                    Level Journey
                                </h3>
                            </div>
                            <span class="rounded-full bg-gray-200 dark:bg-slate-800/70 px-3 py-1 text-xs font-semibold text-gray-800 dark:text-white">
                                {{ levelName }}
                            </span>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-between gap-3 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-white/70">
                            <span>Level progress</span>
                            <span>{{ xpSummaryData.xp_current || 0 }} / {{ xpSummaryData.xp_needed_for_next || 100 }}
                                XP</span>
                        </div>
                        <XpProgressBar ref="xpProgressRef" :current-xp="xpSummaryData.xp_current || 0"
                            :xp-for-next="xpSummaryData.xp_needed_for_next || 100" :level-name="levelName"
                            @level-up="handleLevelUp" />

                        <div class="mt-5 grid gap-4 text-sm text-gray-800 dark:text-white/80 sm:grid-cols-2">
                            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900/40 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/60">Next milestone</p>
                                <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                    {{ xpSummaryData.next_level?.name || 'Coming soon' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900/40 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-600 dark:text-white/60">XP remaining
                                </p>
                                <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                    {{ Math.max((xpSummaryData.xp_needed_for_next || 0) - (xpSummaryData.xp_current ||
                                    0), 0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="card in [
                { label: 'Total XP', value: gamificationSummary.xp_total || 0, suffix: '' },
                { label: 'Badges Earned', value: gamificationSummary.badges_count || 0, suffix: '' },
                { label: 'Tasks Completed', value: gamificationSummary.tasks_completed || 0, suffix: '' },
                { label: 'Current Streak', value: gamificationSummary.streak_days || 0, suffix: ' days' }
            ]" :key="card.label"
                class="group cursor-pointer relative overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-card p-5 text-gray-900 dark:text-white shadow transition duration-300 hover:-translate-y-1">
                <div
                    class="pointer-events-none absolute inset-0 bg-gradient-to-br from-sky-100 via-blue-50 to-emerald-100  dark:from-sky-500/20 dark:via-blue-900/30 dark:to-emerald-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600 dark:text-white/60">{{ card.label }}</p>
                    <h2 class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ card.value }}{{ card.suffix }}</h2>
                </div>
            </div>
        </section>
        <section class="flex flex-col gap-6">
            <div class="group relative overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-card p-6 shadow-xl transition duration-300">
                <div class="relative z-10 mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600 dark:text-white/60">
                            Collectibles
                        </p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">Top Badges</h3>
                    </div>
                    <button class="text-sm font-semibold text-sky-600 dark:text-sky-300 transition hover:text-sky-700 dark:hover:text-white"
                        @click="$router.push('/badges')">
                        Browse badges
                    </button>
                </div>
                <div class="relative z-10">
                    <!-- Horizontal Scrollable Badge List on Mobile, Grid on Desktop -->
                    <div v-if="allBadges.length > 0">
                        <BadgeList :badges="allBadges" ui="list" @view-badge="handleViewBadge" />
                    </div>
                    <div v-else class="py-8">
                        <EmptyState
                            icon="Activity"
                            title="No badges collected yet"
                            subtitle="Collect badges to earn XP and level up!"
                            size="default"
                        />
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-card p-6 shadow-xl transition duration-300">
                <div class="relative z-10 mb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600 dark:text-white/60">
                            Daily Focus
                        </p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">Today's Tasks</h3>
                    </div>
                </div>
                <div class="relative z-10">
                    <TaskList 
                        :daily-tasks="dailyTasks" 
                        :weekly-tasks="weeklyTasks"
                        :show-filter="true"
                    />
                </div>
            </div>
        </section>
        <LevelUpModal :visible="showLevelUpModal" :level="newLevel" @close="handleCloseLevelUp" />
        <div v-if="showLevelGlow" class="level-glow-overlay"></div>
    </div>
</template>
<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { useRouter } from 'vue-router'
import { useGlobalDataStore } from '@/stores/globalData'
import XpProgressBar from '@/components/gamification/XpProgressBar.vue'
import TaskList from '@/components/gamification/TaskList.vue'
import LevelUpModal from '@/components/gamification/LevelUpModal.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import BadgeList from '../gamification/BadgeList.vue'

const router = useRouter()
const globalDataStore = useGlobalDataStore()

const xpProgressRef = ref(null)
const showLevelUpModal = ref(false)
const showLevelGlow = ref(false)
const newLevel = ref(null)

const gamificationSummary = computed(() => globalDataStore.gamificationSummary)
const xpSummaryData = computed(() => globalDataStore.xpProgress)
const allBadges = computed(() => globalDataStore.allBadges.slice(0, 9))
const dailyTasks = computed(() => globalDataStore.dailyTasks)
const weeklyTasks = computed(() => globalDataStore.weeklyTasks)
const levelName = computed(() => xpSummaryData.value.current_level?.name || 'Level 1')
const levelProgress = computed(() => {
    if (!xpSummaryData.value.xp_needed_for_next) return 0
    return Math.min(
        100,
        Math.round((xpSummaryData.value.xp_current || 0) / (xpSummaryData.value.xp_needed_for_next || 1) * 100)
    )
})

const handleOpenProfile = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleLevelUp = () => {
    newLevel.value = xpSummaryData.value.current_level || {
        name: 'New Level',
        tier: 'beginner'
    }
    showLevelUpModal.value = true
    showLevelGlow.value = true

    setTimeout(() => {
        if (!showLevelUpModal.value) {
            showLevelGlow.value = false
        }
    }, 3000)
}

const handleCloseLevelUp = () => {
    showLevelUpModal.value = false
    setTimeout(() => {
        showLevelGlow.value = false
    }, 500)
}

const handleViewBadge = (badge) => {
    // Handle both badge object and badge slug string
    const badgeSlug = typeof badge === 'string' ? badge : (badge?.slug || badge?.id)
    if (badgeSlug) {
        router.push(`/badges/${badgeSlug}`)
    }
}
</script>

<style scoped>
/* Custom scrollbar for horizontal badge scroll on mobile */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #475569;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>
