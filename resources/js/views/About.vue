<script setup>
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
  Mail,
  Compass,
  Eye,
  Share2,
  Layers,
  Sparkles,
  CalendarDays
} from 'lucide-vue-next'

const themeStore = useThemeStore()

const stats = [
  { label: 'Active Developers', value: '10,000+', icon: Users },
  { label: 'Projects Shared', value: '5,000+', icon: Code2 },
  { label: 'Community Stars', value: '50,000+', icon: Star },
  { label: 'Countries', value: '80+', icon: Globe }
]

const missionHighlights = [
  {
    title: 'Share Your Work',
    description: 'Showcase your builds to a global audience and collect meaningful feedback.',
    icon: Share2
  },
  {
    title: 'Monetize Expertise',
    description: 'Unlock premium tools to sell templates, components, and consulting.',
    icon: Zap
  },
  {
    title: 'Open Collaboration',
    description: 'Contribute to OSS missions with curated bounties and contributor spotlights.',
    icon: Heart
  },
  {
    title: 'Quality Assurance',
    description: 'Discover vetted starter kits and production-ready resources.',
    icon: Target
  }
]

const pillars = [
  {
    title: 'Innovation through collaboration',
    icon: Sparkles,
    copy: 'Pair builders across time zones to co-create, critique, and celebrate every launch.'
  },
  {
    title: 'Quality over quantity',
    icon: Layers,
    copy: 'Curated reviews ensure our marketplace feels handcrafted—not crowded.'
  },
  {
    title: 'Inclusive developer community',
    icon: Users,
    copy: 'Mentorship circles and scholarships keep the ladder down for new makers.'
  },
  {
    title: 'Open source first mindset',
    icon: Code2,
    copy: 'We reinvest platform revenue into OSS maintenance and contributor grants.'
  },
  {
    title: 'Continuous learning and growth',
    icon: CalendarDays,
    copy: 'Weekly AMAs, sprints, and teardown sessions sharpen everyone’s craft.'
  },
  {
    title: 'Sustainable development practices',
    icon: Heart,
    copy: 'Thoughtful defaults and guides help teams ship accessible, maintainable products.'
  }
]

const journey = [
  {
    year: '2019',
    title: 'Community roots',
    copy: 'A handful of Laravel and Vue creators started weekly project drops. Those meetups became LaraVue.'
  },
  {
    year: '2021',
    title: 'Launch of project marketplace',
    copy: 'We introduced verified listings so makers can sell premium components and agencies can source talent.'
  },
  {
    year: '2023',
    title: 'Learning tracks & badges',
    copy: 'Gamified achievements, office hours, and peer-led cohorts made shipping together even more rewarding.'
  },
  {
    year: 'Today',
    title: 'Global collective',
    copy: '10k+ members ship OSS, mentor juniors, run hackathons, and build sustainable businesses together.'
  }
]
</script>

<template>
  <div class="min-h-screen space-y-10 py-5">
    <!-- Hero -->
    <section class="relative overflow-hidden rounded-lg bg-gradient-to-br from-gray-50 via-white to-primary/10 dark:from-slate-900 dark:via-slate-900/90 dark:to-primary/20 border border-gray-200 dark:border-white/5 shadow-[0_35px_120px_rgba(15,23,42,0.15)] dark:shadow-[0_35px_120px_rgba(15,23,42,0.55)]">
      <div class="absolute inset-0 opacity-30 pointer-events-none">
        <div class="absolute -top-20 -right-16 w-80 h-80 bg-primary/20 dark:bg-primary/40 blur-[120px]"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-secondary/20 dark:bg-secondary/30 blur-[120px]"></div>
      </div>
      <div class="relative px-6 sm:px-10 lg:px-16 py-14 text-center max-w-5xl mx-auto">
        <div class="inline-flex items-center gap-3 px-5 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-300 dark:border-white/10 text-sm font-semibold uppercase tracking-widest text-gray-700 dark:text-white mb-8">
          <span class="w-8 h-8 rounded-full bg-primary/20 dark:bg-primary/30 flex items-center justify-center">
            <Code2 class="w-4 h-4 text-primary" />
          </span>
          Building the world's friendliest dev guild
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white leading-tight mb-6">
          Where Laravel craftsmanship meets Vue artistry
        </h1>
        <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-300 leading-relaxed mb-10">
          We're a modern guild for makers who ship ambitious products with Laravel, Vue, and Tailwind. Learn faster, collaborate on OSS, sell premium resources, and celebrate every release together.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Button class="bg-gradient-to-r from-primary to-primary/80 text-white shadow-2xl shadow-primary/50" @click="$router.push('/signup')">
            <Users class="h-5 w-5 mr-2" />
            Join the community
          </Button>
          <Button variant="outline" class="border-gray-300 dark:border-white/30 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10" @click="$router.push('/projects')">
            Explore Projects
            <ArrowRight class="h-5 w-5 ml-2" />
          </Button>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <Card
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white dark:bg-card/80 backdrop-blur border border-gray-200 dark:border-white/5 shadow-lg hover:shadow-primary/20 hover:-translate-y-1 transition-all duration-300"
      >
        <CardContent class="p-5 flex flex-col items-center text-center gap-2">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center mb-2">
            <component :is="stat.icon" class="w-6 h-6 text-primary" />
          </div>
          <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-sm uppercase tracking-wide text-gray-600 dark:text-gray-400">{{ stat.label }}</p>
        </CardContent>
      </Card>
    </section>

    <!-- Mission & Vision -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <Card class="lg:col-span-2 bg-white dark:bg-card border border-gray-200 dark:border-white/5 shadow-xl">
        <CardHeader class="space-y-3">
          <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center text-primary">
              <Compass class="w-5 h-5" />
            </span>
            <div>
              <CardTitle class="text-2xl text-gray-900 dark:text-white">Our Mission</CardTitle>
              <CardDescription class="text-base text-gray-600 dark:text-gray-400">
                Empower every builder to go from idea to launch with a community that turns feedback into fuel.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent class="grid sm:grid-cols-2 gap-5">
          <Card
            v-for="feature in missionHighlights"
            :key="feature.title"
            class="bg-gradient-to-br from-gray-50 to-white dark:from-white/5 dark:to-white/0 border border-gray-200 dark:border-white/5 shadow-inner hover:border-primary/40 transition-all duration-300"
          >
            <CardHeader class="space-y-3">
              <div class="w-11 h-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <component :is="feature.icon" class="w-5 h-5" />
              </div>
              <CardTitle class="text-lg text-gray-900 dark:text-white">{{ feature.title }}</CardTitle>
              <CardDescription class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ feature.description }}
              </CardDescription>
            </CardHeader>
          </Card>
        </CardContent>
      </Card>
      <Card class="relative overflow-hidden bg-gradient-to-br from-gray-50 via-white to-primary/10 dark:from-slate-900 dark:via-slate-900/80 dark:to-primary/20 border border-gray-200 dark:border-white/5 shadow-2xl">
        <div class="absolute inset-0 opacity-30 pointer-events-none">
          <!-- <div class="absolute -top-20 -right-16 w-80 h-80 bg-primary/40 blur-[120px]"></div> -->
          <div class="absolute bottom-0 left-0 w-72 h-72 bg-secondary/20 dark:bg-secondary/30 blur-[120px]"></div>
        </div>
        <CardHeader class="space-y-3">
          <div class="flex items-center gap-3">
            <!-- <span class="text-primary">
              <Eye class="w-5 h-5" />
            </span> -->
            <div>
              <CardTitle class="text-2xl text-gray-900 dark:text-white">Our Vision</CardTitle>
              <CardDescription class="text-base text-gray-700 dark:text-gray-300">
                A global builder network where knowledge flows freely, revenue is shared fairly, and open source wins by default.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="rounded-2xl border border-gray-300 dark:border-white/10 p-4 text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
            "We're designing LaraVue to be the most generous place on the internet for Laravel + Vue makers. Your launches, learnings, and lessons shouldn't be siloed. They should become someone else's superpower."
          </div>
          <div class="flex flex-col gap-3">
            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
              <CheckCircle class="text-primary w-5 h-5" />
              Zero-gatekeeping mentorship culture
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
              <CheckCircle class="text-primary w-5 h-5" />
              Verified marketplace for premium kits
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
              <CheckCircle class="text-primary w-5 h-5" />
              Community-backed funding for OSS
            </div>
          </div>
        </CardContent>
      </Card>
    </section>

    <!-- Values -->
    <section class="bg-white dark:bg-card/60 border border-gray-200 dark:border-white/5 rounded-lg p-8 shadow-2xl">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-primary">DNA</p>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">The values we refuse to compromise</h2>
          <p class="text-gray-600 dark:text-gray-400 mt-2 max-w-3xl">
            Everything we launch is filtered through these principles. If it doesn't serve creators, mentor new builders, or push open source forward—it doesn't ship.
          </p>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <Card
          v-for="value in pillars"
          :key="value.title"
          class="bg-gradient-to-br from-gray-50 to-white dark:from-white/5 dark:to-white/0 border border-gray-200 dark:border-white/5 shadow-inner hover:border-primary/40 transition-all duration-300"
        >
          <CardContent class="flex items-start gap-4 p-5">
            <div class="w-11 h-11 rounded-2xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
              <component :is="value.icon" class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ value.title }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ value.copy || 'We obsess over tiny details so community members can obsess over outcomes.' }}
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </section>

    <!-- Journey -->
    <section class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <Card
        v-for="step in journey"
        :key="step.year"
        class="bg-white dark:bg-card/80 backdrop-blur border border-gray-200 dark:border-white/5 shadow-lg hover:shadow-primary/20 hover:-translate-y-1 transition-all duration-300"
      >
        <CardContent class="p-6 space-y-3">
          <p class="text-sm uppercase tracking-[0.25em] text-primary">{{ step.year }}</p>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ step.title }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ step.copy }}</p>
        </CardContent>
      </Card>
    </section>

    <!-- CTA -->
    <section class="max-w-4xl mx-auto">
      <Card class="bg-gradient-to-br from-primary/15 via-primary/5 to-transparent dark:from-primary/15 dark:via-primary/5 dark:to-transparent border border-primary/30 dark:border-primary/30 shadow-2xl">
        <CardContent class="p-10 text-center space-y-6">
          <p class="text-sm uppercase tracking-[0.35em] text-primary">Let's ship together</p>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ready to join the guild?</h2>
          <p class="text-lg text-gray-700 dark:text-gray-300">
            Partner with creative Laravel and Vue makers, earn badges for what you ship, join private AMAs, and get paid to collaborate on projects that matter.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Button class="bg-primary text-white px-8 py-3 shadow-lg shadow-primary/40" @click="$router.push('/signup')">
              <Rocket class="h-5 w-5 mr-2" />
              Start Shipping
            </Button>
            <Button variant="outline" class="border-gray-300 dark:border-white/40 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10" @click="$router.push('/contact')">
              <Mail class="h-5 w-5 mr-2" />
              Contact us
            </Button>
          </div>
          <div class="flex justify-center gap-6 text-gray-600 dark:text-gray-400">
            <a href="#" class="hover:text-primary transition"><Github class="w-5 h-5" /></a>
            <a href="#" class="hover:text-secondary transition"><Twitter class="w-5 h-5" /></a>
            <a href="#" class="hover:text-primary/80 transition"><Mail class="w-5 h-5" /></a>
          </div>
        </CardContent>
      </Card>
    </section>
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