<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { ArrowRight, Star, Users, FolderGit2, Download, Globe2, Code2, Zap, Shield, Heart, Github, Twitter, MessageCircle, BookOpen, Award, TrendingUp, Clock, CheckCircle, Play, Eye, ThumbsUp, MessageSquare, Bookmark, Filter, Search, Flame, Trophy, Rocket, Sparkles, Target, Coffee, Lightbulb, Cpu, Database, Server, GitBranch, Package, Terminal, FileCode, Layers, Settings, BarChart3, PieChart, Activity, Bell, Calendar, MapPin, ExternalLink, Share2, Plus } from 'lucide-vue-next'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

const featuredProjects = ref([
  {
    id: 1,
    title: 'Laravel E-Commerce Platform',
    description: 'Complete e-commerce solution with payment integration, inventory management, and admin dashboard',
    type: 'Premium',
    price: '$89',
    stars: 1245,
    downloads: '15.2k',
    image: 'https://picsum.photos/600/400?random=1',
    author: 'Sarah Johnson',
    avatar: 'https://picsum.photos/40/40?random=101',
    tags: ['Laravel 11', 'Vue.js', 'Stripe', 'Redis'],
    category: 'E-Commerce',
    featured: true,
    trending: true
  },
  {
    id: 2,
    title: 'Real-time Chat & Video Calls',
    description: 'WebRTC-powered chat application with Laravel WebSockets, file sharing, and video conferencing',
    type: 'Open Source',
    stars: 2890,
    downloads: '28.5k',
    image: 'https://picsum.photos/600/400?random=2',
    author: 'Alex Chen',
    avatar: 'https://picsum.photos/40/40?random=102',
    tags: ['Laravel', 'WebRTC', 'Socket.io', 'Vue 3'],
    category: 'Communication',
    featured: true
  },
  {
    id: 3,
    title: 'AI-Powered CMS',
    description: 'Content management system with AI content generation, SEO optimization, and multi-language support',
    type: 'Premium',
    price: '$129',
    stars: 987,
    downloads: '8.9k',
    image: 'https://picsum.photos/600/400?random=3',
    author: 'Maria Rodriguez',
    avatar: 'https://picsum.photos/40/40?random=103',
    tags: ['Laravel', 'OpenAI', 'Tailwind', 'Inertia'],
    category: 'CMS',
    featured: true,
    new: true
  }
])

const communityStats = [
  { label: 'Laravel Developers', value: '25,000+', icon: Users, color: 'from-primary to-primary/80' },
  { label: 'Projects Shared', value: '12,500+', icon: FolderGit2, color: 'from-secondary to-secondary/80' },
  { label: 'Questions Asked', value: '10,000+', icon: MessageCircle, color: 'from-primary to-primary/80' },
  { label: 'Countries', value: '180+', icon: Globe2, color: 'from-secondary to-secondary/60' }
]

const recentQnA = ref([
  {
    id: 1,
    title: 'How to implement Laravel Sanctum with Vue 3 Composition API?',
    author: 'DevMaster',
    avatar: 'https://picsum.photos/32/32?random=201',
    answers: 12,
    views: 1250,
    votes: 45,
    tags: ['Laravel', 'Sanctum', 'Vue 3', 'Authentication'],
    timeAgo: '2 hours ago',
    solved: true
  },
  {
    id: 2,
    title: 'Best practices for Laravel queue optimization in production?',
    author: 'QueueExpert',
    avatar: 'https://picsum.photos/32/32?random=202',
    answers: 8,
    views: 890,
    votes: 32,
    tags: ['Laravel', 'Queues', 'Performance', 'Redis'],
    timeAgo: '4 hours ago',
    hot: true
  },
  {
    id: 3,
    title: 'Laravel Livewire vs Inertia.js - Which to choose in 2024?',
    author: 'FullStackDev',
    avatar: 'https://picsum.photos/32/32?random=203',
    answers: 15,
    views: 2100,
    votes: 67,
    tags: ['Laravel', 'Livewire', 'Inertia.js', 'Frontend'],
    timeAgo: '6 hours ago',
    trending: true
  }
])

const communityFeed = ref([
  {
    id: 1,
    type: 'project_launch',
    author: 'TechInnovator',
    avatar: 'https://picsum.photos/40/40?random=301',
    content: 'Just launched my Laravel-powered SaaS boilerplate! 🚀 Includes subscription billing, team management, and API integrations.',
    image: 'https://picsum.photos/500/300?random=feed1',
    likes: 89,
    comments: 23,
    shares: 12,
    timeAgo: '1 hour ago',
    tags: ['Laravel', 'SaaS', 'Stripe']
  },
  {
    id: 2,
    type: 'achievement',
    author: 'LaravelNinja',
    avatar: 'https://picsum.photos/40/40?random=302',
    content: 'Excited to share that my Laravel package just hit 10k downloads! 🎉 Thank you to the amazing Laravel community!',
    likes: 156,
    comments: 34,
    shares: 28,
    timeAgo: '3 hours ago',
    milestone: '10K Downloads'
  },
  {
    id: 3,
    type: 'tutorial',
    author: 'CodeMentor',
    avatar: 'https://picsum.photos/40/40?random=303',
    content: 'New tutorial series: Building a multi-tenant Laravel application from scratch. Part 1 is now live!',
    image: 'https://picsum.photos/500/300?random=feed2',
    likes: 234,
    comments: 45,
    shares: 67,
    timeAgo: '5 hours ago',
    tags: ['Tutorial', 'Multi-tenant', 'Laravel']
  }
])

const laravelFeatures = [
  {
    title: 'Eloquent ORM',
    description: 'Beautiful, simple ActiveRecord implementation for working with your database',
    icon: Database,
    color: 'from-primary to-primary/80'
  },
  {
    title: 'Artisan CLI',
    description: 'Powerful command-line interface for rapid development and automation',
    icon: Terminal,
    color: 'from-secondary to-secondary/80'
  },
  {
    title: 'Blade Templates',
    description: 'Simple, yet powerful templating engine with template inheritance',
    icon: FileCode,
    color: 'from-primary to-primary/60'
  },
  {
    title: 'Queue System',
    description: 'Unified API across variety of queue backends for background job processing',
    icon: Layers,
    color: 'from-secondary to-secondary/60'
  }
]

const categories = [
  { name: 'E-Commerce', count: 1250, icon: '🛒', color: 'bg-primary' },
  { name: 'CMS', count: 890, icon: '📝', color: 'bg-secondary' },
  { name: 'API', count: 2100, icon: '🔌', color: 'bg-primary/80' },
  { name: 'SaaS', count: 750, icon: '☁️', color: 'bg-secondary/80' },
  { name: 'Admin Panels', count: 1500, icon: '⚙️', color: 'bg-primary/60' },
  { name: 'Real-time', count: 650, icon: '⚡', color: 'bg-secondary/60' }
]
</script>

<template>
  <div :class="['min-h-screen transition-all duration-500',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">

    <!-- Hero Section with Animated Background -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 -z-10">
                <div :class="['absolute inset-0 transition-all duration-500',
          themeStore.isDark 
            ? 'bg-gradient-to-br from-gray-900 via-primary/10 to-gray-900' 
            : 'bg-gradient-to-br from-primary/5 via-secondary/5 to-primary/5'
        ]"></div>
        <div class="absolute top-20 left-10 w-20 h-20 bg-primary/10 rounded-full animate-pulse"></div>
        <div class="absolute top-40 right-20 w-32 h-32 bg-secondary/10 rounded-full animate-bounce"
          style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-1/4 w-16 h-16 bg-primary/5 rounded-full animate-pulse"
          style="animation-delay: 2s;"></div>
        <div :class="['absolute inset-0 opacity-20',
          themeStore.isDark ? 'bg-grid-white/[0.05]' : 'bg-grid-black/[0.05]'
        ]"
          style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 50px 50px;">
        </div>
      </div>

      <div class="container mx-auto px-4 py-20 sm:py-32 lg:py-30">
        <div class="text-center max-w-5xl mx-auto">
          <!-- Badge -->
          <div class="mb-8 inline-flex items-center">
            <Badge
              class="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground border-0 px-6 py-2 text-sm font-medium">
              <Sparkles class="w-4 h-4 mr-2" />
              Laravel 11 Community Hub
            </Badge>
          </div>

          <!-- Main Heading -->
          <h1 class="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-black tracking-tight mb-8">
            <span :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Build Amazing</span>
            <br>
            <span class="bg-gradient-to-r from-primary via-primary/80 to-secondary bg-clip-text text-transparent">
              Laravel Projects
            </span>
          </h1>

          <!-- Subtitle -->
                    <p class="text-xl sm:text-2xl lg:text-3xl font-medium mb-12 max-w-4xl mx-auto leading-relaxed"
             :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Join <span class="text-primary font-bold">25,000+</span> Laravel developers sharing projects, 
            solving problems, and building the future of web development together.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
                        <Button @click="router.push('/projects')" 
                    class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground px-10 py-4 text-lg font-semibold rounded-full shadow-2xl transform hover:scale-105 transition-all duration-300">
              <Rocket class="w-5 h-5 mr-2" />
              Explore Projects
            </Button>
            <Button @click="router.push('/add-project')" variant="outline" :class="['border-2 px-10 py-4 text-lg font-semibold rounded-full transition-all duration-300 hover:scale-105',
              themeStore.isDark
                ? 'border-gray-600 text-gray-300 hover:bg-gray-800 hover:border-primary hover:text-primary'
                : 'border-gray-300 text-gray-700 hover:bg-primary/10 hover:border-primary hover:text-primary'
            ]">
              <Plus class="w-5 h-5 mr-2" />
              Share Your Project
            </Button>
          </div>

          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-3xl mx-auto">
            <div v-for="stat in communityStats" :key="stat.label" class="text-center">
              <div
                :class="['w-16 h-16 mx-auto mb-3 rounded-2xl bg-gradient-to-r flex items-center justify-center', stat.color]">
                <component :is="stat.icon" class="w-8 h-8 text-white" />
              </div>
              <div class="text-2xl font-bold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ stat.value }}
              </div>
              <div class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                {{ stat.label }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section :class="['py-20 transition-colors duration-500',
      themeStore.isDark ? 'bg-gray-900/50' : 'bg-white'
    ]">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Explore by Category
          </h2>
          <p class="text-xl" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
            Discover Laravel projects across different domains
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
          <div v-for="category in categories" :key="category.name" :class="['group cursor-pointer p-6 rounded-2xl transition-all duration-300 hover:scale-105 hover:shadow-2xl',
            themeStore.isDark ? 'bg-gray-800/50 hover:bg-gray-800' : 'bg-gray-50 hover:bg-white hover:shadow-lg'
          ]">
            <div class="text-center">
              <div
                :class="['w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-2xl', category.color]">
                {{ category.icon }}
              </div>
              <h3 class="font-semibold mb-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ category.name }}
              </h3>
              <p class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                {{ category.count }} projects
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Projects -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-16">
          <div>
            <h2 class="text-4xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              🔥 Trending Projects
            </h2>
            <p class="text-xl" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
              Most popular Laravel projects this week
            </p>
          </div>
          <Button variant="outline" @click="router.push('/projects')"
            :class="themeStore.isDark ? 'border-gray-600 text-gray-300 hover:bg-gray-800' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
            View All
            <ArrowRight class="w-4 h-4 ml-2" />
          </Button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div v-for="project in featuredProjects" :key="project.id" :class="['group relative overflow-hidden rounded-3xl transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl',
            themeStore.isDark ? 'bg-gray-800 border border-gray-700' : 'bg-white border border-gray-200'
          ]">

            <!-- Project Image -->
            <div class="relative overflow-hidden">
              <img :src="project.image" :alt="project.title"
                class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110" />

              <!-- Overlay Badges -->
              <div class="absolute top-4 left-4 flex gap-2">
                <Badge v-if="project.trending" class="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground border-0">
                  <Flame class="w-3 h-3 mr-1" />
                  Trending
                </Badge>
                <Badge v-if="project.new" class="bg-gradient-to-r from-secondary to-secondary/80 text-secondary-foreground border-0">
                  <Sparkles class="w-3 h-3 mr-1" />
                  New
                </Badge>
              </div>

                            <div class="absolute top-4 right-4">
                <Badge :class="project.type === 'Premium' 
                  ? 'bg-gradient-to-r from-primary to-primary/80 text-primary-foreground border-0' 
                  : 'bg-gradient-to-r from-secondary to-secondary/80 text-secondary-foreground border-0'">
                  {{ project.type }}
                </Badge>
              </div>

              <!-- Quick Actions Overlay -->
              <div
                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                <div class="flex gap-3">
                  <Button size="sm" class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30">
                    <Eye class="w-4 h-4" />
                  </Button>
                  <Button size="sm" class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30">
                    <Heart class="w-4 h-4" />
                  </Button>
                  <Button size="sm" class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30">
                    <Share2 class="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </div>

            <!-- Project Content -->
            <div class="p-6">
              <!-- Author Info -->
              <div class="flex items-center mb-4">
                <img :src="project.avatar" :alt="project.author" class="w-8 h-8 rounded-full mr-3" />
                <span class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                  {{ project.author }}
                </span>
                <Badge variant="secondary" class="ml-auto text-xs">
                  {{ project.category }}
                </Badge>
              </div>

              <!-- Project Title & Description -->
              <h3 class="text-xl font-bold mb-3" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ project.title }}
              </h3>
              <p class="text-sm leading-relaxed mb-4" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                {{ project.description }}
              </p>

              <!-- Tags -->
              <div class="flex flex-wrap gap-2 mb-4">
                <Badge v-for="tag in project.tags" :key="tag" variant="secondary" class="text-xs">
                  {{ tag }}
                </Badge>
              </div>

              <!-- Stats -->
              <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4 text-sm">
                  <div class="flex items-center">
                    <Star class="w-4 h-4 text-yellow-500 mr-1" />
                    <span :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                      {{ project.stars }}
                    </span>
                  </div>
                  <div class="flex items-center">
                    <Download class="w-4 h-4 text-green-500 mr-1" />
                    <span :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                      {{ project.downloads }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Action Button -->
              <Button class="w-full" :class="project.type === 'Premium'
                ? 'bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground'
                : 'bg-gradient-to-r from-secondary to-secondary/80 hover:from-secondary/90 hover:to-secondary/70 text-secondary-foreground'
                ">
                {{ project.type === 'Premium' ? `Buy for ${project.price}` : 'View Project' }}
                <ExternalLink class="w-4 h-4 ml-2" />
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Q&A Section -->
    <section :class="['py-20 transition-colors duration-500',
      themeStore.isDark ? 'bg-gray-900/30' : 'bg-gray-50'
    ]">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-16">
          <div>
            <h2 class="text-4xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              💬 Latest Q&A
            </h2>
            <p class="text-xl" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
              Get help from Laravel experts
            </p>
          </div>
          <Button variant="outline" @click="router.push('/qna')"
            :class="themeStore.isDark ? 'border-gray-600 text-gray-300 hover:bg-gray-800' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
            Ask Question
            <MessageSquare class="w-4 h-4 ml-2" />
          </Button>
        </div>

        <div class="space-y-6">
          <div v-for="question in recentQnA" :key="question.id" :class="['group p-6 rounded-2xl transition-all duration-300 hover:scale-[1.01] cursor-pointer',
            themeStore.isDark ? 'bg-gray-800/50 hover:bg-gray-800 border border-gray-700' : 'bg-white hover:bg-gray-50 border border-gray-200'
          ]">

            <div class="flex items-start space-x-4">
              <!-- Author Avatar -->
              <img :src="question.avatar" :alt="question.author" class="w-12 h-12 rounded-full flex-shrink-0" />

              <div class="flex-grow">
                <!-- Question Header -->
                <div class="flex items-center space-x-3 mb-3">
                  <h3 class="font-semibold" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                    {{ question.author }}
                  </h3>
                  <span class="text-sm" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-500'">
                    {{ question.timeAgo }}
                  </span>

                  <!-- Status Badges -->
                  <div class="flex space-x-2">
                    <Badge v-if="question.solved"
                      class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                      <CheckCircle class="w-3 h-3 mr-1" />
                      Solved
                    </Badge>
                    <Badge v-if="question.hot" class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                      <Flame class="w-3 h-3 mr-1" />
                      Hot
                    </Badge>
                    <Badge v-if="question.trending"
                      class="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                      <TrendingUp class="w-3 h-3 mr-1" />
                      Trending
                    </Badge>
                  </div>
                </div>

                <!-- Question Title -->
                <h4 class="text-lg font-semibold mb-3 group-hover:text-primary transition-colors duration-300"
                  :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  {{ question.title }}
                </h4>

                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-4">
                  <Badge v-for="tag in question.tags" :key="tag" variant="secondary" class="text-xs">
                    {{ tag }}
                  </Badge>
                </div>

                <!-- Stats -->
                <div class="flex items-center space-x-6 text-sm">
                  <div class="flex items-center">
                    <ThumbsUp class="w-4 h-4 mr-1 text-primary" />
                    <span :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                      {{ question.votes }} votes
                    </span>
                  </div>
                  <div class="flex items-center">
                    <MessageSquare class="w-4 h-4 mr-1 text-secondary" />
                    <span :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                      {{ question.answers }} answers
                    </span>
                  </div>
                  <div class="flex items-center">
                    <Eye class="w-4 h-4 mr-1 text-primary/80" />
                    <span :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                      {{ question.views }} views
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Community Feed -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-16">
          <div>
            <h2 class="text-4xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              📢 Community Feed
            </h2>
            <p class="text-xl" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
              Latest updates from the Laravel community
            </p>
          </div>
          <Button variant="outline" @click="router.push('/feed')"
            :class="themeStore.isDark ? 'border-gray-600 text-gray-300 hover:bg-gray-800' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
            View All Posts
            <Activity class="w-4 h-4 ml-2" />
          </Button>
        </div>

        <div class="max-w-2xl mx-auto space-y-8">
          <div v-for="post in communityFeed" :key="post.id" :class="['p-6 rounded-2xl transition-all duration-300 hover:scale-[1.01]',
            themeStore.isDark ? 'bg-gray-800/50 border border-gray-700' : 'bg-white border border-gray-200'
          ]">

            <!-- Post Header -->
            <div class="flex items-center space-x-3 mb-4">
              <img :src="post.avatar" :alt="post.author" class="w-12 h-12 rounded-full" />
              <div class="flex-grow">
                <h4 class="font-semibold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  {{ post.author }}
                </h4>
                <p class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                  {{ post.timeAgo }}
                </p>
              </div>

              <!-- Post Type Badge -->
              <Badge v-if="post.milestone" class="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground">
                <Trophy class="w-3 h-3 mr-1" />
                {{ post.milestone }}
              </Badge>
            </div>

            <!-- Post Content -->
            <p class="text-lg mb-4 leading-relaxed" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
              {{ post.content }}
            </p>

            <!-- Post Image -->
            <div v-if="post.image" class="mb-4 rounded-xl overflow-hidden">
              <img :src="post.image" :alt="post.content" class="w-full h-64 object-cover" />
            </div>

            <!-- Post Tags -->
            <div v-if="post.tags" class="flex flex-wrap gap-2 mb-4">
              <Badge v-for="tag in post.tags" :key="tag" variant="secondary" class="text-xs">
                {{ tag }}
              </Badge>
            </div>

            <!-- Post Actions -->
            <div class="flex items-center justify-between pt-4 border-t"
              :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-200'">
              <div class="flex items-center space-x-6">
                <button class="flex items-center space-x-2 text-sm hover:text-primary transition-colors duration-300">
                  <Heart class="w-4 h-4" />
                  <span>{{ post.likes }}</span>
                </button>
                <button class="flex items-center space-x-2 text-sm hover:text-secondary transition-colors duration-300">
                  <MessageSquare class="w-4 h-4" />
                  <span>{{ post.comments }}</span>
                </button>
                <button class="flex items-center space-x-2 text-sm hover:text-primary/80 transition-colors duration-300">
                  <Share2 class="w-4 h-4" />
                  <span>{{ post.shares }}</span>
                </button>
              </div>
              <Button size="sm" variant="ghost">
                <Bookmark class="w-4 h-4" />
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Laravel Features Showcase -->
    <section :class="['py-20 transition-colors duration-500',
      themeStore.isDark ? 'bg-gradient-to-r from-gray-900 via-red-900/10 to-gray-900' : 'bg-gradient-to-r from-red-50 via-orange-50 to-yellow-50'
    ]">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            ⚡ Why Laravel?
          </h2>
          <p class="text-xl max-w-3xl mx-auto" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
            Laravel makes web development a joy with its elegant syntax and powerful features
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="feature in laravelFeatures" :key="feature.title" :class="['group p-8 rounded-3xl transition-all duration-500 hover:scale-105 hover:shadow-2xl text-center',
            themeStore.isDark ? 'bg-gray-800/50 hover:bg-gray-800' : 'bg-white/80 backdrop-blur-sm hover:bg-white'
          ]">

            <div
              :class="['w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-r flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300', feature.color]">
              <component :is="feature.icon" class="w-10 h-10 text-white" />
            </div>

            <h3 class="text-xl font-bold mb-4" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              {{ feature.title }}
            </h3>

            <p class="leading-relaxed" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 sm:py-32">
      <div class="container mx-auto px-4">
        <div :class="['relative overflow-hidden rounded-3xl p-12 lg:p-20 text-center',
          themeStore.isDark ? 'bg-gradient-to-r from-gray-800 to-gray-900' : 'bg-gradient-to-r from-primary to-primary/80'
        ]">

          <!-- Background Pattern -->
          <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0"
              style="background-image: radial-gradient(circle, currentColor 2px, transparent 2px); background-size: 40px 40px;">
            </div>
          </div>

          <div class="relative z-10">
            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6 text-white">
              Ready to Build Something Amazing?
            </h2>

            <p class="text-xl lg:text-2xl mb-12 text-white/90 max-w-3xl mx-auto">
              Join thousands of Laravel developers sharing knowledge, building projects, and growing together.
            </p>

            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                            <Button @click="router.push('/register')" 
                      class="bg-white text-primary hover:bg-gray-100 px-10 py-4 text-lg font-semibold rounded-full shadow-2xl transform hover:scale-105 transition-all duration-300">
                <Rocket class="w-5 h-5 mr-2" />
                Join Community Free
              </Button>
              <Button variant="outline" @click="router.push('/projects')"
                      class="border-2 border-white text-white hover:bg-white hover:text-primary px-10 py-4 text-lg font-semibold rounded-full transition-all duration-300 hover:scale-105">
                <Eye class="w-5 h-5 mr-2" />
                Browse Projects
              </Button>
            </div>

            <!-- Social Proof -->
            <div class="mt-12 flex justify-center items-center space-x-8 text-white/80">
              <div class="flex items-center">
                <Users class="w-5 h-5 mr-2" />
                <span class="font-semibold">25,000+ Developers</span>
              </div>
              <div class="flex items-center">
                <Star class="w-5 h-5 mr-2" />
                <span class="font-semibold">4.9/5 Rating</span>
              </div>
              <div class="flex items-center">
                <Globe2 class="w-5 h-5 mr-2" />
                <span class="font-semibold">180+ Countries</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Custom animations */
@keyframes float {

  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-20px);
  }
}

@keyframes pulse-glow {

  0%,
  100% {
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
  }

  50% {
    box-shadow: 0 0 40px rgba(239, 68, 68, 0.8);
  }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}

/* Grid background */
.bg-grid-white\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

.bg-grid-black\/\[0\.05\] {
  background-image: radial-gradient(circle, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom gradient text */
.gradient-text {
  background: linear-gradient(135deg, #ef4444, #f97316, #eab308);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Hover effects */
.hover-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Card hover effects */
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

/* Button animations */
.btn-bounce:hover {
  animation: bounce 0.6s ease-in-out;
}

@keyframes bounce {

  0%,
  20%,
  60%,
  100% {
    transform: translateY(0);
  }

  40% {
    transform: translateY(-10px);
  }

  80% {
    transform: translateY(-5px);
  }
}
</style>