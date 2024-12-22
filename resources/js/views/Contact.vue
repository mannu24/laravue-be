<script setup>
import { ref } from 'vue';

const contactData = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const handleSubmit = () => {
  console.log('Contact form submitted:', contactData.value)
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold text-white mb-8">Contact Us</h1>

      <div class="bg-gray-800 rounded-lg p-6">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="name" class="block text-sm font-medium text-white">Name</label>
            <input type="text" id="name" v-model="contactData.name" required
              class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-white">Email</label>
            <input type="email" id="email" v-model="contactData.email" required
              class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
          </div>

          <div>
            <label for="subject" class="block text-sm font-medium text-white">Subject</label>
            <input type="text" id="subject" v-model="contactData.subject" required
              class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
          </div>

          <div>
            <label for="message" class="block text-sm font-medium text-white">Message</label>
            <textarea id="message" v-model="contactData.message" rows="4" required
              class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white"></textarea>
          </div>

          <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
            Send Message
          </button>
        </form>

        <div class="mt-12 border-t border-gray-700 pt-8">
          <h2 class="text-xl font-semibold text-white mb-4">Other Ways to Reach Us</h2>
          <div class="space-y-4 text-gray-300">
            <p>
              <strong class="text-white">Email:</strong> support@LaraVue.com
            </p>
            <p>
              <strong class="text-white">Twitter:</strong> @LaraVue
            </p>
            <p>
              <strong class="text-white">GitHub:</strong> github.com/LaraVue
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>