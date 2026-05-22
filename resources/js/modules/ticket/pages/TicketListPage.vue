<template>
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Tickets</h1>
            <router-link
                to="/tickets/new"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow transition"
            >
                + New Ticket
            </router-link>
        </div>

        <!-- Loading & Error States -->
        <div v-if="isLoading" class="p-6 text-gray-500">Loading tickets...</div>
        <div v-else-if="error" class="p-6 text-red-500">Error loading tickets</div>

        <!-- Ticket List -->
        <div v-else class="space-y-4">
            <div
                v-for="ticket in data?.data"
                :key="ticket.id"
                class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">
                            {{ ticket.subject }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Ticket #{{ ticket.id }}</p>
                    </div>
                    <span
                        :class="[
                    'px-4 py-1 text-xs font-medium rounded-full capitalize',
                    statusColorStyles[ticket.status_color] || 'bg-gray-100 text-gray-700'
                ]"
                    >
                {{ ticket.status }}
            </span>
                </div>

                <div class="flex justify-between items-center mt-4">
                    <div class="flex gap-3 text-xs text-gray-500">
                        <span>{{ formatDate(ticket.created_at) }}</span>
                        <span>{{ ticket.messages_count ?? 0 }} messages</span>
                    </div>
                    <button
                        @click="selectedTicket = ticket"
                        class="text-blue-600 hover:text-blue-800 font-medium"
                    >
                        View Conversation →
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal (Ticket Detail) -->
        <TicketModal
            v-if="selectedTicket"
            :ticket="selectedTicket"
            @close="selectedTicket = null"
        />
    </div>
    <ToastContainer ref="toastContainer" />
</template>

<script setup>
import {onMounted, ref} from 'vue'
import { useQuery } from '@tanstack/vue-query'
import api from '../../../lib/axios'
import TicketModal from '../components/TicketModal.vue'
import ToastContainer from '../../../components/ToastContainer.vue'
import {useAuthStore} from "../../../context/useAuth";

const authStore = useAuthStore()
const userId = authStore.user?.id

const toastContainer = ref(null)
const selectedTicket = ref(null)

const { data, isLoading, error } = useQuery({
    queryKey: ['tickets'],
    queryFn: async () => {
        const res = await api.get('/api/v1/customer/tickets')
        return res.data
    }
})

const formatDate = (dateString) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};

const statusColorStyles = {
    yellow: 'bg-yellow-100 text-yellow-700',
    red: 'bg-red-100 text-red-700',
    gray: 'bg-gray-200 text-gray-700',
    green: 'bg-green-100 text-green-700'
}

const fetchAndShowUnreadNotifications = async () => {
    try {
        const response = await api.get(`/api/v1/customer/notifications/${userId}/unread`)
        const notifications = response.data.data || response.data || []

        for (const notif of notifications) {
            let message = ''
            if (notif.data) {
                if (typeof notif.data === 'string') {
                    try {
                        const parsed = JSON.parse(notif.data)
                        message = parsed.message || parsed.title || parsed.body || notif.data
                    } catch {
                        message = notif.data
                    }
                } else {
                    message = notif.data.message || notif.data.title || notif.data.body || 'New notification'
                }
            } else {
                message = 'You have a new notification'
            }

            if (toastContainer.value) {
                toastContainer.value.addToast(message, 'info', 6000)
            }
        }
    } catch (err) {
        console.error('Failed to fetch unread notifications', err)
    }
}

onMounted(() => {
    fetchAndShowUnreadNotifications()
})
</script>
