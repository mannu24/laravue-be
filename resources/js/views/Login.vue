<script setup>
import { computed, nextTick, onMounted, ref, getCurrentInstance } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import { useThemeStore } from '../stores/theme.js';
import CustomInput from "../components/elements/CustomInput.vue";
import { PinInput, PinInputGroup, PinInputSlot, PinInputSeparator } from '../../../src/components/ui/pin-input';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay } from 'swiper/modules';
import 'swiper/css';
import { jwtDecode } from "jwt-decode";
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
const btn = ref('false');
const isOtpSent = ref(false);
const timer = ref(null);
const timer_html = ref('');
const errorMessage = ref('');
const loginData = ref({
    email: '',
    otp: [],
});

const OTP_LENGTH = 6;
const { proxy } = getCurrentInstance();

const enforceNumericInput = (event) => {
    if (proxy?.validate) {
        proxy.validate(event);
        return;
    }

    const key = event.key || '';
    if (!/[0-9]/.test(key) && event.keyCode !== 13) {
        event.preventDefault();
    }
};

const handleOtpPaste = (event) => {
    event.preventDefault();
    const pastedText = (event.clipboardData || window.clipboardData)?.getData('text') || '';
    const digits = pastedText.replace(/\D/g, '').slice(0, OTP_LENGTH).split('');

    if (!digits.length) {
        return;
    }

    const normalized = Array(OTP_LENGTH).fill('');
    digits.forEach((char, index) => {
        normalized[index] = char;
    });

    loginData.value.otp = normalized;

    if (digits.length === OTP_LENGTH) {
        handleComplete(normalized.join(''));
    }

    nextTick(() => {
        const focusIndex = Math.min(digits.length, OTP_LENGTH - 1);
        document.getElementById(`pinInputRef_${focusIndex}`)?.focus();
    });
};

const features = [
    {
        icon: Code2,
        title: 'Showcase Your Work',
        description: 'Share your creations with a community that values innovation and quality.'
    },
    {
        icon: Trophy,
        title: 'Earn Recognition',
        description: 'Build your reputation through meaningful contributions and achievements.'
    },
    {
        icon: MessageSquare,
        title: 'Connect & Learn',
        description: 'Engage in meaningful discussions and exchange knowledge with peers.'
    },
    {
        icon: FileText,
        title: 'Share Knowledge',
        description: 'Help others grow while building your own expertise and network.'
    },
    {
        icon: Sparkles,
        title: 'Unlock Rewards',
        description: 'Progress through levels and unlock exclusive benefits as you participate.'
    },
    {
        icon: Users,
        title: 'Build Community',
        description: 'Join a vibrant ecosystem of passionate developers and creators.'
    }
];

const featureSlides = computed(() => {
    const slides = [];
    for (let i = 0; i < features.length; i += 2) {
        slides.push(features.slice(i, i + 2));
    }
    return slides;
});

const init_timer = () => {
    var sec = 119;
    timer.value = setInterval(function () {
        var minutes = Math.floor(sec / 60);
        var seconds = sec % 60;
        var formattedMinutes = String(minutes).padStart(2, '0');
        var formattedSeconds = String(seconds).padStart(2, '0');
        timer_html.value = formattedMinutes + ':' + formattedSeconds;
        sec--;
        if (sec < 0) {
            clearInterval(timer.value);
            timer.value = null
        }
    }, 1000);
}

const otp = computed(() => {
    return loginData.value.otp.join('');
})

const handleComplete = (value) => {
    loginData.value.otp = Array.isArray(value) ? value : String(value).split('');
}

const handleOtpRequest = async () => {
    btn.value = 'loading';
    try {
        await axios.post('/api/v1/auth/otp', { email: loginData.value.email });
        isOtpSent.value = true;
        btn.value = false;
        errorMessage.value = '';
        loginData.value.otp = [];
        nextTick(() => {
            setTimeout(() => {
                init_timer();
            }, 100);
            setTimeout(() => {
                document.getElementById(`pinInputRef_0`)?.focus();
            }, 1000);
        });
    } catch (error) {
        btn.value = false;
        errorMessage.value = error.response?.data?.message || 'Failed to send OTP.';
    }
};

const edit_email = () => {
    isOtpSent.value = false;
    loginData.value.otp = [];
}

const handleOtpVerification = async () => {
    btn.value = 'loading';
    try {
        const response = await axios.post('/api/v1/auth/otp', {
            email: loginData.value.email,
            otp: otp.value
        });
        btn.value = false;
        if (response.data.status == 'success') {
            const data = response.data.data;
            const { token, user } = data;
            authStore.setAuthData(token, user);
            errorMessage.value = '';
            if (response.data.data.is_new) {
                router.push('/dashboard')
            } else {
                router.push('/')
            }
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        btn.value = false;
        errorMessage.value = error.response?.data?.message || 'Failed to verify OTP.';
    }
};

const handleGoogleCallback = (response) => {
    const decoded = jwtDecode(response.credential);
    console.log(decoded);
    // form.value.name = decoded.name;
    // form.value.email = decoded.email;
    // form.value.profile_id = decoded.jti.substring(0, 8);
    // form.value.image_path = decoded.picture;
    // form.value.profile_type = 'google';
};


onMounted(() => {
    google.accounts.id.initialize({
        client_id: import.meta.env.VITE_GOOGLE_SIGNIN,
        callback: handleGoogleCallback,
    });

    google.accounts.id.renderButton(
        document.getElementById("google-signin-btn"),
        { theme: "outline", size: "large" }
    );
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
                    <!-- Background gradient overlay -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-vue/5 via-transparent to-laravel/5 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <!-- Header -->
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
                                    :autoplay="{
                                        delay: 2500,
                                        disableOnInteraction: false,
                                    }"
                                >
                                    <SwiperSlide v-for="(group, slideIndex) in featureSlides" :key="`feature-slide-${slideIndex}`">
                                        <div class="space-y-4">
                                            <div
                                                v-for="feature in group"
                                                :key="feature.title"
                                                class="group relative overflow-hidden rounded-xl border p-4 transition-all duration-300 hover:-translate-y-0.5"
                                                :class="themeStore.isDark 
                                                    ? 'bg-gray-800/50 border-white/10 hover:border-vue/30 hover:shadow-lg hover:shadow-vue/10' 
                                                    : 'bg-white/60 border-gray-200/50 hover:border-vue/30 hover:shadow-lg hover:shadow-vue/10'"
                                            >
                                                <div class="flex items-start gap-3">
                                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-vue/20 to-laravel/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                                        <component :is="feature.icon" class="w-5 h-5" :class="themeStore.isDark ? 'text-vue' : 'text-vue'"/>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="text-sm font-semibold mb-1.5" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                                                            {{ feature.title }}
                                                        </h3>
                                                        <p class="text-xs leading-relaxed" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                                            {{ feature.description }}
                                                        </p>
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
                                            <component :is="feature.icon" 
                                                       class="w-5 h-5"
                                                       :class="themeStore.isDark ? 'text-vue' : 'text-vue'"/>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold mb-1.5"
                                                :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                                                {{ feature.title }}
                                            </h3>
                                            <p class="text-xs leading-relaxed"
                                               :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                                {{ feature.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Call to Action -->
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t"
                             :class="themeStore.isDark ? 'border-white/10' : 'border-gray-200/50'">
                            <div class="flex items-center gap-1.5"
                                 :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                <CheckCircle2 class="w-4 h-4 text-vue" />
                                <span class="text-xs font-medium">Secure authentication</span>
                            </div>
                            <div class="flex items-center gap-1.5"
                                 :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                <CheckCircle2 class="w-4 h-4 text-vue" />
                                <span class="text-xs font-medium">Free to join</span>
                            </div>
                            <div class="flex items-center gap-1.5"
                                 :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                                <CheckCircle2 class="w-4 h-4 text-vue" />
                                <span class="text-xs font-medium">Start in seconds</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Side: Login Card (40% width) -->
            <div class="lg:w-[40%]">
                <div class="lg:sticky lg:top-8">
                    <transition name="fade" mode="out-in">
                        <form v-if="!isOtpSent" 
                              @submit.prevent="handleOtpRequest()" 
                              class="relative overflow-hidden w-full text-center rounded-2xl border p-6 lg:p-8 transition-all duration-300 shadow-xl"
                              :class="themeStore.isDark 
                                ? 'bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 border-white/10' 
                                : 'bg-white/95 border-gray-200/50 backdrop-blur-sm'">
                            <!-- Background gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-tl from-vue/5 via-transparent to-laravel/5 pointer-events-none"></div>
                            
                            <div class="relative z-10">
                            <header class="mb-6">
                                <h1 class="text-2xl font-bold mb-2"
                                    :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                                    Sign In
                                </h1>
                                <!-- <p class="text-sm"
                                   :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                    Enter your email to continue
                                </p> -->
                            </header>
                            
                            <div class="mb-4">
                                <CustomInput 
                                    type="text" 
                                    placeholder="Email address" 
                                    v-model="loginData.email"
                                    class="w-full" />
                            </div>
                            
                            <div v-if="errorMessage" class="text-red-500 text-sm mb-4 text-left">
                                {{ errorMessage }}
                            </div>
                            
                            <div class="space-y-3">
                                <button 
                                    type="submit" 
                                    :disabled="btn == 'loading'"
                                    :class="btn == 'loading' ? 'cursor-wait bg-vue/70 opacity-50' : 'bg-vue hover:bg-vue/90'" 
                                    class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-vue/20 focus:outline-none focus:ring-2 focus:ring-vue/40">
                                    <span v-if="btn == 'loading'">Sending...</span>
                                    <span v-else class="flex items-center gap-2">
                                        Continue
                                        <ArrowRight class="w-4 h-4" />
                                    </span>
                                </button>
                                
                                <div class="relative my-4">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full border-t"
                                             :class="themeStore.isDark ? 'border-white/10' : 'border-gray-200'"></div>
                                    </div>
                                    <div class="relative flex justify-center text-xs uppercase">
                                        <span :class="themeStore.isDark ? 'text-gray-500 bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 px-2' : 'text-gray-500 bg-white/95 px-2'">
                                            Or
                                        </span>
                                    </div>
                                </div>
                                
                                <div id="google-signin-btn" class="flex justify-center"></div>
                            </div>
                            </div>
                        </form>
                        <form v-else 
                              id="otp-form" 
                              @submit.prevent="handleOtpVerification()" 
                              class="relative overflow-hidden w-full text-center rounded-2xl border p-6 lg:p-8 transition-all duration-300 shadow-xl"
                              :class="themeStore.isDark 
                                ? 'bg-gradient-to-br from-gray-900/95 via-gray-800/90 to-gray-900/95 border-white/10' 
                                : 'bg-white/95 border-gray-200/50 backdrop-blur-sm'">
                            <!-- Background gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-vue/5 via-transparent to-laravel/5 pointer-events-none"></div>
                            
                            <div class="relative z-10">
                            <header class="mb-6">
                                <h1 class="text-2xl font-bold mb-2"
                                    :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                                    Verify Email
                                </h1>
                                <p class="text-sm mb-3"
                                   :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                    Enter the code sent to
                                </p>
                                <div class="flex items-center justify-center gap-2 mb-4">
                                    <span class="text-sm font-medium"
                                          :class="themeStore.isDark ? 'text-vue' : 'text-vue'">
                                        {{ loginData.email }}
                                    </span>
                                    <button 
                                        type="button"
                                        @click="edit_email"
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
                            
                            <div v-if="errorMessage" class="text-red-500 text-sm mb-4">
                                {{ errorMessage }}
                            </div>
                            
                            <button 
                                type="submit" 
                                :class="btn == 'loading' ? 'cursor-wait bg-vue/70' : 'bg-vue hover:bg-vue/90'" 
                                class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-vue/20 focus:outline-none focus:ring-2 focus:ring-vue/40 mb-4">
                                <span v-if="btn == 'loading'">Verifying...</span>
                                <span v-else>Verify & Continue</span>
                            </button>
                            
                            <div class="text-xs"
                                 :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                Didn't receive code?
                                <button 
                                    v-if="!timer" 
                                    type="button"
                                    @click="handleOtpRequest"
                                    class="font-semibold text-vue hover:text-laravel transition-colors">
                                    Resend
                                </button>
                                <span v-else class="font-semibold text-vue">
                                    Resend in {{ timer_html }}
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
