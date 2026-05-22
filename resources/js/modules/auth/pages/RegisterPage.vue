<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <form @submit.prevent="onSubmit" class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 space-y-3">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                    <p class="text-sm text-gray-500">Join Support Desk today</p>
                </div>

                <!-- Name -->
                <div class="space-y-1 relative">
                    <label class="text-sm font-medium text-gray-700">Name</label>
                    <div class="relative">
                        <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="John Doe"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition"
                        />
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-1 relative">
                    <label class="text-sm font-medium text-gray-700">Email address</label>
                    <div class="relative">
                        <MailIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition"
                        />
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-1 relative">
                    <label class="text-sm font-medium text-gray-700">Password</label>
                    <div class="relative">
                        <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition"
                        />
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1 relative">
                    <label class="text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="relative">
                        <CheckIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100 outline-none transition"
                        />
                    </div>
                </div>

                <!-- Register Button -->
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold py-2.5 rounded-lg shadow-sm hover:shadow transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <UserIcon :size="20" />
                    {{ isLoading ? 'Registering...' : 'Register' }}
                </button>

                <!-- Footer -->
                <p class="text-sm text-center text-gray-500">
                    Already have an account?
                    <router-link to="/login" class="text-blue-600 font-medium hover:underline">
                        Sign in
                    </router-link>
                </p>
            </form>

            <p class="text-center text-xs text-gray-400 mt-4">
                © {{ currentYear }} Tickets Chain
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../lib/axios'
import { User as UserIcon, Mail as MailIcon, Lock as LockIcon, Check as CheckIcon } from 'lucide-vue-next'

const router = useRouter()
const currentYear = new Date().getFullYear()

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const isLoading = ref(false)

const onSubmit = async () => {
    isLoading.value = true
    try {
        await api.post('/api/v1/register', {
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation
        })
        router.push('/login')
    } catch (error) {
        console.error(error)
        alert('Registration failed')
    } finally {
        isLoading.value = false
    }
}
</script>
