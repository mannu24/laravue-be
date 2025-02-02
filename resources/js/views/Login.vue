<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import { useThemeStore } from '../stores/theme.js';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Mail, Lock } from 'lucide-vue-next';

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
};
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <Card class="w-full max-w-md">
      <CardHeader>
        <CardTitle class="text-2xl font-bold text-center">Sign in to Laravue</CardTitle>
        <CardDescription class="text-center">Enter your email to receive an OTP</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="isOtpSent ? handleOtpVerification() : handleOtpRequest()" class="space-y-4">
          <div v-if="!isOtpSent" class="space-y-2">
            <label for="email-address" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email
              address</label>
            <div class="relative">
              <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size="18" />
              <Input id="email-address" name="email" type="email" required v-model="loginData.email" class="pl-10"
                placeholder="Enter your email" />
            </div>
          </div>

          <div v-if="isOtpSent" class="space-y-2">
            <label for="otp" class="text-sm font-medium text-gray-700 dark:text-gray-300">OTP</label>
            <div class="relative">
              <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size="18" />
              <Input id="otp" name="otp" type="text" required v-model="loginData.otp" class="pl-10"
                placeholder="Enter OTP" />
            </div>
          </div>

          <div v-if="errorMessage" class="text-red-500 text-sm">
            {{ errorMessage }}
          </div>

          <Button type="submit" :disabled="btn === 'loading'" class="w-full">
            {{ isOtpSent ? 'Verify OTP' : 'Send OTP' }}
          </Button>
        </form>
      </CardContent>
      <CardFooter class="flex justify-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Don't have an account?
          <a href="#"
            class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
            Sign up
          </a>
        </p>
      </CardFooter>
    </Card>
  </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>