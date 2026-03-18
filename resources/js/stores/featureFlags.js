/**
 * Feature Flags Store
 * Fetches public feature flags from /api/v1/app-config
 * and makes them available across the entire Vue app.
 *
 * To enable a feature: set AI_QNA_ENABLED=true in .env and redeploy.
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useFeatureFlagsStore = defineStore('featureFlags', () => {
    const features = ref({
        ai_qna: false,
    })
    const loaded = ref(false)

    const isAiQnaEnabled = computed(() => features.value.ai_qna === true)

    const fetchFlags = async () => {
        if (loaded.value) return
        try {
            const { data } = await axios.get('/api/v1/app-config')
            features.value = { ...features.value, ...data.features }
        } catch {
            // Fall back to all-disabled defaults silently
        } finally {
            loaded.value = true
        }
    }

    return { features, loaded, isAiQnaEnabled, fetchFlags }
})
