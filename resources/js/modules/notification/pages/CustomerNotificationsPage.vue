<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <p class="text-sm text-gray-500 mt-1">Your latest updates</p>
            </div>
            <div v-if="hasUnread" class="flex gap-2">
                <button @click="markAllAsRead" :disabled="isMarkingAll"
                        class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                    Mark all as read
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Loading -->
            <div v-if="isLoading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="text-center py-8">
                <p class="text-red-600">Failed to load notifications: {{ error.message }}</p>
                <button @click="fetchNotifications" class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">
                    Retry
                </button>
            </div>

            <!-- Empty -->
            <div v-else-if="notifications.length === 0" class="text-center py-8 text-gray-500">
                <p>No notifications found.</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="notif in notifications" :key="notif.id"
                        :class="['hover:bg-gray-50 transition', !notif.read_at ? 'bg-blue-50' : '']">
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="notif.read_at ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700'"
                                      class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ notif.read_at ? 'Read' : 'Unread' }}
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ formatType(notif.type) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ getMessage(notif.data) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(notif.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <button v-if="!notif.read_at"
                                    @click="markAsRead(notif.id)"
                                    :disabled="isMarking === notif.id"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Mark as read
                            </button>
                            <span v-else class="text-gray-400 text-sm">Done</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex justify-between items-center">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                        class="px-3 py-1 text-sm border rounded disabled:opacity-50">Previous</button>
                <span class="text-sm text-gray-600">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
                        class="px-3 py-1 text-sm border rounded disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../../lib/axios'
import {useAuthStore} from "../../../context/useAuth";

const authStore = useAuthStore()
const userId = authStore.user?.id

const notifications = ref([])
const isLoading = ref(false)
const error = ref(null)
const pagination = ref(null)
const isMarking = ref(null)      // id notif in mark process
const isMarkingAll = ref(false)

const hasUnread = computed(() => notifications.value.some(n => !n.read_at))

const fetchNotifications = async (page = 1) => {
    isLoading.value = true
    error.value = null
    try {
        const response = await api.get(`/api/v1/customer/notifications/${userId}`, { params: { page } })
        notifications.value = response.data.data || []
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
        }
    } catch (err) {
        error.value = err.response?.data?.message || err.message || 'An error occurred'
        console.error('Failed to fetch notifications:', err)
    } finally {
        isLoading.value = false
    }
}

const markAsRead = async (id) => {
    isMarking.value = id
    try {
        await api.post(`/api/v1/customer/notifications/${id}/${userId}/mark-as-read`)
        const index = notifications.value.findIndex(n => n.id === id)
        if (index !== -1) notifications.value[index].read_at = new Date().toISOString()
    } catch (err) {
        console.error('Failed to mark as read:', err)
        alert('Could not mark notification as read')
    } finally {
        isMarking.value = null
    }
}

const markAllAsRead = async () => {
    isMarkingAll.value = true
    try {
        await api.post(`/api/v1/customer/notifications/${userId}/mark-all-as-read`)
        notifications.value.forEach(n => n.read_at = new Date().toISOString())
    } catch (err) {
        console.error('Failed to mark all as read:', err)
        alert('Could not mark all as read')
    } finally {
        isMarkingAll.value = false
    }
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchNotifications(page)
    }
}

const formatDate = (dateString) => {
    if (!dateString) return '—'
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

const formatType = (type) => {
    const parts = type.split('\\')
    return parts.pop().replace(/([A-Z])/g, ' $1').trim()
}

const getMessage = (data) => {
    if (!data) return '—'
    if (typeof data === 'string') {
        try { data = JSON.parse(data) } catch { return data }
    }
    return data.message || data.title || data.body || JSON.stringify(data)
}

onMounted(() => {
    fetchNotifications()
})
</script>
