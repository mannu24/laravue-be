<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import { useThemeStore } from '../stores/theme.js';
import CustomInput from "../components/elements/CustomInput.vue";
import { PinInput, PinInputGroup, PinInputSlot, PinInputSeparator } from '../../../src/components/ui/pin-input';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay } from 'swiper/modules';
import 'swiper/css';
import {
    Code2,
    Users,
    Trophy,
    MessageSquare,
    FileText,
    Sparkles,
    ArrowRight,
    CheckCircle2
} from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();
const btn = ref(false);
const errorMessage = ref('');
const googleReady = ref(false);
const loginData = ref({
    email: authStore.otpEmail || '',
    otp: [],
});

const OTP_LENGTH = 6;

// ─── Timer (driven from store, survives refresh) ───
let timerInterval = null;
const timerDisplay = ref('');

const isOtpSent = computed(() => authStore.isOtpPending);
const canResend = computed(() => authStore.otpResendRemaining === 0);

const tickTimer = () => {
    const remaining = authStore.otpResendRemaining;
    if (remaining <= 0) {
        timerDisplay.value = '';
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        return;
    }
    const min = String(Math.floor(remaining / 60)).padStart(2, '0');
    const sec = String(remaining % 60).padStart(2, '0');
    timerDisplay.value = `${min}:${sec}`;
};

const startTimerTick = () => {
    if (timerInterval) clearInterval(timerInterval);
    tickTimer(); // immediate first tick
    timerInterval = setInterval(tickTimer, 1000);
};

// ─── Email validation ───
const isValidEmail = computed(() => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(loginData.value.email);
});

// ─── OTP input helpers ───
const enforceNumericInput = (event) => {
    const key = event.key || '';
    if (!/[0-9]/.test(key) && event.keyCode !== 13) {
        event.preventDefault();
    }
};

const handleOtpPaste = (event) => {
    event.preventDefault();
    const pastedText = (event.clipboardData || window.clipboardData)?.getData('text') || '';
    const digits = pastedText.replace(/\D/g, '').slice(0, OTP_LENGTH).split('');
    if (!digits.length) return;

    const normalized = Array(OTP_LENGTH).fill('');
    digits.forEach((char, index) => { normalized[index] = char; });
    loginData.value.otp = normalized;

    if (digits.length === OTP_LENGTH) {
        handleComplete(normalized.join(''));
    }
    nextTick(() => {
        const focusIndex = Math.min(digits.length, OTP_LENGTH - 1);
        document.getElementById(`pinInputRef_${focusIndex}`)?.focus();
    });
};

const otp = computed(() => loginData.value.otp.join(''));

const handleComplete = (value) => {
    loginData.value.otp = Array.isArray(value) ? value : String(value).split('');
};

// ─── OTP Request ───
const handleOtpRequest = async () => {
    if (!isValidEmail.value) {
        errorMessage.value = 'Please enter a valid email address.';
        return;
    }
    btn.value = 'loading';
    errorMessage.value = '';
    try {
        await axios.post('/api/v1/auth/otp', { email: loginData.value.email });
        authStore.setOtpSent(loginData.value.email);
        btn.value = false;
        loginData.value.otp = [];
        startTimerTick();
        nextTick(() => {
            setTimeout(() => {
                document.getElementById('pinInputRef_0')?.focus();
            }, 500);
        });
    } catch (error) {
        btn.value = false;
        const status = error.response?.status;
        if (status === 429) {
            errorMessage.value = 'Too many attempts. Please wait a moment before trying again.';
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to send OTP.';
        }
    }
};

// ─── OTP Verification ───
const handleOtpVerification = async () => {
    if (otp.value.length !== OTP_LENGTH) {
        errorMessage.value = 'Please enter the complete 6-digit code.';
        return;
    }
    btn.value = 'loading';
    errorMessage.value = '';
    try {
        const response = await axios.post('/api/v1/auth/otp', {
            email: loginData.value.email,
            otp: otp.value
        });
        btn.value = false;
        if (response.data.status === 'success') {
            const { token, user, is_new } = response.data.data;
            authStore.setAuthData(token, user);
            router.push(is_new ? '/dashboard' : '/');
        } else {
            errorMessage.value = response.data.message || 'Verification failed.';
        }
    } catch (error) {
        btn.value = false;
        const status = error.response?.status;
        if (status === 429) {
            errorMessage.value = 'Too many attempts. Please wait before trying again.';
        } else if (status === 422) {
            errorMessage.value = error.response?.data?.message || 'Invalid or expired OTP.';
        } else {
            errorMessage.value = error.response?.data?.message || 'Verification failed.';
        }
    }
};

// ─── Edit email (go back) ───
const editEmail = () => {
    authStore.clearOtpState();
    loginData.value.otp = [];
    errorMessage.value = '';
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    timerDisplay.value = '';
};

// ─── Google Sign-In ───
const googleLoading = ref(false);

const initGoogleSignIn = () => {
    if (typeof google === 'undefined' || !google.accounts) {
        googleReady.value = false;
        return;
    }
    const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID || import.meta.env.VITE_GOOGLE_SIGNIN;
    if (!clientId) {
        googleReady.value = false;
        return;
    }
    try {
        google.accounts.id.initialize({
            client_id: clientId,
            callback: handleGoogleCallback,
        });
        // Render a hidden button — we trigger it from our custom button
        const hiddenContainer = document.getElementById('google-signin-hidden');
        if (hiddenContainer) {
            google.accounts.id.renderButton(hiddenContainer, {
                type: 'icon',
                size: 'large',
            });
        }
        googleReady.value = true;
    } catch (e) {
        console.warn('[GoogleSignIn] Failed to initialize:', e);
        googleReady.value = false;
    }
};

const triggerGoogleSignIn = () => {
    // Click the hidden Google button to trigger the popup
    const hiddenBtn = document.querySelector('#google-signin-hidden div[role="button"]');
    if (hiddenBtn) {
        hiddenBtn.click();
    } else {
        // Fallback: use prompt
        try {
            google.accounts.id.prompt();
        } catch (e) {
            errorMessage.value = 'Google sign-in is not available right now.';
        }
    }
};

const handleGoogleCallback = async (response) => {
    if (!response?.credential) {
        errorMessage.value = 'Google sign-in failed. Please try again.';
        return;
    }
    googleLoading.value = true;
    errorMessage.value = '';
    try {
        const res = await axios.post('/api/v1/auth/google', {
            credential: response.credential,
        });
        googleLoading.value = false;
        if (res.data.status === 'success') {
            const { token, user, is_new } = res.data.data;
            authStore.setAuthData(token, user);
            router.push(is_new ? '/dashboard' : '/');
        } else {
            errorMessage.value = res.data.message || 'Google sign-in failed.';
        }
    } catch (error) {
        googleLoading.value = false;
        errorMessage.value = error.response?.data?.message || 'Google sign-in failed. Please try again.';
    }
};

// ─── Features data ───
const features = [
    { icon: Code2, title: 'Showcase Your Work', description: 'Share your creations with a community that values innovation and quality.' },
    { icon: Trophy, title: 'Earn Recognition', description: 'Build your reputation through meaningful contributions and achievements.' },
    { icon: MessageSquare, title: 'Connect & Learn', description: 'Engage in meaningful discussions and exchange knowledge with peers.' },
    { icon: FileText, title: 'Share Knowledge', description: 'Help others grow while building your own expertise and network.' },
    { icon: Sparkles, title: 'Unlock Rewards', description: 'Progress through levels and unlock exclusive benefits as you participate.' },
    { icon: Users, title: 'Build Community', description: 'Join a vibrant ecosystem of passionate developers and creators.' },
];

const featureSlides = computed(() => {
    const slides = [];
    for (let i = 0; i < features.length; i += 2) {
        slides.push(features.slice(i, i + 2));
    }
    return slides;
});

// ─── Lifecycle ───
onMounted(() => {
    // Restore email from store if OTP was already sent
    if (authStore.otpEmail) {
        loginData.value.email = authStore.otpEmail;
    }
    // Resume timer if still counting down
    if (authStore.otpResendRemaining > 0) {
        startTimerTick();
    }
    // Init Google (with retry for slow script load)
    initGoogleSignIn();
    if (!googleReady.value) {
        const retryTimer = setTimeout(() => initGoogleSignIn(), 1500);
        onBeforeUnmount(() => clearTimeout(retryTimer));
    }
});

onBeforeUnmount(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
});
</script>

<template>
    <div class="min-h-[90vh]">
        <div class="flex flex-col lg:flex-row gap-6 py-4 lg:py-16">
            <!-- Left Side: Overview Card (60% width) -->
            <div class="lg:w-[60%]">
                <div class="relative overflow-hidden rounded-2xl border p-6 lg:p-8 shadow-xl transition-all duration-300"
                     :class="themeStore.isDark
                        ? 'bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 border-white/10'
                        : 'bg-white/95 border-gray-200/50 backdrop-blur-sm'">
                    <div class="absolute inset-0 bg-gradient-to-tr from-vue/5 via-transparent to-laravel/5 pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="mb-6">
                            <h1 class="text-center md:text-left text-2xl lg:text-3xl font-bold mb-3 bg-gradient-to-r from-vue to-laravel bg-clip-text text-transparent">
                                Welcome to Laravue
                            </h1>
                            <p class="text-sm lg:text-base leading-relaxed"
                               :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                A vibrant community platform where developers connect, create, and grow together.
                                Join thousands of passionate creators building the future of web development.
                            </p>
                        </div>

                        <!-- Features Carousel (mobile) -->
                        <div class="md:hidden mb-6">
                            <Swiper
                                :slides-per-view="1"
                                :space-between="16"
                                class="pb-8"
                                :loop="true"
                                :modules="[Autoplay]"
                                :autoplay="{ delay: 2500, disableOnInteraction: false }"
                            >
                                <SwiperSlide v-for="(group, slideIndex) in featureSlides" :key="`feature-slide-${slideIndex}`">
                                    <div class="space-y-4">
                                        <div v-for="feature in group" :key="feature.title"
                                             class="group relative overflow-hidden rounded-xl border p-4 transition-all duration-300 hover:-translate-y-0.5"
                                             :class="themeStore.isDark
                                                ? 'bg-gray-800/50 border-white/10 hover:border-vue/30 hover:shadow-lg hover:shadow-vue/10'
                                                : 'bg-white/60 border-gray-200/50 hover:border-vue/30 hover:shadow-lg hover:shadow-vue/10'">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-vue/20 to-laravel/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                                    <component :is="feature.icon" class="w-5 h-5 text-vue"/>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-sm font-semibold mb-1.5" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ feature.title }}</h3>
                                                    <p class="text-xs leading-relaxed" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">{{ feature.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </SwiperSlide>
                            </Swiper>
                        </div>

                        <!-- Features Grid (desktop) -->
                        <div class="hidden md:grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div v-for="(feature, index) in features" :key="index"
                                 class="group relative overflow-hidden rounded-xl border p-4 transition-all duration-300 hover:-translate-y-0.5"
                                 :class="themeStore.isDark
                                    ? 'bg-gray-800/50 border-white/10 hover:border-vue/30 hover:shadow-lg hover:shadow-vue/10'
                                    : 'bg-white/60 border-gray-200/50 hover:border-vue/30 hover:shadow-lg hover:shadow-vue/10'">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-vue/20 to-laravel/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <component :is="feature.icon" class="w-5 h-5 text-vue"/>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold mb-1.5" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">{{ feature.title }}</h3>
                                        <p class="text-xs leading-relaxed" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">{{ feature.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t"
                             :class="themeStore.isDark ? 'border-white/10' : 'border-gray-200/50'">
                            <div class="flex items-center gap-1.5" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                <CheckCircle2 class="w-4 h-4 text-vue" /><span class="text-xs font-medium">Secure authentication</span>
                            </div>
                            <div class="flex items-center gap-1.5" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                <CheckCircle2 class="w-4 h-4 text-vue" /><span class="text-xs font-medium">Free to join</span>
                            </div>
                            <div class="flex items-center gap-1.5" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                <CheckCircle2 class="w-4 h-4 text-vue" /><span class="text-xs font-medium">Start in seconds</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Card (40% width) -->
            <div class="lg:w-[40%]">
                <div class="lg:sticky lg:top-8">
                    <transition name="fade" mode="out-in">
                        <!-- Email Step -->
                        <form v-if="!isOtpSent"
                              @submit.prevent="handleOtpRequest()"
                              class="relative overflow-hidden w-full text-center rounded-2xl border p-6 lg:p-8 transition-all duration-300 shadow-xl"
                              :class="themeStore.isDark
                                ? 'bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 border-white/10'
                                : 'bg-white/95 border-gray-200/50 backdrop-blur-sm'">
                            <div class="absolute inset-0 bg-gradient-to-tl from-vue/5 via-transparent to-laravel/5 pointer-events-none"></div>
                            <div class="relative z-10">
                                <header class="mb-6">
                                    <h1 class="text-2xl font-bold mb-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Sign In</h1>
                                </header>

                                <div class="mb-4">
                                    <CustomInput
                                        type="text"
                                        placeholder="Email address"
                                        v-model="loginData.email"
                                        class="w-full" />
                                </div>

                                <div v-if="errorMessage" class="text-red-500 text-sm mb-4 text-left">{{ errorMessage }}</div>

                                <div class="space-y-3">
                                    <button
                                        type="submit"
                                        :disabled="btn === 'loading' || !isValidEmail"
                                        :class="[
                                            btn === 'loading' || !isValidEmail ? 'cursor-not-allowed bg-vue/50 opacity-60' : 'bg-vue hover:bg-vue/90',
                                        ]"
                                        class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-vue/20 focus:outline-none focus:ring-2 focus:ring-vue/40">
                                        <span v-if="btn === 'loading'">Sending...</span>
                                        <span v-else class="flex items-center gap-2">Continue <ArrowRight class="w-4 h-4" /></span>
                                    </button>

                                    <div class="relative my-4">
                                        <div class="absolute inset-0 flex items-center">
                                            <div class="w-full border-t" :class="themeStore.isDark ? 'border-white/10' : 'border-gray-200'"></div>
                                        </div>
                                        <div class="relative flex justify-center text-xs uppercase">
                                            <span :class="themeStore.isDark ? 'text-gray-500 bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 px-2' : 'text-gray-500 bg-white/95 px-2'">Or</span>
                                        </div>
                                    </div>

                                    <div id="google-signin-hidden" class="absolute w-0 h-0 overflow-hidden opacity-0 pointer-events-none"></div>
                                    <button
                                        v-if="googleReady"
                                        type="button"
                                        @click="triggerGoogleSignIn"
                                        :disabled="googleLoading"
                                        class="w-full inline-flex items-center justify-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200 border"
                                        :class="themeStore.isDark
                                            ? 'bg-white/5 border-white/15 text-gray-200 hover:bg-white/10 hover:border-white/25 active:bg-white/15'
                                            : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400 active:bg-gray-100 shadow-sm'">
                                        <svg v-if="!googleLoading" class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                        </svg>
                                        <svg v-else class="w-5 h-5 flex-shrink-0 animate-spin" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span>{{ googleLoading ? 'Signing in...' : 'Continue with Google' }}</span>
                                    </button>
                                    <p v-else class="text-xs text-center" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">
                                        Google Sign-In unavailable
                                    </p>
                                </div>
                            </div>
                        </form>

                        <!-- OTP Step -->
                        <form v-else
                              id="otp-form"
                              @submit.prevent="handleOtpVerification()"
                              class="relative overflow-hidden w-full text-center rounded-2xl border p-6 lg:p-8 transition-all duration-300 shadow-xl"
                              :class="themeStore.isDark
                                ? 'bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 border-white/10'
                                : 'bg-white/95 border-gray-200/50 backdrop-blur-sm'">
                            <div class="absolute inset-0 bg-gradient-to-tr from-vue/5 via-transparent to-laravel/5 pointer-events-none"></div>
                            <div class="relative z-10">
                                <header class="mb-6">
                                    <h1 class="text-2xl font-bold mb-2" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">Verify Email</h1>
                                    <p class="text-sm mb-3" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">Enter the code sent to</p>
                                    <div class="flex items-center justify-center gap-2 mb-4">
                                        <span class="text-sm font-medium text-vue">{{ loginData.email }}</span>
                                        <button type="button" @click="editEmail"
                                                class="text-xs underline hover:no-underline transition-all"
                                                :class="themeStore.isDark ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'">
                                            Change
                                        </button>
                                    </div>
                                </header>

                                <div class="flex items-center justify-center mb-6">
                                    <PinInput
                                        id="pin-input"
                                        v-model="loginData.otp"
                                        placeholder=""
                                        @complete="handleComplete"
                                        @paste.prevent="handleOtpPaste"
                                        mask
                                    >
                                        <PinInputGroup class="flex flex-nowrap justify-center gap-2">
                                            <template v-for="(id, index) in 6" :key="id">
                                                <PinInputSlot
                                                    :id="`pinInputRef_${index}`"
                                                    type="password"
                                                    inputmode="numeric"
                                                    pattern="[0-9]*"
                                                    maxlength="1"
                                                    @keypress="enforceNumericInput"
                                                    class="rounded-xl border-2 w-9 h-9 text-lg sm:text-xl font-semibold transition-all duration-200"
                                                    :class="themeStore.isDark
                                                        ? 'text-white bg-gray-800/50 border-white/20 hover:border-vue/60 hover:bg-gray-700/50 focus:bg-gray-700 focus:border-vue focus:ring-4 focus:ring-vue/20 shadow-lg'
                                                        : 'text-gray-900 bg-white border-gray-300 hover:border-vue/60 hover:bg-gray-50 focus:bg-white focus:border-vue focus:ring-4 focus:ring-vue/20 shadow-md'"
                                                    :index="index"
                                                />
                                                <template v-if="index !== 5">
                                                    <PinInputSeparator class="w-2" />
                                                </template>
                                            </template>
                                        </PinInputGroup>
                                    </PinInput>
                                </div>

                                <div v-if="errorMessage" class="text-red-500 text-sm mb-4">{{ errorMessage }}</div>

                                <button
                                    type="submit"
                                    :disabled="btn === 'loading' || otp.length !== 6"
                                    :class="btn === 'loading' || otp.length !== 6 ? 'cursor-not-allowed bg-vue/50 opacity-60' : 'bg-vue hover:bg-vue/90'"
                                    class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-vue/20 focus:outline-none focus:ring-2 focus:ring-vue/40 mb-4">
                                    <span v-if="btn === 'loading'">Verifying...</span>
                                    <span v-else>Verify & Continue</span>
                                </button>

                                <div class="text-xs" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                    Didn't receive code?
                                    <button
                                        v-if="canResend"
                                        type="button"
                                        @click="handleOtpRequest"
                                        :disabled="btn === 'loading'"
                                        class="font-semibold text-vue hover:text-laravel transition-colors">
                                        Resend
                                    </button>
                                    <span v-else class="font-semibold text-vue">
                                        Resend in {{ timerDisplay }}
                                    </span>
                                </div>
                            </div>
                        </form>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>
