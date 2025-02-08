<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import CustomInput from "../components/elements/CustomInput.vue";
import { jwtDecode } from "jwt-decode";

const router = useRouter();
const authStore = useAuthStore();
const btn = ref('false');
const isOtpSent = ref(false);
const timer = ref(null);
const timer_html = ref('');
const errorMessage = ref('');
const loginData = ref({
    email: '',
    otp: [],
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

const init_form = () => {
    const form = document.getElementById('otp-form')
    const inputs = [...form.querySelectorAll('input[type=password]')]
    const submit = form.querySelector('button[type=submit]')

    const handleKeyDown = (e) => {
        if (
            !/^[0-9]{1}$/.test(e.key)
            && e.key !== 'Backspace'
            && e.key !== 'Delete'
            && e.key !== 'Tab'
            && !e.metaKey
        ) {
            e.preventDefault()
        }

        if (e.key === 'Delete' || e.key === 'Backspace') {
            const index = inputs.indexOf(e.target);
            if (index > 0) {
                inputs[index - 1].value = '';
                inputs[index - 1].focus();
            }
        }
    }

    const handleInput = (e) => {
        const { target } = e
        const index = inputs.indexOf(target)
        if (target.value) {
            if (index < inputs.length - 1) {
                inputs[index + 1].focus()
            } else {
                submit.focus()
            }
        }
    }

    const handleFocus = (e) => {
        e.target.select()
    }

    const handlePaste = (e) => {
        e.preventDefault()
        const text = e.clipboardData.getData('text')
        if (!new RegExp(`^[0-9]{${inputs.length}}$`).test(text)) {
            return
        }
        const digits = text.split('')
        inputs.forEach((input, index) => input.value = digits[index])
        submit.focus()
    }

    inputs.forEach((input) => {
        input.addEventListener('input', handleInput)
        input.addEventListener('keydown', handleKeyDown)
        input.addEventListener('focus', handleFocus)
        input.addEventListener('paste', handlePaste)
    })
};

const handleOtpRequest = async () => {
    btn.value = 'loading';
    await axios.post('/api/v1/auth/otp', { email: loginData.value.email }).then(() => {
        isOtpSent.value = true;
        btn.value = false
        errorMessage.value = '';
        setTimeout(() => {
            init_form()
            init_timer()
            
        }, 500);
    }).catch((error) => {
        btn.value = false
        errorMessage.value = error.response?.data?.message || 'Failed to send OTP.';
    });
};

const edit_email = () => {
    isOtpSent.value = false;
    loginData.value.otp = [];
}

const handleOtpVerification = async () => {
    btn.value = 'loading';
    await axios.post('/api/v1/auth/otp', {
        email: loginData.value.email,
        otp: otp.value
    }).then((response) => {
        btn.value = false
        if (response.data.status == 'success') {
            const data = response.data.data;
            const { token, user } = data;
            authStore.setAuthData(token, user);
            errorMessage.value = '';
            if (response.data.data.is_new) {
                router.push('/profile/@' + response.data.data.user.username)
            } else {
                router.push('/')
            }
        } else errorMessage.value = response.data.message;
    }).catch((error) => {
        btn.value = false
        errorMessage.value = error.response?.data?.message || 'Failed to send OTP.';
    });
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
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full mx-auto py-24">
            <div class="flex justify-center">
                <transition name="fade" mode="out-in">
                    <form v-if="!isOtpSent" @submit.prevent="handleOtpRequest()" class="w-full max-w-xl mx-auto text-center bg-gray-100 dark:bg-gray-900 px-8 py-10 rounded-xl transition-all duration-300 shadow-md hover:shadow-vue dark:shadow-none">
                        <header class="mb-8">
                            <h1 class="text-2xl font-bold mb-3 text-slate-600 dark:text-white">Sign in to Laravue</h1>
                            <p class="text-[15px] text-slate-400">We will need your email to send the verification code.</p>
                        </header>
                        <div class="flex items-center justify-center gap-3">
                            <CustomInput type="text" placeholder="Write Email Here..." v-model="loginData.email" />
                        </div>
                        <div v-if="errorMessage" class="text-red-500 text-sm">
                            {{ errorMessage }}
                        </div>
                        <div class="max-w-[260px] mx-auto mt-4 space-y-3">
                            <button type="submit" :class="btn == 'loading' ? 'cursor-wait bg-vue/70' : 'bg-vue/90 hover:bg-vue'" class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-3.5 py-2.5 text-sm font-medium text-white shadow-sm shadow-vue/10 focus:outline-none focus:bg-vue focus:ring focus:ring-vue/40 focus-visible:outline-none focus-visible:ring focus-visible:ring-vue/40">Get OTP</button>
                            <div><b class="text-lg text-dark dark:text-white">OR</b></div>
                            <div id="google-signin-btn"></div>
                        </div>
                    </form>
                    <form v-else id="otp-form" @submit.prevent="handleOtpVerification()" class="w-full max-w-xl mx-auto text-center bg-gray-100 dark:bg-gray-900 px-8 py-10 rounded-xl transition-all duration-300 shadow-md hover:shadow-vue dark:shadow-none">
                        <header class="mb-8">
                            <h1 class="text-2xl font-bold mb-3 text-slate-600 dark:text-white">Email Verification</h1>
                            <p class="text-[15px] text-slate-400">Enter the 4-digit verification code that was sent to <br>
                                <span class="flex gap-2 items-center justify-center mt-1 hover:text-slate-800 dark:hover:text-white cursor-pointer" @click="edit_email">
                                    <b>"{{ loginData.email }}"</b>
                                    <i class="fas fa-pencil-alt"></i>
                                </span>
                            </p>
                        </header>
                        <div class="flex items-center justify-center gap-3">
                            <input type="password" v-model="loginData.otp[0]" class="w-12 h-12 text-center text-2xl font-extrabold dark:text-gray-100 dark:bg-gray-700 border border-transparent dark:hover:border-slate-200 appearance-none rounded p-4 outline-none dark:focus:bg-gray-600 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                            <input type="password" v-model="loginData.otp[1]" class="w-12 h-12 text-center text-2xl font-extrabold dark:text-gray-100 dark:bg-gray-700 border border-transparent dark:hover:border-slate-200 appearance-none rounded p-4 outline-none dark:focus:bg-gray-600 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                            <input type="password" v-model="loginData.otp[2]" class="w-12 h-12 text-center text-2xl font-extrabold dark:text-gray-100 dark:bg-gray-700 border border-transparent dark:hover:border-slate-200 appearance-none rounded p-4 outline-none dark:focus:bg-gray-600 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                            <input type="password" v-model="loginData.otp[3]" class="w-12 h-12 text-center text-2xl font-extrabold dark:text-gray-100 dark:bg-gray-700 border border-transparent dark:hover:border-slate-200 appearance-none rounded p-4 outline-none dark:focus:bg-gray-600 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                            <input type="password" v-model="loginData.otp[4]" class="w-12 h-12 text-center text-2xl font-extrabold dark:text-gray-100 dark:bg-gray-700 border border-transparent dark:hover:border-slate-200 appearance-none rounded p-4 outline-none dark:focus:bg-gray-600 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                            <input type="password" v-model="loginData.otp[5]" class="w-12 h-12 text-center text-2xl font-extrabold dark:text-gray-100 dark:bg-gray-700 border border-transparent dark:hover:border-slate-200 appearance-none rounded p-4 outline-none dark:focus:bg-gray-600 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                        </div>
                        <div v-if="errorMessage" class="text-laravel/80 text-sm mt-3">
                            {{ errorMessage }}
                        </div>
                        <div class="max-w-[260px] mx-auto mt-4">
                            <button type="submit" :class="btn == 'loading' ? 'cursor-wait bg-vue/70' : 'bg-vue/90 hover:bg-vue'" class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-3.5 py-2.5 text-sm font-medium text-white shadow-sm shadow-vue/10 focus:outline-none focus:bg-vue focus:ring focus:ring-vue/40 focus-visible:outline-none focus-visible:ring focus-visible:ring-vue/40">Verify Account</button>
                        </div>
                        <div class="text-sm text-slate-400 mt-4">Didn't receive code?
                            <span v-if="!timer" class="font-medium text-vue hover:text-laravel/80 cursor-pointer"
                                @click="handleOtpRequest">Resend</span>
                            <span v-else class="font-medium text-vue">Resend in {{ timer_html }}</span>
                        </div>
                    </form>
                </transition>
            </div>
        </div>
    </div>
</template>