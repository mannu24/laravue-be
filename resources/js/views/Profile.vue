<script setup>
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Calendar,
  Code2,
  Eye,
  Heart,
  Mail,
  MapPin,
  Settings,
  Star,
  Users
} from 'lucide-vue-next'
import { ref } from 'vue'
import SocialLinks from '../components/profile/SocialLinks.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useThemeStore } from '../stores/theme'

const themeStore = useThemeStore()

// Mock user data - replace with actual user data
const user = ref({
  username: 'johndoe',
  name: 'John Doe',
  email: 'john@example.com',
  bio: 'Full-stack developer passionate about Vue.js and Laravel. Love building scalable web applications and contributing to open source projects.',
  location: 'San Francisco, CA',
  joinDate: 'January 2023',
  avatar: 'https://picsum.photos/200/200?random=1',
  stats: {
    projects: 24,
    followers: 1250,
    following: 342,
    stars: 3400
  }
})

const projects = ref([
  {
    id: 1,
    title: 'Vue Task Manager',
    description: 'A modern task management application built with Vue 3 and TypeScript.',
    stars: 245,
    language: 'Vue.js',
    updated: '2 days ago',
    type: 'Open Source'
  },
  {
    id: 2,
    title: 'Laravel API Kit',
    description: 'Comprehensive API development toolkit for Laravel applications.',
    stars: 892,
    language: 'PHP',
    updated: '1 week ago',
    type: 'Premium'
  },
  {
    id: 3,
    title: 'Component Library',
    description: 'Reusable Vue components with Tailwind CSS styling.',
    stars: 156,
    language: 'Vue.js',
    updated: '3 days ago',
    type: 'Open Source'
  }
])
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <!-- Hero Section with Profile Header -->
    <div class="relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 -z-10">
        <div :class="['absolute inset-0 transition-all duration-500',
          themeStore.isDark
            ? 'bg-gradient-to-br from-gray-900 via-purple-900/10 to-gray-900'
            : 'bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50'
        ]"></div>

        <!-- Grid Pattern -->
        <div :class="['absolute inset-0 opacity-20',
          themeStore.isDark ? 'bg-grid-white/[0.05]' : 'bg-grid-black/[0.05]'
        ]"
          style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 30px 30px;">
        </div>
      </div>

      <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <Card :class="['border-0 shadow-2xl backdrop-blur-sm',
          themeStore.isDark ? 'bg-gray-800/90' : 'bg-white/90'
        ]">
          <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-purple-500 via-blue-500 to-indigo-500">
          </div>

          <CardContent class="p-8">
            <div class="flex flex-col md:flex-row gap-8">
              <!-- Avatar and Basic Info -->
              <div class="flex flex-col items-center md:items-start">
                <div class="relative mb-6">
                  <img :src="user.avatar" :alt="user.name"
                    class="w-32 h-32 rounded-full border-4 border-gradient-to-r from-purple-500 to-blue-500 shadow-xl">
                  <div
                    class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white dark:border-gray-800 flex items-center justify-center">
                    <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                  </div>
                </div>

                <Button
                  class="mb-4 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white">
                  <Settings class="h-4 w-4 mr-2" />
                  Edit Profile
                </Button>
              </div>

              <!-- Profile Details -->
              <div class="flex-1">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6">
                  <div>
                    <h1 class="text-4xl font-bold mb-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      {{ user.name }}
                    </h1>
                    <p class="text-xl text-purple-500 dark:text-purple-400 font-medium mb-4">
                      @{{ user.username }}
                    </p>
                  </div>
                </div>

                <p class="text-lg mb-6 leading-relaxed" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                  {{ user.bio }}
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-purple-500 dark:text-purple-400">{{ user.stats.projects }}</div>
                    <div class="text-sm text-muted-foreground">Projects</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-500 dark:text-blue-400">{{ user.stats.followers }}</div>
                    <div class="text-sm text-muted-foreground">Followers</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-500 dark:text-indigo-400">{{ user.stats.following }}
                    </div>
                    <div class="text-sm text-muted-foreground">Following</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-amber-500 dark:text-amber-400">{{ user.stats.stars }}</div>
                    <div class="text-sm text-muted-foreground">Stars</div>
                  </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm"
                  :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                  <div class="flex items-center">
                    <Mail class="h-4 w-4 mr-2" />
                    {{ user.email }}
                  </div>
                  <div class="flex items-center">
                    <MapPin class="h-4 w-4 mr-2" />
                    {{ user.location }}
                  </div>
                  <div class="flex items-center">
                    <Calendar class="h-4 w-4 mr-2" />
                    Joined {{ user.joinDate }}
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-24 -mt-8">
      <Tabs defaultValue="social" class="space-y-8">
        <TabsList :class="['grid grid-cols-3 w-full max-w-md mx-auto border-0 shadow-xl',
          themeStore.isDark ? 'bg-gray-800' : 'bg-white'
        ]">
          <TabsTrigger value="projects"
            class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-purple-500 data-[state=active]:to-blue-500 data-[state=active]:text-white">
            Projects
          </TabsTrigger>
          <TabsTrigger value="social"
            class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-purple-500 data-[state=active]:to-blue-500 data-[state=active]:text-white">
            Social
          </TabsTrigger>
          <TabsTrigger value="activity"
            class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-purple-500 data-[state=active]:to-blue-500 data-[state=active]:text-white">
            Activity
          </TabsTrigger>
        </TabsList>

        <!-- Projects Tab -->
        <TabsContent value="projects" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Card v-for="project in projects" :key="project.id" :class="['group transition-all duration-300 hover:-translate-y-2 border-0 shadow-xl cursor-pointer',
              themeStore.isDark ? 'bg-gray-800/90 hover:bg-gray-800' : 'bg-white/90 hover:bg-white'
            ]">
              <CardHeader>
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <CardTitle class="text-xl mb-2 group-hover:text-purple-500 transition-colors duration-300"
                      :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      {{ project.title }}
                    </CardTitle>
                    <div class="flex items-center gap-3 mb-3">
                      <span :class="[
                        'px-3 py-1 rounded-full text-xs font-medium',
                        project.type === 'Premium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                      ]">
                        {{ project.type }}
                      </span>
                      <span class="text-xs text-muted-foreground">{{ project.language }}</span>
                    </div>
                  </div>
                  <div class="flex items-center text-amber-500">
                    <Star class="h-4 w-4 mr-1 fill-current" />
                    <span class="text-sm font-medium">{{ project.stars }}</span>
                  </div>
                </div>
                <CardDescription :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                  {{ project.description }}
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="flex items-center justify-between text-sm text-muted-foreground">
                  <span>Updated {{ project.updated }}</span>
                  <div class="flex items-center space-x-3">
                    <div class="flex items-center">
                      <Eye class="h-4 w-4 mr-1" />
                      <span>{{ Math.floor(project.stars * 2.5) }}</span>
                    </div>
                    <div class="flex items-center">
                      <Heart class="h-4 w-4 mr-1" />
                      <span>{{ Math.floor(project.stars * 0.8) }}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Social Links Tab -->
        <TabsContent value="social">
          <SocialLinks />
        </TabsContent>

        <!-- Activity Tab -->
        <TabsContent value="activity" class="space-y-6">
          <Card :class="['border-0 shadow-xl',
            themeStore.isDark ? 'bg-gray-800/90' : 'bg-white/90'
          ]">
            <CardHeader>
              <CardTitle :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                Recent Activity
              </CardTitle>
              <CardDescription>
                Your recent contributions and interactions
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-start space-x-4 p-4 rounded-lg"
                  :class="themeStore.isDark ? 'bg-gray-700/50' : 'bg-gray-50'">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                    <Code2 class="h-5 w-5 text-white" />
                  </div>
                  <div class="flex-1">
                    <p class="font-medium" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      Created new project: Vue Task Manager
                    </p>
                    <p class="text-sm text-muted-foreground">2 days ago</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4 p-4 rounded-lg"
                  :class="themeStore.isDark ? 'bg-gray-700/50' : 'bg-gray-50'">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center">
                    <Star class="h-5 w-5 text-white" />
                  </div>
                  <div class="flex-1">
                    <p class="font-medium" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      Received 50 new stars on Laravel API Kit
                    </p>
                    <p class="text-sm text-muted-foreground">1 week ago</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4 p-4 rounded-lg"
                  :class="themeStore.isDark ? 'bg-gray-700/50' : 'bg-gray-50'">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                    <Users class="h-5 w-5 text-white" />
                  </div>
                  <div class="flex-1">
                    <p class="font-medium" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      Gained 25 new followers
                    </p>
                    <p class="text-sm text-muted-foreground">1 week ago</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>

<style scoped>
/* Grid background */
.bg-grid-white\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

.bg-grid-black\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
}

/* Smooth transitions */
* {
  transition-property: transform, opacity, background-color, border-color, color, fill, stroke, box-shadow;
  transition-timing-function: cubic-bezier
}
</style>