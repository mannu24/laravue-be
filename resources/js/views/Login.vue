<script setup>
import { computed, nextTick, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import CustomInput from "../components/elements/CustomInput.vue";

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
    timer.value = setInterval(function(){
        var minutes = Math.floor(sec / 60);
        var seconds = sec % 60;
        var formattedMinutes = String(minutes).padStart(2, '0');
        var formattedSeconds = String(seconds).padStart(2, '0');
        timer_html.value = formattedMinutes+':'+formattedSeconds ;
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
        nextTick(() => {
            init_form()
            init_timer()
        })
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
        if(response.data.status == 'success') {
            const data = response.data.data;
            const { token, user } = data;
            authStore.setAuthData(token, user);
            errorMessage.value = '';
            if (response.data.data.is_new) {
                router.push('/profile/@' + response.data.data.user.username)
            } else {
                router.push('/')
            }
        } else errorMessage.value = response.data.message ;
    }).catch((error) => {
        btn.value = false
        errorMessage.value = error.response?.data?.message || 'Failed to send OTP.';
    });
};
</script>

<template>
    <div class="min-h-[80vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-6xl mx-auto px-4 md:px-6 py-24">
            <div class="flex justify-center">
                <form v-if="!isOtpSent" @submit.prevent="handleOtpRequest()" class="max-w-md mx-auto text-center bg-gray-800/80 px-4 sm:px-8 py-10 rounded-xl shadow">
                    <header class="mb-8">
                        <h1 class="text-2xl font-bold mb-3 text-white">Sign in to Laravue</h1>
                        <p class="text-[15px] text-slate-400">We will need your email to send the verification code.</p>
                    </header>
                    <div class="flex items-center justify-center gap-3">
                        <CustomInput type="text" placeholder="Write Email Here..." v-model="loginData.email" />
                    </div>
                    <div v-if="errorMessage" class="text-red-500 text-sm">
                        {{ errorMessage }}
                    </div>
                    <div class="max-w-[260px] mx-auto mt-4">
                        <button type="submit" :class="btn == 'loading' ? 'cursor-wait bg-vue/70' : 'bg-vue/90 hover:bg-vue'"
                        class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-3.5 py-2.5 text-sm font-medium text-white shadow-sm shadow-vue/10 focus:outline-none focus:bg-vue focus:ring focus:ring-vue/40 focus-visible:outline-none focus-visible:ring focus-visible:ring-vue/40">Get OTP</button>
                    </div>
                </form>
                <form v-else id="otp-form" @submit.prevent="handleOtpVerification()" class="max-w-md mx-auto text-center bg-gray-800/80 px-4 sm:px-8 py-10 rounded-xl shadow">
                    <header class="mb-8">
                        <h1 class="text-2xl font-bold mb-3 text-white">Email Verification</h1>
                        <p class="text-[15px] text-slate-400">Enter the 4-digit verification code that was sent to <br>
                            <div class="flex gap-2 items-center justify-center mt-1">
                                <b>"{{ loginData.email }}"</b>
                                <i class="fas fa-pencil-alt hover:text-white cursor-pointer" @click="edit_email"></i>
                            </div>
                        </p>
                    </header>
                    <div class="flex items-center justify-center gap-3">
                        <input type="password" v-model="loginData.otp[0]" class="w-12 h-12 text-center text-2xl font-extrabold text-gray-100 bg-gray-700 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-gray-500 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                        <input type="password" v-model="loginData.otp[1]" class="w-12 h-12 text-center text-2xl font-extrabold text-gray-100 bg-gray-700 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-gray-500 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                        <input type="password" v-model="loginData.otp[2]" class="w-12 h-12 text-center text-2xl font-extrabold text-gray-100 bg-gray-700 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-gray-500 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                        <input type="password" v-model="loginData.otp[3]" class="w-12 h-12 text-center text-2xl font-extrabold text-gray-100 bg-gray-700 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-gray-500 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                        <input type="password" v-model="loginData.otp[4]" class="w-12 h-12 text-center text-2xl font-extrabold text-gray-100 bg-gray-700 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-gray-500 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                        <input type="password" v-model="loginData.otp[5]" class="w-12 h-12 text-center text-2xl font-extrabold text-gray-100 bg-gray-700 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-gray-500 focus:border-vue/90 focus:ring-2 focus:ring-vue/40" pattern="\d*" maxlength="1" />
                    </div>
                    <div v-if="errorMessage" class="text-laravel/80 text-sm mt-3">
                        {{ errorMessage }}
                    </div>
                    <div class="max-w-[260px] mx-auto mt-4">
                        <button type="submit" :class="btn == 'loading' ? 'cursor-wait bg-vue/70' : 'bg-vue/90 hover:bg-vue'"
                        class="w-full inline-flex transition-all duration-300 justify-center whitespace-nowrap rounded-lg px-3.5 py-2.5 text-sm font-medium text-white shadow-sm shadow-vue/10 focus:outline-none focus:bg-vue focus:ring focus:ring-vue/40 focus-visible:outline-none focus-visible:ring focus-visible:ring-vue/40">Verify Account</button>
                    </div>
                    <div class="text-sm text-slate-400 mt-4">Didn't receive code? 
                        <span v-if="!timer" class="font-medium text-vue hover:text-laravel/80 cursor-pointer" @click="handleOtpRequest">Resend</span>
                        <span v-else class="font-medium text-vue">Resend in {{ timer_html }}</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
