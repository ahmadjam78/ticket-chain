<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Web Service Logs</h3>
            <p class="text-sm text-gray-500 mt-1">All attempts to send tickets to external web service</p>
        </div>

        <div class="p-6">
            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-8">
                <p class="text-red-600">Failed to load logs: {{ error.message }}</p>
                <button @click="fetchLogs" class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                    Retry
                </button>
            </div>

            <!-- Empty State -->
            <div v-else-if="logs.length === 0" class="text-center py-8 text-gray-500">
                <p>No web service logs found.</p>
            </div>

            <!-- Logs Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempt #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ log.ticket.subject }} (#{{ log.ticket.id }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ log.ticket.user.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ log.attempt_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="log.status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                      class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ log.status }}
                                </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-md truncate" :title="formatResponseRaw(log.response).code">
                            {{  formatResponseRaw(log.response).message }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(log.created_at) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex justify-between items-center">
                <button @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="px-3 py-1 text-sm border rounded disabled:opacity-50">
                    Previous
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-3 py-1 text-sm border rounded disabled:opacity-50">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../../../lib/axios'

const logs = ref([])
const isLoading = ref(false)
const error = ref(null)
const pagination = ref(null)

const fetchLogs = async (page = 1) => {
    isLoading.value = true
    error.value = null

    try {
        const response = await api.get('/api/v1/admin/web-service-logs', {
            params: { page }
        })
        logs.value = response.data.data || []
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
        }
    } catch (err) {
        error.value = err.response?.data?.message || err.message || 'An error occurred'
        console.error('Failed to fetch logs:', err)
    } finally {
        isLoading.value = false
    }
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchLogs(page)
    }
}

const formatDate = (dateString) => {
    if (!dateString) return '—'
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Pretty print response
const formatResponse = (response) => {
    if (!response) return '—'
    try {
        const parsed = JSON.parse(response)
        // If there is a message, display it; otherwise stringify the whole object
        return parsed.message || parsed.error || JSON.stringify(parsed)
    } catch {
        // If not JSON, show the string truncated to 100 characters
        return response.length > 100 ? response.substring(0, 100) + '...' : response
    }
}

// Full version for tooltip
const formatResponseRaw = (response) => {
    if (!response) return ''
    try {
        const parsed = JSON.parse(response)
        return JSON.stringify(parsed, null, 2)
    } catch {
        return response
    }
}

// Initial fetch
fetchLogs()
</script>
