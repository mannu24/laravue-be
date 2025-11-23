<!--
  UpdateProfile View
  Purpose: Update user profile information and manage social links
  UI similar to Settings page
-->
<template>
  <div :class="[
    'min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950' : 'bg-gray-50'
  ]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex items-center gap-4 mb-8">
        <Button
          variant="ghost"
          size="icon"
          @click="router.back()"
          class="flex-shrink-0"
        >
          <ArrowLeft class="h-5 w-5" />
        </Button>
        <div>
          <h1 class="text-3xl font-bold" :class="[
            themeStore.isDark ? 'text-white' : 'text-gray-900'
          ]">
            Update Profile
          </h1>
          <p class="text-sm mt-1" :class="[
            themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
          ]">
            Update your profile information and manage your social links
          </p>
        </div>
      </div>

      <!-- Profile Form -->
      <div class="space-y-6">
        <!-- Basic Information Card -->
        <Card :class="[
          'border-0 shadow-sm',
          themeStore.isDark ? 'bg-gray-900/50' : 'bg-white'
        ]">
          <CardHeader>
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-lg" :class="[
                themeStore.isDark ? 'bg-blue-900/30' : 'bg-blue-100'
              ]">
                <User class="h-5 w-5" :class="[
                  themeStore.isDark ? 'text-blue-400' : 'text-blue-600'
                ]" />
              </div>
              <div>
                <CardTitle :class="[
                  themeStore.isDark ? 'text-white' : 'text-gray-900'
                ]">
                  Basic Information
                </CardTitle>
                <CardDescription :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">
                  Update your name, email, bio, and avatar
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="updateProfile" class="space-y-6">
              <!-- Name and Email in one row -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name Field -->
                <div class="space-y-2">
                  <Label :class="[
                    themeStore.isDark ? 'text-white' : 'text-gray-900'
                  ]" class="text-sm font-medium">
                    Name <span class="text-red-500">*</span>
                  </Label>
                  <Input
                    v-model="formData.name"
                    type="text"
                    required
                    placeholder="Your name"
                    :class="[
                      themeStore.isDark
                        ? 'bg-gray-800 border-gray-700'
                        : 'bg-white border-gray-300'
                    ]"
                    maxlength="255"
                  />
                </div>

                <!-- Email Field -->
                <div class="space-y-2">
                  <Label :class="[
                    themeStore.isDark ? 'text-white' : 'text-gray-900'
                  ]" class="text-sm font-medium">
                    Email <span class="text-red-500">*</span>
                  </Label>
                  <Input
                    v-model="formData.email"
                    type="email"
                    required
                    placeholder="your.email@example.com"
                    :class="[
                      themeStore.isDark
                        ? 'bg-gray-800 border-gray-700'
                        : 'bg-white border-gray-300'
                    ]"
                    maxlength="255"
                  />
                </div>
              </div>

              <!-- Avatar Upload with Preview -->
              <div class="space-y-3">
                <Label :class="[
                  themeStore.isDark ? 'text-white' : 'text-gray-900'
                ]" class="text-sm font-medium">
                  Avatar
                </Label>
                <div class="flex items-start gap-4">
                  <!-- Avatar Preview -->
                  <div class="flex-shrink-0">
                    <div class="relative w-24 h-24 rounded-full overflow-hidden border-2" :class="[
                      themeStore.isDark ? 'border-gray-700' : 'border-gray-300'
                    ]">
                      <img
                        v-if="avatarPreview"
                        :src="avatarPreview"
                        alt="Avatar preview"
                        class="w-full h-full object-cover"
                        @error="handleImageError"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center" :class="[
                        themeStore.isDark ? 'bg-gray-800' : 'bg-gray-100'
                      ]">
                        <User class="h-8 w-8" :class="[
                          themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                        ]" />
                      </div>
                      <button
                        v-if="avatarPreview || avatarFile"
                        type="button"
                        @click="removeAvatar"
                        class="absolute top-0 right-0 p-1 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors"
                      >
                        <XIcon class="h-3 w-3" />
                      </button>
                    </div>
                  </div>

                  <!-- Upload Button -->
                  <div class="flex-1">
                    <input
                      ref="fileInputRef"
                      type="file"
                      accept="image/*"
                      @change="handleAvatarUpload"
                      class="hidden"
                      id="avatar-upload"
                    />
                    <label
                      for="avatar-upload"
                      class="inline-flex items-center justify-center px-4 py-2 rounded-md border cursor-pointer transition-colors"
                      :class="[
                        themeStore.isDark
                          ? 'bg-gray-800 border-gray-700 hover:bg-gray-700 text-white'
                          : 'bg-white border-gray-300 hover:bg-gray-50 text-gray-900'
                      ]"
                    >
                      <Upload class="h-4 w-4 mr-2" />
                      <span class="text-sm font-medium">
                        {{ avatarFile ? 'Change Avatar' : 'Upload Avatar' }}
                      </span>
                    </label>
                    <p class="text-xs mt-2" :class="[
                      themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                    ]">
                      JPG, PNG or GIF. Max size 5MB.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Bio Field -->
              <div class="space-y-2">
                <Label :class="[
                  themeStore.isDark ? 'text-white' : 'text-gray-900'
                ]" class="text-sm font-medium">
                  Bio
                </Label>
                <textarea
                  v-model="formData.bio"
                  rows="3"
                  class="w-full rounded-md border px-3 py-2 text-sm resize-none"
                  :class="[
                    themeStore.isDark
                      ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400'
                      : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'
                  ]"
                  placeholder="Tell us about yourself..."
                  maxlength="1000"
                ></textarea>
                <p class="text-xs" :class="[
                  themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                ]">
                  {{ (formData.bio || '').length }} / 1000 characters
                </p>
              </div>

              <!-- Error Message -->
              <div v-if="errorMessage" class="p-3 rounded-lg border" :class="[
                themeStore.isDark
                  ? 'bg-red-900/20 border-red-800/50'
                  : 'bg-red-50 border-red-200'
              ]">
                <div class="flex items-start gap-2">
                  <AlertCircle class="h-4 w-4 mt-0.5 flex-shrink-0" :class="[
                    themeStore.isDark ? 'text-red-400' : 'text-red-600'
                  ]" />
                  <p class="text-xs" :class="[
                    themeStore.isDark ? 'text-red-300' : 'text-red-700'
                  ]">
                    {{ errorMessage }}
                  </p>
                </div>
              </div>

              <!-- Success Message -->
              <div v-if="successMessage" class="p-3 rounded-lg border" :class="[
                themeStore.isDark
                  ? 'bg-green-900/20 border-green-800/50'
                  : 'bg-green-50 border-green-200'
              ]">
                <div class="flex items-start gap-2">
                  <CheckCircle class="h-4 w-4 mt-0.5 flex-shrink-0" :class="[
                    themeStore.isDark ? 'text-green-400' : 'text-green-600'
                  ]" />
                  <p class="text-xs" :class="[
                    themeStore.isDark ? 'text-green-300' : 'text-green-700'
                  ]">
                    {{ successMessage }}
                  </p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3 pt-4">
                <Button
                  type="submit"
                  :disabled="isSaving"
                  class="min-w-[120px]"
                >
                  <Loader2 v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
                  <span v-if="isSaving">Saving...</span>
                  <span v-else>Save Changes</span>
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  @click="router.back()"
                  :disabled="isSaving"
                >
                  Cancel
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Social Links Management Card -->
        <Card :class="[
          'border-0 shadow-sm',
          themeStore.isDark ? 'bg-gray-900/50' : 'bg-white'
        ]">
          <CardHeader>
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-lg" :class="[
                themeStore.isDark ? 'bg-purple-900/30' : 'bg-purple-100'
              ]">
                <Link class="h-5 w-5" :class="[
                  themeStore.isDark ? 'text-purple-400' : 'text-purple-600'
                ]" />
              </div>
              <div>
                <CardTitle :class="[
                  themeStore.isDark ? 'text-white' : 'text-gray-900'
                ]">
                  Social Links
                </CardTitle>
                <CardDescription :class="[
                  themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                ]">
                  Add and manage your social media profiles
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <SocialLinksManager />
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import SocialLinksManager from '@/components/profile/SocialLinksManager.vue'
import { useThemeStore } from '@/stores/theme'
import { useAuthStore } from '@/stores/auth'
import { ArrowLeft, Loader2, User, Link, AlertCircle, CheckCircle, Upload, X as XIcon } from 'lucide-vue-next'
import axios from 'axios'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const avatarFile = ref(null)
const avatarPreview = ref(null)
const fileInputRef = ref(null)
const formData = ref({
  name: '',
  email: '',
  bio: ''
})

// Initialize form data from auth store
const initializeFormData = () => {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }

  const user = authStore.user
  if (user) {
    formData.value = {
      name: user.name || '',
      email: user.email || '',
      bio: user.bio || ''
    }
    // Set avatar preview from existing profile photo
    avatarPreview.value = user.profile_photo || user.avatar || null
  }
}

// Handle avatar file selection
const handleAvatarUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      errorMessage.value = 'Please select a valid image file.'
      return
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = 'Image size must be less than 5MB.'
      return
    }

    avatarFile.value = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    errorMessage.value = ''
  }
}

// Remove avatar
const removeAvatar = () => {
  avatarFile.value = null
  avatarPreview.value = authStore.user?.profile_photo || authStore.user?.avatar || null
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

// Handle image error
const handleImageError = (event) => {
  event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(formData.value.name || 'User')}&background=6366f1&color=fff&size=200`
}

// Update profile
const updateProfile = async () => {
  if (!authStore.isAuthenticated) return

  // Clear previous messages
  errorMessage.value = ''
  successMessage.value = ''

  // Validate required fields
  if (!formData.value.name || !formData.value.email) {
    errorMessage.value = 'Name and email are required fields.'
    return
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(formData.value.email)) {
    errorMessage.value = 'Please enter a valid email address.'
    return
  }

  // Validate field lengths
  if (formData.value.name.length > 255) {
    errorMessage.value = 'Name must not exceed 255 characters.'
    return
  }
  if (formData.value.email.length > 255) {
    errorMessage.value = 'Email must not exceed 255 characters.'
    return
  }
  if (formData.value.bio && formData.value.bio.length > 1000) {
    errorMessage.value = 'Bio must not exceed 1000 characters.'
    return
  }

  isSaving.value = true
  try {
    // Create FormData for file upload
    const submitData = new FormData()
    submitData.append('name', formData.value.name)
    submitData.append('email', formData.value.email)
    if (formData.value.bio) {
      submitData.append('bio', formData.value.bio)
    }
    if (avatarFile.value) {
      submitData.append('avatar', avatarFile.value)
    }

    // Use POST method with FormData
    const response = await axios.post('/api/v1/user', submitData, {
      ...authStore.config,
      headers: {
        ...authStore.config.headers,
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response.data.status === 'success') {
      // Update auth store
      if (authStore.user && response.data.data) {
        const updatedUser = response.data.data
        authStore.user = { 
          ...authStore.user, 
          name: formData.value.name,
          email: formData.value.email,
          bio: formData.value.bio,
          avatar: updatedUser.avatar || updatedUser.profile_photo,
          profile_photo: updatedUser.profile_photo || updatedUser.avatar
        }
        // Update preview if avatar was uploaded
        if (updatedUser.profile_photo || updatedUser.avatar) {
          avatarPreview.value = updatedUser.profile_photo || updatedUser.avatar
        }
      }
      
      // Reset file input
      avatarFile.value = null
      if (fileInputRef.value) {
        fileInputRef.value.value = ''
      }
      
      successMessage.value = 'Profile updated successfully!'
      
      // Redirect after a short delay
      setTimeout(() => {
        router.push({ 
          name: 'profile', 
          params: { username: authStore.user?.username } 
        })
      }, 1500)
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    
    // Handle validation errors
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const firstError = Object.values(errors)[0]
      errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Failed to update profile. Please try again.'
    }
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  initializeFormData()
})
</script>

<style scoped>
/* Styles handled by Tailwind and components */
</style>
