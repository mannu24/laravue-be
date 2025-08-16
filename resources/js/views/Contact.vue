<script setup lang="ts">
import { CheckCircle, ExternalLink, Github, Loader2, Mail, MessageSquare, Phone, Send, Twitter, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Textarea } from '../components/ui/textarea';
import { useThemeStore } from '../stores/theme';

const themeStore = useThemeStore();
const contactData = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const isSubmitting = ref(false);
const isSubmitted = ref(false);
const formError = ref('');

const handleSubmit = () => {
  isSubmitting.value = true;
  formError.value = '';

  // Simulate API call
  setTimeout(() => {
    console.log('Contact form submitted:', contactData.value);
    isSubmitting.value = false;
    isSubmitted.value = true;

    // Reset form after 3 seconds
    setTimeout(() => {
      isSubmitted.value = false;
      contactData.value = {
        name: '',
        email: '',
        subject: '',
        message: ''
      };
    }, 3000);
  }, 1500);
};

const faqs = [
  {
    question: 'How quickly do you respond to inquiries?',
    answer: 'We typically respond to all inquiries within 24-48 hours during business days.'
  },
  {
    question: 'Do you offer technical support?',
    answer: 'Yes, we provide technical support for all our Laravel projects and services.'
  }
];

const officeHours = [
  { day: 'Monday - Friday', hours: '9:00 AM - 6:00 PM EST' },
  { day: 'Saturday', hours: '10:00 AM - 2:00 PM EST' },
  { day: 'Sunday', hours: 'Closed' }
];
</script>

<template>
  <div :class="['min-h-screen transition-colors duration-300',
    themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
  ]">
    <!-- Hero Section with Background -->
    <div class="relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 -z-10">
        <div :class="['absolute inset-0 transition-all duration-500',
          themeStore.isDark
            ? 'bg-gradient-to-br from-gray-900 via-red-900/10 to-gray-900'
            : 'bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50'
        ]"></div>

        <!-- Grid Pattern -->
        <div :class="['absolute inset-0 opacity-20',
          themeStore.isDark ? 'bg-grid-white/[0.05]' : 'bg-grid-black/[0.05]'
        ]"
          style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 30px 30px;">
        </div>
      </div>

      <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
          <h1 class="text-5xl font-extrabold tracking-tight mb-6">
            <span :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Get in </span>
            <span class="bg-gradient-to-r from-red-500 via-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Touch
            </span>
          </h1>
          <p class="text-xl mb-8 leading-relaxed" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
            Have questions about Laravel development or our community? We're here to help!
            Fill out the form below and our team will get back to you as soon as possible.
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-24 -mt-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div>
          <Card :class="['overflow-hidden transition-all duration-300 border-0 shadow-xl',
            themeStore.isDark ? 'bg-gray-800/90 backdrop-blur-sm' : 'bg-white/90 backdrop-blur-sm'
          ]">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-red-500 to-orange-500"></div>

            <CardHeader class="pb-6">
              <div class="flex items-center mb-2">
                <div
                  class="w-10 h-10 rounded-full bg-gradient-to-r from-red-500 to-orange-500 flex items-center justify-center mr-3">
                  <MessageSquare class="h-5 w-5 text-white" />
                </div>
                <CardTitle class="text-2xl" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  Send Us a Message
                </CardTitle>
              </div>
              <CardDescription :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                We'd love to hear from you! Fill out the form below and we'll respond promptly.
              </CardDescription>
            </CardHeader>

            <CardContent>
              <div v-if="isSubmitted"
                class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
                <CheckCircle class="h-12 w-12 text-green-500 mx-auto mb-4" />
                <h3 class="text-xl font-semibold mb-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  Message Sent Successfully!
                </h3>
                <p :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                  Thank you for reaching out. We'll get back to you as soon as possible.
                </p>
              </div>

              <form v-else @submit.prevent="handleSubmit" class="space-y-6">
                <div class="space-y-1.5">
                  <label for="name" class="text-sm font-medium"
                    :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                    Full Name
                  </label>
                  <Input type="text" id="name" v-model="contactData.name" required placeholder="Your name"
                    :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-200'"
                    class="transition-all duration-300 focus:ring-2 focus:ring-red-500 focus:border-red-500" />
                </div>

                <div class="space-y-1.5">
                  <label for="email" class="text-sm font-medium"
                    :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                    Email Address
                  </label>
                  <Input type="email" id="email" v-model="contactData.email" required placeholder="your@email.com"
                    :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-200'"
                    class="transition-all duration-300 focus:ring-2 focus:ring-red-500 focus:border-red-500" />
                </div>

                <div class="space-y-1.5">
                  <label for="subject" class="text-sm font-medium"
                    :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                    Subject
                  </label>
                  <Input type="text" id="subject" v-model="contactData.subject" required
                    placeholder="What's this about?"
                    :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-200'"
                    class="transition-all duration-300 focus:ring-2 focus:ring-red-500 focus:border-red-500" />
                </div>

                <div class="space-y-1.5">
                  <label for="message" class="text-sm font-medium"
                    :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                    Your Message
                  </label>
                  <Textarea id="message" v-model="contactData.message" rows="5" required
                    placeholder="Please describe how we can help you..."
                    :class="themeStore.isDark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-200'"
                    class="transition-all duration-300 focus:ring-2 focus:ring-red-500 focus:border-red-500" />
                </div>

                <div v-if="formError" class="text-red-500 text-sm">
                  {{ formError }}
                </div>

                <Button type="submit"
                  class="w-full bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white py-6 text-lg font-medium"
                  :disabled="isSubmitting">
                  <Loader2 v-if="isSubmitting" class="h-5 w-5 mr-2 animate-spin" />
                  <Send v-else class="h-5 w-5 mr-2" />
                  {{ isSubmitting ? 'Sending...' : 'Send Message' }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>

        <!-- Contact Info & FAQs -->
        <div class="space-y-8">
          <!-- Contact Information -->
          <Card :class="['overflow-hidden transition-all duration-300 border-0 shadow-xl',
            themeStore.isDark ? 'bg-gray-800/90 backdrop-blur-sm' : 'bg-white/90 backdrop-blur-sm'
          ]">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-500 to-cyan-500"></div>

            <CardHeader class="pb-6">
              <div class="flex items-center mb-2">
                <div
                  class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                  <Users class="h-5 w-5 text-white" />
                </div>
                <CardTitle class="text-2xl" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                  Connect With Us
                </CardTitle>
              </div>
              <CardDescription :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                Reach out through any of these channels
              </CardDescription>
            </CardHeader>

            <CardContent>
              <div class="space-y-6">
                <div :class="['flex items-start p-4 rounded-lg transition-all duration-300',
                  themeStore.isDark ? 'bg-gray-700/50 hover:bg-gray-700' : 'bg-gray-50 hover:bg-gray-100'
                ]">
                  <div
                    class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center mr-4 flex-shrink-0">
                    <Mail class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                  </div>
                  <div>
                    <h3 class="font-medium mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      Email Us
                    </h3>
                    <p class="text-sm mb-2" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                      For general inquiries and support
                    </p>
                    <a href="mailto:support@LaraVue.com"
                      class="text-yellow-600 dark:text-yellow-400 hover:underline flex items-center">
                      support@LaraVue.com
                      <ExternalLink class="h-3 w-3 ml-1" />
                    </a>
                  </div>
                </div>
                <div :class="['flex items-start p-4 rounded-lg transition-all duration-300',
                  themeStore.isDark ? 'bg-gray-700/50 hover:bg-gray-700' : 'bg-gray-50 hover:bg-gray-100'
                ]">
                  <div
                    class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center mr-4 flex-shrink-0">
                    <Phone class="h-5 w-5 text-blue-700 dark:text-blue-300" />
                  </div>
                  <div>
                    <h3 class="font-medium mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      Phone
                    </h3>
                    <p class="text-sm mb-2" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                      For urgent matters and direct support
                    </p>
                    <a href="tel:+1-555-123-4567"
                      class="text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                      +1 (555) 123-4567
                      <ExternalLink class="h-3 w-3 ml-1" />
                    </a>
                  </div>
                </div>
                <div :class="['flex items-start p-4 rounded-lg transition-all duration-300',
                  themeStore.isDark ? 'bg-gray-700/50 hover:bg-gray-700' : 'bg-gray-50 hover:bg-gray-100'
                ]">
                  <div
                    class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center mr-4 flex-shrink-0">
                    <Twitter class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                  </div>
                  <div>
                    <h3 class="font-medium mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      Twitter
                    </h3>
                    <p class="text-sm mb-2" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                      Follow us for updates and quick responses
                    </p>
                    <a href="https://twitter.com/LaraVue" target="_blank" rel="noopener noreferrer"
                      class="text-purple-600 dark:text-purple-400 hover:underline flex items-center">
                      @LaraVue
                      <ExternalLink class="h-3 w-3 ml-1" />
                    </a>
                  </div>
                </div>

                <div :class="['flex items-start p-4 rounded-lg transition-all duration-300',
                  themeStore.isDark ? 'bg-gray-700/50 hover:bg-gray-700' : 'bg-gray-50 hover:bg-gray-100'
                ]">
                  <div
                    class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center mr-4 flex-shrink-0">
                    <Github class="h-5 w-5 text-green-600 dark:text-green-400" />
                  </div>
                  <div>
                    <h3 class="font-medium mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                      GitHub
                    </h3>
                    <p class="text-sm mb-2" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                      Contribute to our open source projects
                    </p>
                    <a href="https://github.com/LaraVue" target="_blank" rel="noopener noreferrer"
                      class="text-green-600 dark:text-green-400 hover:underline flex items-center">
                      github.com/LaraVue
                      <ExternalLink class="h-3 w-3 ml-1" />
                    </a>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

        </div>
      </div>
      <!-- FAQs -->
      <Card :class="['overflow-hidden transition-all duration-300 border-0 shadow-xl mt-16',
        themeStore.isDark ? 'bg-gray-800/90 backdrop-blur-sm' : 'bg-white/90 backdrop-blur-sm'
      ]">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-purple-500 to-pink-500"></div>

        <CardHeader class="pb-6">
          <div class="flex items-center mb-2">
            <div
              class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center mr-3">
              <HelpCircle class="h-5 w-5 text-white" />
            </div>
            <CardTitle class="text-2xl" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
              Frequently Asked Questions
            </CardTitle>
          </div>
        </CardHeader>

        <CardContent>
          <div class="space-y-4">
            <div v-for="(faq, index) in faqs" :key="index" :class="['p-4 rounded-lg transition-all duration-300',
              themeStore.isDark ? 'bg-gray-700/50' : 'bg-gray-50'
            ]">
              <h3 class="font-semibold mb-2 flex items-start"
                :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                <span class="text-purple-500 dark:text-purple-400 mr-2">Q:</span>
                {{ faq.question }}
              </h3>
              <p class="pl-5" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                {{ faq.answer }}
              </p>
            </div>
          </div>

          <div class="mt-6 text-center">
            <Button variant="link"
              class="text-purple-500 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300">
              View all FAQs
              <ArrowRight class="h-4 w-4 ml-1" />
            </Button>
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

/* Pulse animation for map pin */
@keyframes pulse-slow {

  0%,
  100% {
    opacity: 0.6;
    transform: scale(1);
  }

  50% {
    opacity: 0.9;
    transform: scale(1.1);
  }
}

.animate-pulse-slow {
  animation: pulse-slow 2s ease-in-out infinite;
}

/* Smooth transitions */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Card hover effects */
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
  transform: translateY(-5px);
}
</style>