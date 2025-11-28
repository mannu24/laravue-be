<script setup>
import { ref, onMounted, computed } from 'vue'
import { Button } from '@/components/ui/button'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import { useThemeStore } from '@/stores/theme'
import { useAuthStore } from '@/stores/auth'
import { Plus, X, Eye, EyeOff, Trash2, Loader2 } from 'lucide-vue-next'
import axios from 'axios'
import { useGlobalDataStore } from '@/stores/globalData'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const globalDataStore = useGlobalDataStore()

const socialLinks = computed(() => globalDataStore.user?.social_links ?? [])
const socialLinkTypes = ref([])
const newLink = ref({
  social_link_type_id: '',
  username: ''
})
const formErrors = ref({})
const showAddForm = ref(false)
const isAdding = ref(false)
const showDeleteDialog = ref(false)
const linkToDelete = ref(null)

const fetchSocialLinkTypes = async () => {
  try {
    const response = await axios.get('/api/v1/social-links/types', {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
    socialLinkTypes.value = response.data || []
  } catch (error) {
    console.error('Error fetching social link types:', error)
  }
}

const resetFormErrors = () => {
  formErrors.value = {}
}

const addSocialLink = async () => {
  isAdding.value = true
  resetFormErrors()
  try {
    await axios.post('/api/v1/social-links', newLink.value, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
    globalDataStore.fetchGlobalData({ force: true })
    newLink.value = { social_link_type_id: '', username: '' }
    showAddForm.value = false
  } catch (error) {
    console.error('Error adding social link:', error)
    if (error.response?.status === 422) {
      formErrors.value = error.response.data?.errors ?? {}
      if (!formErrors.value.general && error.response.data?.message) {
        formErrors.value.general = [error.response.data.message]
      }
    } else {
      alert('Failed to add social link. Please try again.')
    }
  } finally {
    isAdding.value = false
  }
}

const getError = (field) => formErrors.value?.[field]?.[0] ?? null
const generalError = computed(() => getError('general') ?? getError('message'))

const toggleVisibility = async (link) => {
  link.social_link_type_id = link.social_link_type.id
  try {
    const response = await axios.put(
      `/api/v1/social-links/${link.id}`,
      {
        ...link,
        is_visible: !link.is_visible
      },
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`
        }
      }
    )
    globalDataStore.user.social_links.find(l => l.id === link.id).is_visible = response.data.data.is_visible
  } catch (error) {
    console.error('Error toggling visibility:', error)
    alert('Failed to update visibility. Please try again.')
  }
}

const showDeleteConfirmation = (link) => {
  linkToDelete.value = link
  showDeleteDialog.value = true
}

const deleteSocialLink = async () => {
  if (!linkToDelete.value) return

  try {
    await axios.delete(`/api/v1/social-links/${linkToDelete.value.id}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
    globalDataStore.user.social_links = globalDataStore.user.social_links.filter(
      (l) => l.id !== linkToDelete.value.id
    )
    linkToDelete.value = null
    showDeleteDialog.value = false
  } catch (error) {
    console.error('Error deleting social link:', error)
    alert('Failed to delete social link. Please try again.')
  }
}

const formatUsername = (username) => {
  return username.replace(/^https?:\/\//, '').replace(/\/$/, '')
}

onMounted(() => {
  fetchSocialLinkTypes()
})
</script>
<template>
  <div class="social-links-manager">
    <div class="mb-6">
      <Button
        @click="showAddForm = !showAddForm"
        :variant="showAddForm ? 'outline' : ''"
      >
        <X v-if="showAddForm" class="h-4 w-4 mr-2" />
        <Plus v-else class="h-4 w-4 mr-2" />
        {{ showAddForm ? 'Cancel' : 'Add New Link' }}
      </Button>

      <div
        v-if="showAddForm"
        class="mt-4 p-5 border rounded-lg shadow-sm"
        :class="[
          themeStore.isDark
            ? 'bg-gray-800/50 border-gray-700'
            : 'bg-gray-50 border-gray-200'
        ]"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium" :class="[
            themeStore.isDark ? 'text-white' : 'text-gray-900'
          ]">
            Add New Social Profile
          </h3>
          <Button
            variant="ghost"
            size="icon"
            @click="showAddForm = false"
            class="h-8 w-8"
          >
            <X class="h-4 w-4" />
          </Button>
        </div>
        <form @submit.prevent="addSocialLink">
          <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
            <div class="w-full sm:w-1/3">
              <label class="block text-sm font-medium mb-1.5" :class="[
                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
              ]">
                Platform
              </label>
              <select
                v-model="newLink.social_link_type_id"
                class="w-full h-10 rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="[
                  themeStore.isDark
                    ? 'bg-gray-800 border-gray-600 text-white'
                    : 'bg-white border-gray-300 text-gray-900'
                ]"
                required
              >
                <option value="">Select Platform</option>
                <option
                  v-for="type in socialLinkTypes"
                  :key="type.id"
                  :value="type.id"
                >
                  {{ type.name }}
                </option>
              </select>
              <p
                v-if="getError('social_link_type_id')"
                class="mt-1 text-xs text-red-500"
              >
                {{ getError('social_link_type_id') }}
              </p>
            </div>

            <div class="w-full sm:w-1/2">
              <label class="block text-sm font-medium mb-1.5" :class="[
                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
              ]">
                URL
              </label>
              <input
                v-model="newLink.username"
                type="text"
                class="flex h-10 w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="[
                  themeStore.isDark
                    ? 'bg-gray-800 text-white placeholder-gray-400'
                    : 'bg-white text-gray-900 placeholder-gray-500',
                  getError('username')
                    ? (themeStore.isDark ? 'border-red-600' : 'border-red-300')
                    : (themeStore.isDark ? 'border-gray-600' : 'border-gray-300')
                ]"
                placeholder="Enter full URL"
                required
              />
              <p
                v-if="getError('username')"
                class="mt-1 text-xs text-red-500"
              >
                {{ getError('username') }}
              </p>
            </div>

            <div class="flex gap-2">
              <Button type="submit" :disabled="isAdding" class="min-w-[80px]">
                <Loader2 v-if="isAdding" class="h-4 w-4 mr-2 animate-spin" />
                <span v-if="isAdding">Adding...</span>
                <span v-else>Add</span>
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div v-if="socialLinks.length > 0" class="relative">
      <TransitionGroup name="list" tag="div" class="space-y-3">
        <div
          v-for="link in socialLinks"
          :key="link.id"
          class="flex items-center gap-4 p-4 rounded-lg border transition-all"
          :class="[
            themeStore.isDark
              ? 'bg-gray-800/50 border-gray-700 hover:border-gray-600 hover:bg-gray-800/70'
              : 'bg-white border-gray-200 hover:border-gray-300 hover:bg-gray-50'
          ]"
        >
          <!-- Platform Icon -->
          <div
            class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full"
            :class="[
              themeStore.isDark 
                ? 'bg-gray-800/70 border border-gray-700' 
                : 'bg-gray-100 border border-gray-200'
            ]"
          >
            <i :class="[link.social_link_type.icon, 'text-xl', themeStore.isDark ? 'text-white' : 'text-gray-700']"></i>
          </div>

          <!-- Link Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h4 class="font-medium" :class="[
                themeStore.isDark ? 'text-white' : 'text-gray-900'
              ]">
                {{ link.social_link_type.name }}
              </h4>
              <span
                v-if="!link.is_visible"
                class="px-1.5 py-0.5 rounded-full text-xs font-medium"
                :class="[
                  themeStore.isDark
                    ? 'bg-gray-600 text-gray-300'
                    : 'bg-gray-200 text-gray-600'
                ]"
              >
                Hidden
              </span>
            </div>

            <a
              :href="link.url"
              target="_blank"
              rel="noopener noreferrer"
              class="text-sm hover:underline truncate block"
              :class="[
                themeStore.isDark ? 'text-blue-400 hover:text-blue-300' : 'text-blue-600 hover:text-blue-700'
              ]"
            >
              {{ formatUsername(link.username) }}
            </a>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <Button
              variant="ghost"
              size="icon"
              @click="toggleVisibility(link)"
              :title="link.is_visible ? 'Hide from profile' : 'Show on profile'"
              class="h-9 w-9 rounded-full"
              :class="[
                themeStore.isDark
                  ? 'hover:bg-gray-600 text-gray-300'
                  : 'hover:bg-gray-100 text-gray-600'
              ]"
            >
              <Eye v-if="link.is_visible" class="h-4 w-4" />
              <EyeOff v-else class="h-4 w-4" />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              @click="showDeleteConfirmation(link)"
              title="Delete link"
              class="h-9 w-9 rounded-full"
              :class="[
                themeStore.isDark
                  ? 'hover:bg-gray-600 text-gray-300 hover:text-red-400'
                  : 'hover:bg-gray-100 text-gray-600 hover:text-red-600'
              ]"
            >
              <Trash2 class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </TransitionGroup>
    </div>

    <div
      v-else
      class="flex flex-col items-center justify-center py-12 text-center border rounded-lg"
      :class="[
        themeStore.isDark
          ? 'border-gray-600 bg-gray-700/30'
          : 'border-gray-200 bg-gray-50'
      ]"
    >
      <div
        class="rounded-full p-4 mb-4"
        :class="[
          themeStore.isDark ? 'bg-gray-600' : 'bg-gray-200'
        ]"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          :class="[
            themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
          ]"
        >
          <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
          <polyline points="15 3 21 3 21 9"></polyline>
          <line x1="10" y1="14" x2="21" y2="3"></line>
        </svg>
      </div>
      <h3 class="text-lg font-medium mb-1" :class="[
        themeStore.isDark ? 'text-white' : 'text-gray-900'
      ]">
        No social profiles yet
      </h3>
      <p :class="[
        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
      ]">
        Add your social media profiles to share with others.
      </p>
    </div>

    <ConfirmDialog
      v-model:open="showDeleteDialog"
      title="Delete Social Link"
      description="Are you sure you want to delete this social link? This action cannot be undone."
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="destructive"
      @confirm="deleteSocialLink"
    />
  </div>
</template>
<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.list-leave-active {
  position: absolute;
  width: 100%;
}
</style>

