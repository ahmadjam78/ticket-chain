<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <form @submit.prevent="onSubmit" class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 space-y-6">
                <!-- Header -->
                <div class="text-center space-y-2">
                    <h1 class="text-2xl font-bold text-gray-900">Welcome</h1>
                    <p class="text-sm text-gray-500">Sign in to your account</p>
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
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
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
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-2.5 rounded-lg shadow-sm hover:shadow transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <LogInIcon :size="20" />
                    {{ isLoading ? 'Signing in...' : 'Sign in' }}
                </button>

                <!-- Footer -->
                <p class="text-sm text-center text-gray-500">
                    Don’t have an account?
                    <router-link to="/register" class="text-blue-600 font-medium hover:underline">
                        Create one
                    </router-link>
                </p>
            </form>

            <p class="text-center text-xs text-gray-400 mt-6">
                © {{ currentYear }} Tickets Chain
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../lib/axios'
import {useAuth} from '../../../context/useAuth'
import { Mail as MailIcon, Lock as LockIcon, LogIn as LogInIcon } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuth()
const { setUser } = authStore // setUser method from store

const form = reactive({
    email: '',
    password: ''
})

const isLoading = ref(false)
const currentYear = new Date().getFullYear()

const onSubmit = async () => {
    isLoading.value = true
    try {
        // Get CSRF cookie (similar to React)
        await api.get('/sanctum/csrf-cookie')
        // Send login request
        await api.post('/api/v1/login', {
            email: form.email,
            password: form.password
        })
        // Get user data
        const res = await api.get('/api/v1/user')
        const user = res.data.data
        authStore.setUserAndInit(user)
        // Redirect based on role
        if (user.role === 'admin-level-1' || user.role === 'admin-level-2') {
            router.push('/admin/tickets')
        } else {
            router.push('/tickets')
        }
    } catch (error) {
        console.error(error)
        alert('Invalid credentials')
    } finally {
        isLoading.value = false
    }
}
</script>
