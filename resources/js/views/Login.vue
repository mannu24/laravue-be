<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

const router = useRouter();
const authStore = useAuthStore();
const btn = ref('false');

const loginData = ref({
  email: '',
  otp: '',
});

const isOtpSent = ref(false);
const errorMessage = ref('');

const handleOtpRequest = async () => {
  btn.value = 'loading';
  try {
    const response = await axios.post('/api/v1/auth/otp', { email: loginData.value.email });
    isOtpSent.value = true;
    btn.value = false
    errorMessage.value = '';
  } catch (error) {
    btn.value = false
    errorMessage.value = error.response?.data?.message || 'Failed to send OTP.';
  }
};

const handleOtpVerification = async () => {
  // try {
  btn.value = 'loading';
  const response = await axios.post('/api/v1/auth/otp', {
    email: loginData.value.email,
    otp: loginData.value.otp
  });
  btn.value = false

  const data = response.data.data;

  const { token, user } = data;

  authStore.setAuthData(token, user);

  errorMessage.value = '';

  if (response.data.is_new) {
    router.push('/profile/' + response.data?.user?.email)
  } else {
    router.push('/')
  }
  // } catch (error) {
  //   btn.value = false
  //   errorMessage.value = error.response?.data?.message || 'OTP verification failed.';
  // }
};
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-bold text-white">
          Sign in to Laravue
        </h2>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="isOtpSent ? handleOtpVerification() : handleOtpRequest()">
        <div class="rounded-md shadow-sm -space-y-px">
          <div v-if="!isOtpSent">
            <label for="email-address" class="sr-only">Email address</label>
            <input id="email-address" name="email" type="email" required v-model="loginData.email"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-700 bg-gray-800 text-white rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Email address">
          </div>

          <div v-if="isOtpSent">
            <label for="otp" class="sr-only">OTP</label>
            <input id="otp" name="otp" type="text" required v-model="loginData.otp"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-700 bg-gray-800 text-white rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Enter OTP">
          </div>
        </div>

        <div v-if="errorMessage" class="text-red-500 text-sm">
          {{ errorMessage }}
        </div>

        <div>
          <button type="submit"
            :class="btn == 'loading' ? 'text-gray-300 cursor-wait bg-primary-700' : 'text-white bg-primary-600 text-gray-700'"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:shadow-lg shadow-white">
            {{ isOtpSent ? 'Verify OTP' : 'Send OTP' }}
          </button>
        </div>
        <!-- 
        <div class="text-center">
          <router-link to="/signup" class="text-primary-400 hover:text-primary-300">
            Don't have an account? Sign up
          </router-link>
        </div> -->
      </form>
    </div>
  </div>
</template>
