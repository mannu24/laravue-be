<script setup>
import { ref } from 'vue'
import { useThemeStore } from '../stores/theme'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Code2,
  Users,
  Zap,
  Heart,
  Star,
  Globe,
  Target,
  Rocket,
  ArrowRight,
  CheckCircle,
  Github,
  Twitter,
  Mail
} from 'lucide-vue-next'

const themeStore = useThemeStore()

const stats = [
  { label: 'Active Developers', value: '10,000+', icon: Users },
  { label: 'Projects Shared', value: '5,000+', icon: Code2 },
  { label: 'Community Stars', value: '50,000+', icon: Star },
  { label: 'Countries', value: '80+', icon: Globe },
]

const features = [
  {
    title: 'Share Your Work',
    description: 'Showcase your projects to a global audience of developers and get valuable feedback.',
    icon: Rocket,
  },
  {
    title: 'Monetize Expertise',
    description: 'Turn your skills into income by selling premium projects and solutions.',
    icon: Zap,
  },
  {
    title: 'Open Collaboration',
    description: 'Contribute to open source projects and collaborate with talented developers.',
    icon: Heart,
  },
  {
    title: 'Quality Assurance',
    description: 'Discover high-quality, vetted projects from experienced developers.',
    icon: Target,
  },
]

const values = [
  'Innovation through collaboration',
  'Quality over quantity',
  'Inclusive developer community',
  'Open source first mindset',
  'Continuous learning and growth',
  'Sustainable development practices'
]
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300 space-y-12',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 -z-10">
        <div :class="['absolute inset-0 transition-all duration-500',
          themeStore.isDark
            ? 'bg-gradient-to-br from-gray-900 via-primary/10 to-gray-900'
            : 'bg-gradient-to-br from-primary/5 via-secondary/5 to-primary/5'
        ]"></div>

        <!-- Grid Pattern -->
        <div :class="['absolute inset-0 opacity-20',
          themeStore.isDark ? 'bg-grid-white/[0.05]' : 'bg-grid-black/[0.05]'
        ]"
          style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 30px 30px;">
        </div>
      </div>

      <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <div class="text-center max-w-4xl mx-auto">
          <div class="flex items-center justify-center mb-6">
            <div
              class="w-16 h-16 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center shadow-2xl">
              <Code2 class="h-8 w-8 text-primary-foreground" />
            </div>
          </div>

          <h1 class="text-6xl font-extrabold tracking-tight mb-8">
            <span :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">About </span>
            <span class="bg-gradient-to-r from-primary via-primary/80 to-secondary bg-clip-text text-transparent">
              LaraVue
            </span>
          </h1>

          <p class="text-xl mb-8 leading-relaxed" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            LaraVue is more than just a platform—it's a thriving ecosystem where developers come together
            to share, discover, and collaborate on amazing projects. Whether you're looking to showcase your work,
            find quality open-source projects, or sell your premium solutions, LaraVue provides the perfect
            space for the developer community.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Button
              class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground px-8 py-3 text-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300"
              @click="$router.push('/signup')">
              <Users class="h-5 w-5 mr-2" />
              Join Our Community
            </Button>
            <Button variant="outline"
              class="px-8 py-3 text-lg font-medium border-2 hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300"
              @click="$router.push('/projects')">
              Explore Projects
              <ArrowRight class="h-5 w-5 ml-2" />
            </Button>
          </div>
        </div>
      </div>
    </div>
    <!-- Stats Section -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <Card v-for="stat in stats" :key="stat.label" :class="['text-center transition-all duration-300 hover:scale-105 border-0 shadow-xl backdrop-blur-sm',
        themeStore.isDark ? 'bg-gray-800/90' : 'bg-white/90'
      ]">
        <CardContent class="p-6">
          <div class="flex justify-center mb-3">
            <div
              class="w-12 h-12 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center">
              <component :is="stat.icon" class="h-6 w-6 text-primary-foreground" />
            </div>
          </div>
          <div
            class="text-3xl font-bold mb-2 bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-transparent">
            {{ stat.value }}
          </div>
          <div class="text-sm font-medium" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
            {{ stat.label }}
          </div>
        </CardContent>
      </Card>
    </div>
    <!-- Mission Section -->
    <div class="max-w-3xl mx-auto space-y-12">
      <div class="text-center mb-10">
        <h2 class="text-4xl font-bold mb-6" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
          Our Mission
        </h2>
        <p class="text-xl max-w-3xl mx-auto leading-relaxed text-justify"
          :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
          We believe in fostering a vibrant developer ecosystem where creativity meets opportunity.
          Our mission is to provide a platform that empowers developers to reach their full potential.
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <Card v-for="feature in features" :key="feature.title" :class="['group transition-all duration-300 hover:-translate-y-2 border-0 shadow-xl',
          themeStore.isDark ? 'bg-gray-800/90' : 'bg-white/90'
        ]">
          <CardHeader>
            <div class="flex items-center mb-4">
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                <component :is="feature.icon" class="h-6 w-6 text-primary-foreground" />
              </div>
              <CardTitle class="text-xl" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ feature.title }}
              </CardTitle>
            </div>
            <CardDescription class="text-base" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              {{ feature.description }}
            </CardDescription>
          </CardHeader>
        </Card>
      </div>
    </div>
    <div class="max-w-7xl mx-auto space-y-12">
      <Card :class="['relative overflow-hidden border-0 shadow-2xl ',
        themeStore.isDark ? 'bg-gray-800' : 'bg-white'
      ]">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-primary via-primary/80 to-secondary"></div>
        <div class="py-12 px-4">
          <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-6" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              Our Values
            </h2>
            <p class="text-xl max-w-3xl mx-auto" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
              These core principles guide everything we do at LaraVue.
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <div v-for="value in values" :key="value" :class="['flex items-center p-4 rounded-lg transition-all duration-300 hover:scale-105',
              themeStore.isDark ? 'bg-gray-700/50' : 'bg-white/50'
            ]">
              <CheckCircle class="h-6 w-6 text-primary mr-3 flex-shrink-0" />
              <span class="font-medium" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ value }}
              </span>
            </div>
          </div>
        </div>
      </Card>
    </div>
    <div class="max-w-3xl mx-auto space-y-12">
      <Card :class="['relative overflow-hidden border-0 shadow-2xl mb-12',
        themeStore.isDark ? 'bg-gray-800' : 'bg-white'
      ]">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-primary via-primary/80 to-secondary"></div>
  
        <CardContent class="p-12 text-center">
          <h2 class="text-2xl md:text-3xl font-bold mb-6" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            Ready to Join Our Community?
          </h2>
          <p class="text-base md:text-lg tracking-wide mb-8 max-w-2xl mx-auto" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Whether you're a seasoned developer or just starting out, LaraVue welcomes you to join our
            growing community. Share your projects, contribute to others' work, and be part of something bigger.
          </p>
  
          <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
            <Button
              class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground px-8 py-3 text-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300"
              @click="$router.push('/signup')">
              <Rocket class="h-5 w-5 mr-2" />
              Get Started Today
            </Button>
            <Button variant="outline" class="px-8 py-3 text-lg font-medium border-2" @click="$router.push('/contact')">
              <Mail class="h-5 w-5 mr-2" />
              Contact Us
            </Button>
          </div>
  
          <div class="flex justify-center space-x-6">
            <a href="#" class="text-gray-400 hover:text-primary transition-colors duration-300">
              <Github class="h-6 w-6" />
            </a>
            <a href="#" class="text-gray-400 hover:text-secondary transition-colors duration-300">
              <Twitter class="h-6 w-6" />
            </a>
            <a href="#" class="text-gray-400 hover:text-primary/80 transition-colors duration-300">
              <Mail class="h-6 w-6" />
            </a>
          </div>
        </CardContent>
      </Card>
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
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>