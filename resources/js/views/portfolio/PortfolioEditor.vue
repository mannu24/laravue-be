<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usePortfolioStore } from '@/stores/portfolioStore'
import { useThemeStore } from '@/stores/theme'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { toast } from '@/components/ui/toast'
import {
  User, Code2, Briefcase, GraduationCap, FolderGit2,
  MessageSquare, Layers, Save, Loader2, ArrowLeft, Plus, Trash2, Globe
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const portfolioStore = usePortfolioStore()
const themeStore = useThemeStore()

const activeTab = ref('profile')
const saving = ref(false)

const tabs = [
  { id: 'profile', label: 'Profile', icon: User },
  { id: 'skills', label: 'Skills', icon: Code2 },
  { id: 'experience', label: 'Experience', icon: Briefcase },
  { id: 'education', label: 'Education', icon: GraduationCap },
  { id: 'projects', label: 'Projects', icon: FolderGit2 },
  { id: 'testimonials', label: 'Testimonials', icon: MessageSquare },
  { id: 'social', label: 'Social Links', icon: Globe },
]

// Form data — initialized from portfolio
const profile = ref({ title: '', tagline: '', bio: '', location_city: '', location_country: '', available_for_hire: false, meta_title: '', meta_description: '' })
const skills = ref([])
const experiences = ref([])
const educations = ref([])
const projects = ref([])
const testimonials = ref([])
const socialLinks = ref([])

onMounted(async () => {
  await portfolioStore.fetchPortfolio()
  if (!portfolioStore.hasPortfolio) {
    router.push('/portfolio')
    return
  }
  initFormData()
})

const initFormData = () => {
  const p = portfolioStore.portfolio
  if (!p) return

  profile.value = {
    title: p.title || '',
    tagline: p.tagline || '',
    bio: p.bio || '',
    location_city: p.location_city || '',
    location_country: p.location_country || '',
    available_for_hire: p.available_for_hire || false,
    meta_title: p.meta_title || '',
    meta_description: p.meta_description || '',
  }
  skills.value = (p.skills || []).map(s => ({ name: s.name, proficiency: s.proficiency?.value || s.proficiency || null }))
  experiences.value = (p.experiences || []).map(e => ({
    company: e.company, role: e.role, description: e.description || '',
    start_date: e.start_date?.split('T')[0] || '', end_date: e.end_date?.split('T')[0] || '', is_current: e.is_current || false,
  }))
  educations.value = (p.educations || []).map(e => ({
    institution: e.institution, degree: e.degree || '', field: e.field || '',
    start_year: e.start_year || '', end_year: e.end_year || '',
  }))
  projects.value = (p.projects || []).map(pr => ({
    title: pr.title, description: pr.description || '', image_path: pr.image_path || '',
    tech_stack: pr.tech_stack || [], live_url: pr.live_url || '', source_url: pr.source_url || '',
    project_id: pr.project_id || null,
  }))
  testimonials.value = (p.testimonials || []).map(t => ({
    author_name: t.author_name, author_role: t.author_role || '', author_company: t.author_company || '',
    content: t.content, author_photo_url: t.author_photo_url || '',
  }))
  socialLinks.value = (p.social_links || []).map(l => ({ platform: l.platform, url: l.url }))
}

const saveProfile = async () => {
  saving.value = true
  try {
    await portfolioStore.updatePortfolio(profile.value)
    toast({ title: 'Profile saved!' })
  } catch (e) {
    toast({ title: 'Error', description: e.response?.data?.message || 'Save failed', variant: 'destructive' })
  } finally {
    saving.value = false
  }
}

const saveSection = async (section, data) => {
  saving.value = true
  try {
    await portfolioStore.updateSection(section, data)
    toast({ title: 'Saved!' })
  } catch (e) {
    toast({ title: 'Error', description: e.response?.data?.message || 'Save failed', variant: 'destructive' })
  } finally {
    saving.value = false
  }
}

// Helper to add empty items
const addSkill = () => skills.value.push({ name: '', proficiency: null })
const addExperience = () => experiences.value.push({ company: '', role: '', description: '', start_date: '', end_date: '', is_current: false })
const addEducation = () => educations.value.push({ institution: '', degree: '', field: '', start_year: '', end_year: '' })
const addProject = () => projects.value.push({ title: '', description: '', image_path: '', tech_stack: [], live_url: '', source_url: '', project_id: null })
const addTestimonial = () => testimonials.value.push({ author_name: '', author_role: '', author_company: '', content: '', author_photo_url: '' })
const addSocialLink = () => socialLinks.value.push({ platform: '', url: '' })

const removeItem = (arr, index) => arr.splice(index, 1)

const inputClass = computed(() => themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : '')
const labelClass = computed(() => themeStore.isDark ? 'text-gray-300' : 'text-gray-700')
</script>

<template>
  <div class="max-w-5xl mx-auto py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
      <Button variant="ghost" size="icon" @click="router.push('/portfolio')">
        <ArrowLeft class="w-5 h-5" />
      </Button>
      <h1 class="text-2xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
        Edit Portfolio
      </h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Tab Navigation -->
      <nav class="lg:w-48 flex lg:flex-col gap-1 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors"
          :class="[
            activeTab === tab.id
              ? 'bg-vue/10 text-vue'
              : themeStore.isDark ? 'text-gray-400 hover:text-gray-200 hover:bg-gray-800' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
          ]"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
        </button>
      </nav>

      <!-- Tab Content -->
      <div class="flex-1">
        <Card :class="themeStore.isDark ? 'bg-gray-800 border-gray-700' : ''">
          <CardContent class="pt-6">

            <!-- Profile Tab -->
            <div v-if="activeTab === 'profile'" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label :class="labelClass" class="text-sm font-medium block mb-1">Display Name</label>
                  <Input v-model="profile.title" placeholder="Your name" :class="inputClass" />
                </div>
                <div>
                  <label :class="labelClass" class="text-sm font-medium block mb-1">Tagline</label>
                  <Input v-model="profile.tagline" placeholder="Full Stack Developer" :class="inputClass" />
                </div>
              </div>
              <div>
                <label :class="labelClass" class="text-sm font-medium block mb-1">Bio</label>
                <Textarea v-model="profile.bio" rows="4" placeholder="Tell visitors about yourself..." :class="inputClass" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label :class="labelClass" class="text-sm font-medium block mb-1">City</label>
                  <Input v-model="profile.location_city" placeholder="Mumbai" :class="inputClass" />
                </div>
                <div>
                  <label :class="labelClass" class="text-sm font-medium block mb-1">Country</label>
                  <Input v-model="profile.location_country" placeholder="India" :class="inputClass" />
                </div>
              </div>
              <div class="flex items-center gap-2">
                <input type="checkbox" v-model="profile.available_for_hire" id="hire" class="rounded" />
                <label for="hire" :class="labelClass" class="text-sm">Available for hire</label>
              </div>
              <hr :class="themeStore.isDark ? 'border-gray-700' : ''">
              <p class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">SEO</p>
              <div>
                <label :class="labelClass" class="text-sm font-medium block mb-1">Meta Title</label>
                <Input v-model="profile.meta_title" placeholder="Page title for search engines" :class="inputClass" />
              </div>
              <div>
                <label :class="labelClass" class="text-sm font-medium block mb-1">Meta Description</label>
                <Textarea v-model="profile.meta_description" rows="2" placeholder="Brief description for search results" :class="inputClass" />
              </div>
              <Button @click="saveProfile" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                <Save v-else class="w-4 h-4 mr-2" /> Save Profile
              </Button>
            </div>

            <!-- Skills Tab -->
            <div v-if="activeTab === 'skills'" class="space-y-3">
              <div v-for="(skill, i) in skills" :key="i" class="flex gap-2 items-center">
                <Input v-model="skill.name" placeholder="Skill name" :class="inputClass" class="flex-1" />
                <select v-model="skill.proficiency"
                  class="px-3 py-2 rounded-md border text-sm"
                  :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-200'">
                  <option :value="null">Level</option>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                  <option value="expert">Expert</option>
                </select>
                <Button variant="ghost" size="icon" @click="removeItem(skills, i)" class="text-red-500 hover:text-red-600">
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>
              <Button variant="outline" size="sm" @click="addSkill" :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                <Plus class="w-4 h-4 mr-1" /> Add Skill
              </Button>
              <div class="pt-2">
                <Button @click="saveSection('skills', { skills })" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                  <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2" /> Save Skills
                </Button>
              </div>
            </div>

            <!-- Experience Tab -->
            <div v-if="activeTab === 'experience'" class="space-y-4">
              <div v-for="(exp, i) in experiences" :key="i" class="p-4 rounded-lg border space-y-3"
                :class="themeStore.isDark ? 'border-gray-600 bg-gray-700/50' : 'border-gray-200 bg-gray-50'">
                <div class="flex justify-between items-start">
                  <span class="text-sm font-medium" :class="labelClass">Experience {{ i + 1 }}</span>
                  <Button variant="ghost" size="icon" @click="removeItem(experiences, i)" class="text-red-500 h-6 w-6">
                    <Trash2 class="w-3 h-3" />
                  </Button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <Input v-model="exp.role" placeholder="Role / Title" :class="inputClass" />
                  <Input v-model="exp.company" placeholder="Company" :class="inputClass" />
                </div>
                <Textarea v-model="exp.description" rows="2" placeholder="Description" :class="inputClass" />
                <div class="grid grid-cols-2 gap-3">
                  <Input v-model="exp.start_date" type="date" :class="inputClass" />
                  <Input v-model="exp.end_date" type="date" :disabled="exp.is_current" :class="inputClass" />
                </div>
                <div class="flex items-center gap-2">
                  <input type="checkbox" v-model="exp.is_current" :id="'current-' + i" class="rounded" />
                  <label :for="'current-' + i" class="text-sm" :class="labelClass">Currently working here</label>
                </div>
              </div>
              <Button variant="outline" size="sm" @click="addExperience" :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                <Plus class="w-4 h-4 mr-1" /> Add Experience
              </Button>
              <div>
                <Button @click="saveSection('experience', { experience: experiences })" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                  <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2" /> Save Experience
                </Button>
              </div>
            </div>

            <!-- Education Tab -->
            <div v-if="activeTab === 'education'" class="space-y-4">
              <div v-for="(edu, i) in educations" :key="i" class="p-4 rounded-lg border space-y-3"
                :class="themeStore.isDark ? 'border-gray-600 bg-gray-700/50' : 'border-gray-200 bg-gray-50'">
                <div class="flex justify-between items-start">
                  <span class="text-sm font-medium" :class="labelClass">Education {{ i + 1 }}</span>
                  <Button variant="ghost" size="icon" @click="removeItem(educations, i)" class="text-red-500 h-6 w-6">
                    <Trash2 class="w-3 h-3" />
                  </Button>
                </div>
                <Input v-model="edu.institution" placeholder="Institution" :class="inputClass" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <Input v-model="edu.degree" placeholder="Degree" :class="inputClass" />
                  <Input v-model="edu.field" placeholder="Field of study" :class="inputClass" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <Input v-model="edu.start_year" type="number" placeholder="Start year" :class="inputClass" />
                  <Input v-model="edu.end_year" type="number" placeholder="End year" :class="inputClass" />
                </div>
              </div>
              <Button variant="outline" size="sm" @click="addEducation" :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                <Plus class="w-4 h-4 mr-1" /> Add Education
              </Button>
              <div>
                <Button @click="saveSection('education', { education: educations })" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                  <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2" /> Save Education
                </Button>
              </div>
            </div>

            <!-- Projects Tab -->
            <div v-if="activeTab === 'projects'" class="space-y-4">
              <div v-for="(proj, i) in projects" :key="i" class="p-4 rounded-lg border space-y-3"
                :class="themeStore.isDark ? 'border-gray-600 bg-gray-700/50' : 'border-gray-200 bg-gray-50'">
                <div class="flex justify-between items-start">
                  <span class="text-sm font-medium" :class="labelClass">Project {{ i + 1 }}</span>
                  <Button variant="ghost" size="icon" @click="removeItem(projects, i)" class="text-red-500 h-6 w-6">
                    <Trash2 class="w-3 h-3" />
                  </Button>
                </div>
                <Input v-model="proj.title" placeholder="Project title" :class="inputClass" />
                <Textarea v-model="proj.description" rows="2" placeholder="Brief description" :class="inputClass" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <Input v-model="proj.live_url" placeholder="Live URL" :class="inputClass" />
                  <Input v-model="proj.source_url" placeholder="Source URL" :class="inputClass" />
                </div>
              </div>
              <Button variant="outline" size="sm" @click="addProject" :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                <Plus class="w-4 h-4 mr-1" /> Add Project
              </Button>
              <div>
                <Button @click="saveSection('projects', { projects })" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                  <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2" /> Save Projects
                </Button>
              </div>
            </div>

            <!-- Testimonials Tab -->
            <div v-if="activeTab === 'testimonials'" class="space-y-4">
              <div v-for="(t, i) in testimonials" :key="i" class="p-4 rounded-lg border space-y-3"
                :class="themeStore.isDark ? 'border-gray-600 bg-gray-700/50' : 'border-gray-200 bg-gray-50'">
                <div class="flex justify-between items-start">
                  <span class="text-sm font-medium" :class="labelClass">Testimonial {{ i + 1 }}</span>
                  <Button variant="ghost" size="icon" @click="removeItem(testimonials, i)" class="text-red-500 h-6 w-6">
                    <Trash2 class="w-3 h-3" />
                  </Button>
                </div>
                <Textarea v-model="t.content" rows="3" placeholder="What they said..." :class="inputClass" />
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <Input v-model="t.author_name" placeholder="Name" :class="inputClass" />
                  <Input v-model="t.author_role" placeholder="Role" :class="inputClass" />
                  <Input v-model="t.author_company" placeholder="Company" :class="inputClass" />
                </div>
              </div>
              <Button variant="outline" size="sm" @click="addTestimonial" :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                <Plus class="w-4 h-4 mr-1" /> Add Testimonial
              </Button>
              <div>
                <Button @click="saveSection('testimonials', { testimonials })" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                  <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2" /> Save Testimonials
                </Button>
              </div>
            </div>

            <!-- Social Links Tab -->
            <div v-if="activeTab === 'social'" class="space-y-3">
              <div v-for="(link, i) in socialLinks" :key="i" class="flex gap-2 items-center">
                <select v-model="link.platform"
                  class="px-3 py-2 rounded-md border text-sm w-36"
                  :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-200'">
                  <option value="">Platform</option>
                  <option value="GitHub">GitHub</option>
                  <option value="LinkedIn">LinkedIn</option>
                  <option value="Twitter">Twitter</option>
                  <option value="Website">Website</option>
                  <option value="Email">Email</option>
                  <option value="YouTube">YouTube</option>
                  <option value="Dribbble">Dribbble</option>
                  <option value="Other">Other</option>
                </select>
                <Input v-model="link.url" placeholder="https://..." :class="inputClass" class="flex-1" />
                <Button variant="ghost" size="icon" @click="removeItem(socialLinks, i)" class="text-red-500 hover:text-red-600">
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>
              <Button variant="outline" size="sm" @click="addSocialLink" :class="themeStore.isDark ? 'border-gray-600 text-gray-300' : ''">
                <Plus class="w-4 h-4 mr-1" /> Add Link
              </Button>
              <div class="pt-2">
                <Button @click="saveSection('social-links', { links: socialLinks })" :disabled="saving" class="bg-vue hover:bg-vue/90 text-white">
                  <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2" /> Save Links
                </Button>
              </div>
            </div>

          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
