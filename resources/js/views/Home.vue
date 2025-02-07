<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { ArrowRight, Star, Users, FolderGit2, Download, Globe2 } from 'lucide-vue-next'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

const featuredProjects = ref([
  {
    id: 1,
    title: 'Vue Laravel Admin Panel',
    description: 'A modern admin panel built with Vue 3 and Laravel 10',
    type: 'Premium',
    price: '$49',
    stars: 245,
    image: 'https://picsum.photos/400/300'
  },
  {
    id: 2,
    title: 'Vue Laravel Starter Kit',
    description: 'Get started quickly with this Vue + Laravel template',
    type: 'Open Source',
    stars: 1200,
    image: 'https://picsum.photos/400/301'
  },
  {
    id: 3,
    title: 'Real-time Chat App',
    description: 'Full-stack chat application with Laravel WebSockets',
    type: 'Premium',
    price: '$39',
    stars: 890,
    image: 'https://picsum.photos/400/302'
  }
])

const communityStats = [
  { label: 'Active Members', value: '10,000+', icon: Users },
  { label: 'Projects Shared', value: '5,000+', icon: FolderGit2 },
  { label: 'Monthly Downloads', value: '1M+', icon: Download },
  { label: 'Countries', value: '120+', icon: Globe2 }
]

const communityFeatures = [
  {
    title: 'Share & Discover',
    description: 'Share your projects and discover amazing work from other developers in the community.'
  },
  {
    title: 'Collaborate & Learn',
    description: 'Work together on open-source projects and learn from experienced developers.'
  },
  {
    title: 'Grow & Earn',
    description: 'Monetize your work through premium projects and receive community support.'
  }
]
</script>

<template>
  <div :class="[
    'min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-8 lg:gap-y-20">
          <div class="relative z-10 mx-auto max-w-2xl lg:col-span-7 lg:max-w-none lg:pt-6 xl:col-span-6">
            <h1 class="text-4xl font-bold tracking-tight sm:text-6xl"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              Vue & Laravel Developer Community
            </h1>
            <p class="mt-6 text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              Join thousands of developers sharing and discovering amazing projects built with Vue.js and
              Laravel.
              Contribute, collaborate, and grow together.
            </p>
            <div class="mt-8 flex flex-wrap gap-x-6 gap-y-4">
              <Button @click="router.push('/projects')" class="text-white">
                Explore Projects
              </Button>
              <Button @click="router.push('/add-project')" variant="outline"
                class="border-secondary text-secondary hover:bg-secondary hover:text-white">
                Share Your Project
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Community Stats -->
    <section :class="[
      'border-t transition-colors duration-300',
      themeStore.isDark ? 'border-gray-800 bg-gray-900' : 'border-gray-200 bg-white'
    ]">
      <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              Trusted by developers worldwide
            </h2>
            <p class="mt-4 text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              Join our thriving community of Vue.js and Laravel developers
            </p>
          </div>
          <dl
            class="mt-16 grid grid-cols-1 gap-0.5 overflow-hidden rounded-2xl text-center sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="stat in communityStats" :key="stat.label" :class="[
              'flex flex-col p-8',
              themeStore.isDark ? 'bg-gray-800/80' : 'bg-gray-100'
            ]">
              <dt class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">{{
                stat.label }}</dt>
              <dd class="order-first text-3xl font-semibold tracking-tight"
                :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                <component :is="stat.icon" class="mx-auto mb-2 h-8 w-8 text-[#41B883]" />
                {{ stat.value }}
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </section>

    <!-- Featured Projects -->
    <section class="py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl"
              :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              Featured Projects
            </h2>
            <p class="mt-4 text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              Discover popular projects from our community
            </p>
          </div>
          <div class="mt-16 grid grid-cols-1 gap-x-8 gap-y-20 lg:grid-cols-3">
            <Card v-for="project in featuredProjects" :key="project.id" :class="[
              'transition-colors duration-300',
              themeStore.isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
            ]">
              <CardHeader>
                <img :src="project.image" :alt="project.title" class="w-full h-48 object-cover rounded-t-lg" />
              </CardHeader>
              <CardContent>
                <CardTitle :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ project.title
                  }}</CardTitle>
                <p class="mt-2" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">{{
                  project.description }}</p>
                <div class="mt-4 flex items-center justify-between">
                  <Badge :variant="project.type === 'Premium' ? 'default' : 'secondary'">
                    {{ project.type }}
                  </Badge>
                  <div class="flex items-center">
                    <Star class="h-5 w-5 text-yellow-400" />
                    <span class="ml-1" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">{{
                      project.stars }}</span>
                  </div>
                </div>
              </CardContent>
              <CardFooter>
                <Button class="w-full" :variant="project.type === 'Premium' ? 'default' : 'outline'">
                  {{ project.type === 'Premium' ? `Buy for ${project.price}` : 'View Project' }}
                </Button>
              </CardFooter>
            </Card>
          </div>
          <div class="mt-10 text-center">
            <Button variant="link" @click="router.push('/projects')" class="text-[#41B883] hover:text-[#3ca576]">
              View all projects
              <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>
    </section>

    <!-- Community Features -->
    <section :class="[
      'py-20 sm:py-32 transition-colors duration-300',
      themeStore.isDark ? 'bg-gray-900' : 'bg-gray-100'
    ]">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:text-center">
          <h2 class="text-base font-semibold leading-7 text-[#FF2D20]">Build Together</h2>
          <p class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl"
            :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Everything you need to grow as a developer
          </p>
          <p class="mt-6 text-lg" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Join a community of passionate developers sharing knowledge, code, and opportunities.
          </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
          <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
            <div v-for="(feature, index) in communityFeatures" :key="index" class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base font-semibold"
                :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-[#41B883]">
                  <component :is="[Users, FolderGit2, Download][index]" class="h-6 w-6 text-white" />
                </div>
                {{ feature.title }}
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base leading-7"
                :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                <p class="flex-auto">{{ feature.description }}</p>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>