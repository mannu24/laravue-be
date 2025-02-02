<script setup lang="ts">
import { ref } from 'vue';
import { useThemeStore } from '../stores/theme';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Textarea } from '../components/ui/textarea';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../components/ui/card';
import { Mail, Twitter, Github } from 'lucide-vue-next';

const themeStore = useThemeStore();

const contactData = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const handleSubmit = () => {
  console.log('Contact form submitted:', contactData.value);
  // Add your form submission logic here
};
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Contact Us</h1>

      <Card>
        <CardHeader>
          <CardTitle>Get in Touch</CardTitle>
          <CardDescription>Fill out the form below and we'll get back to you as soon as possible.</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="space-y-2">
              <label for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
              <Input type="text" id="name" v-model="contactData.name" required placeholder="Your name" />
            </div>

            <div class="space-y-2">
              <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
              <Input type="email" id="email" v-model="contactData.email" required placeholder="your@email.com" />
            </div>

            <div class="space-y-2">
              <label for="subject" class="text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
              <Input type="text" id="subject" v-model="contactData.subject" required placeholder="What's this about?" />
            </div>

            <div class="space-y-2">
              <label for="message" class="text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
              <Textarea id="message" v-model="contactData.message" rows="4" required
                placeholder="Your message here..." />
            </div>

            <Button type="submit" class="w-full">
              Send Message
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card class="mt-12">
        <CardHeader>
          <CardTitle>Other Ways to Reach Us</CardTitle>
          <CardDescription>You can also contact us through these channels:</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <Mail class="h-5 w-5 text-gray-500 dark:text-gray-400" />
              <p class="text-gray-700 dark:text-gray-300">
                <span class="font-medium text-gray-900 dark:text-white">Email:</span> support@LaraVue.com
              </p>
            </div>
            <div class="flex items-center space-x-3">
              <Twitter class="h-5 w-5 text-gray-500 dark:text-gray-400" />
              <p class="text-gray-700 dark:text-gray-300">
                <span class="font-medium text-gray-900 dark:text-white">Twitter:</span> @LaraVue
              </p>
            </div>
            <div class="flex items-center space-x-3">
              <Github class="h-5 w-5 text-gray-500 dark:text-gray-400" />
              <p class="text-gray-700 dark:text-gray-300">
                <span class="font-medium text-gray-900 dark:text-white">GitHub:</span> github.com/LaraVue
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>