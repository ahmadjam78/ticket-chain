<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- ===== Sidebar ===== -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <!-- Brand -->
            <div class="px-6 py-5 border-b border-gray-200 flex items-center gap-3">
                <div class="bg-indigo-600 text-white p-2 rounded-xl shadow">
                    <ShieldIcon :size="18" />
                </div>
                <span class="font-semibold text-gray-900">Admin Panel</span>
            </div>

            <!-- Menu -->
            <nav class="flex-1 p-4 space-y-2">
                <!-- Tickets – visible to both roles -->
                <router-link
                    to="/admin/tickets"
                    custom
                    v-slot="{ isActive, navigate }"
                >
                    <button
                        @click="navigate"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium w-full text-left',
                            isActive
                                ? 'bg-indigo-50 text-indigo-700'
                                : 'text-gray-700 hover:bg-gray-100'
                        ]"
                    >
                        <MessageSquareIcon :size="18" />
                        Tickets
                    </button>
                </router-link>

                <!-- Users – only level 2 -->
                <router-link
                    v-if="userRole === 'admin-level-2'"
                    to="/admin/users"
                    custom
                    v-slot="{ isActive, navigate }"
                >
                    <button
                        @click="navigate"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium w-full text-left',
                            isActive
                                ? 'bg-indigo-50 text-indigo-700'
                                : 'text-gray-700 hover:bg-gray-100'
                        ]"
                    >
                        <UsersIcon :size="18" />
                        Users
                    </button>
                </router-link>

                <!-- Logs – only level 2 -->
                <router-link
                    v-if="userRole === 'admin-level-2'"
                    to="/admin/logs"
                    custom
                    v-slot="{ isActive, navigate }"
                >
                    <button
                        @click="navigate"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium w-full text-left',
                            isActive
                                ? 'bg-indigo-50 text-indigo-700'
                                : 'text-gray-700 hover:bg-gray-100'
                        ]"
                    >
                        <FileTextIcon :size="18" />
                        Logs
                    </button>
                </router-link>
            </nav>
        </aside>

        <!-- ===== Main Area ===== -->
        <div class="flex-1 flex flex-col">
            <!-- ===== Top Navbar ===== -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3 text-gray-900 font-semibold">
                    <HomeIcon :size="18" />
                    Dashboard
                </div>

                <div class="flex items-center gap-5">
                    <div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-100 transition">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-semibold shadow-sm">
                            A
                        </div>
                        <div class="hidden sm:block leading-tight">
                            <p class="text-sm font-medium text-gray-900">Admin</p>
                            <p class="text-xs text-gray-500">Super User</p>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-gray-200" />

                    <button
                        @click="handleLogout"
                        :disabled="isLogoutLoading"
                        class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 transition"
                    >
                        <LogOutIcon :size="18" />
                        {{ isLogoutLoading ? 'Signing out...' : 'Logout' }}
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-8">
                <router-view />
            </main>

            <footer class="bg-white border-t border-gray-200 px-6 py-4 text-sm text-gray-500 flex justify-between">
                <span>© {{ currentYear }} Admin Panel</span>
                <span class="hidden sm:block">Built with Laravel & Vue</span>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useMutation } from '@tanstack/vue-query'
import { computed } from 'vue'
import api from '../lib/axios'
import { useAuthStore } from '../context/useAuth'  // adjust path to your actual auth store

import {
    Shield as ShieldIcon,
    MessageSquare as MessageSquareIcon,
    Users as UsersIcon,
    Home as HomeIcon,
    LogOut as LogOutIcon,
    Bell as BellIcon,
    FileText as FileTextIcon
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const currentYear = new Date().getFullYear()

// Role from store – defaults to 'admin-level-1' if not set
const userRole = computed(() => authStore.user?.role ?? 'admin-level-1')

// Logout mutation
const { mutate: logout, isLoading: isLogoutLoading } = useMutation({
    mutationFn: async () => {
        const res = await api.post('/api/v1/logout')
        return res.data
    },
    onSuccess: () => {
        authStore.clearUser()
        router.replace('/login')
    },
    onError: () => {
        console.error('Logout failed:', error)
        alert('Logout failed. Please try again.')
    }
})

const handleLogout = (e) => {
    e.preventDefault()
    logout()
}
</script>
