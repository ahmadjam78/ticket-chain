<template>
    <div>
        <router-view v-if="$route.path.includes('/notifications')" />

        <div v-else class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Users</h1>
                <p class="text-gray-500 mt-1">Manage system users and permissions</p>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="p-8">Loading...</div>

            <!-- User Grid -->
            <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="user in data"
                    :key="user.id"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition p-5 flex flex-col justify-between"
                >
                    <!-- Top -->
                    <div class="flex items-center gap-4">
                        <!-- Avatar -->
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-semibold text-lg shadow">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>

                        <!-- Name & Email -->
                        <div>
                            <h2 class="font-semibold text-gray-900">{{ user.name }}</h2>
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <MailIcon :size="14" />
                                {{ user.email }}
                            </div>
                        </div>
                    </div>

                    <!-- Bottom -->
                    <div class="mt-5 flex items-center justify-start">
                        <!-- Role -->
                        <span
                            :class="[
                            'flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full capitalize',
                            roleStyles[user.role] || 'bg-gray-100 text-gray-700'
                        ]"
                        >
                        <ShieldIcon :size="12" />
                        {{ user.role }}
                    </span>
                        <router-link
                            :to="{
                                path: '/admin/users/notifications',
                                query: { userId: user.id }
                            }"
                            class="p-2 text-gray-500 hover:text-indigo-600 transition"
                            title="View notifications"
                        >
                            <BellIcon :size="18" />
                        </router-link>
                        <!-- ID -->
                        <p class="text-xs text-gray-400 text-right grow">ID #{{ user.id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useQuery } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import api from '../../../lib/axios'
import { Mail as MailIcon, Shield as ShieldIcon, Bell as BellIcon } from 'lucide-vue-next'

const router = useRouter()

const roleStyles = {
    admin: 'bg-indigo-100 text-indigo-700',
    customer: 'bg-green-100 text-green-700',
}

const { data, isLoading } = useQuery({
    queryKey: ['users'],
    queryFn: async () => {
        const res = await api.get('/api/v1/admin/users')
        return res.data.data
    },
})

</script>
