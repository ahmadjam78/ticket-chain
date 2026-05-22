<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- ===== Top Navbar ===== -->
        <header class="bg-white/80 backdrop-blur border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <!-- Brand -->
                <router-link
                    to="/tickets"
                    class="flex items-center gap-3 group"
                >
                    <div class="bg-blue-600 text-white p-2 rounded-xl shadow-sm group-hover:shadow-md transition">
                        <MessageSquareIcon :size="18" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight text-gray-900">
                       Tickets Chain
                    </span>
                </router-link>

                <!-- Right Side -->
                <div class="flex items-center gap-3">
                    <!-- Profile -->
                    <div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-100 transition">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-semibold shadow-sm">
                            C
                        </div>
                        <div class="hidden sm:block leading-tight">
                            <p class="text-sm font-medium text-gray-900">Customer</p>
                            <p class="text-xs text-gray-500">User Panel</p>
                        </div>
                    </div>

                    <router-link
                        to="/notifications"
                        class="text-gray-600 hover:text-indigo-600 transition rounded-full"
                    >
                        <div class="relative inline-block -ml-[8px] mr-[8px]">
                            <BellIcon :size="20" />
                            <span
                                v-if="unreadCount > 0"
                                class="absolute -top-1 -right-1 flex items-center justify-center h-4 w-4 text-[10px] font-bold text-white bg-red-500 rounded-full"
                            >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
                        </div>
                    </router-link>

                    <div class="h-6 w-px bg-gray-200" />

                    <!-- Logout Button -->
                    <button
                        @click="handleLogout"
                        :disabled="isLogoutLoading"
                        class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 transition"
                    >
                        <LogOutIcon :size="18" />
                        {{ isLogoutLoading ? 'Signing out...' : 'Logout' }}
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div class="max-w-7xl mx-auto w-full px-6 py-8">
                <router-view />
            </div>
        </main>

        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between text-sm text-gray-500">
                <span>© {{ currentYear }} Tickets Chain</span>
                <span class="hidden sm:block">Built with Laravel & Vue</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useMutation } from '@tanstack/vue-query'
import api from '../lib/axios'
import { useAuthStore } from '../context/useAuth'
import {
    LogOut as LogOutIcon,
    MessageSquare as MessageSquareIcon
} from 'lucide-vue-next'
import {useNotifications} from "../lib/useNotifications";
import { Bell as BellIcon } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const currentYear = new Date().getFullYear()

const { unreadCount } = useNotifications()

// Logout using mutation
const { mutate: logout, isLoading: isLogoutLoading } = useMutation({
    mutationFn: async () => {
        const res = await api.post('/api/v1/logout')
        return res.data
    },
    onSuccess: () => {
        // Clear user data from store
        authStore.clearUser()
        // Replace with login page instead of current page (non-returnable with Back button)
        router.replace('/login')
    },
    onError: (error) => {
        console.error('Logout failed:', error)
        alert('Logout failed. Please try again.')
    }
})

// Click handler to prevent default behavior and call logout
const handleLogout = (e) => {
    e.preventDefault()
    logout()
}
</script>
