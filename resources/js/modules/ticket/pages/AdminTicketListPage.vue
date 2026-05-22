<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">All Tickets</h1>
                <p class="text-gray-500 mt-1">Manage and respond to customer support requests</p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search by subject or user..."
                        @input="debouncedSearch"
                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending_level_1">Pending Level 1</option>
                        <option value="pending_level_2">Pending Level 2</option>
                        <option value="approved">Approved</option>
                        <option value="closed">Closed</option>
                        <option value="rejected">Rejected</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <select
                        v-model="filters.priority"
                        @change="applyFilters"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div>
                    <button
                        @click="resetFilters"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                    >
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- Bulk Actions (visible when at least one ticket selected) -->
            <div v-if="selectedIds.size > 0" class="flex justify-end gap-2">
                <button
                    @click="openBulkConfirmModal"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
                >
                    Confirm Selected ({{ selectedIds.size }})
                </button>
                <button
                    @click="openBulkRejectModal"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
                >
                    Reject Selected ({{ selectedIds.size }})
                </button>
                <button
                    v-if="userRole === 'admin-level-2'"
                    @click="openBulkCloseModal"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
                >
                    Close Selected ({{ selectedIds.size }})
                </button>
                <button
                    @click="clearSelection"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="p-8 text-center text-gray-500">Loading tickets...</div>

        <!-- Ticket List -->
        <div v-else class="grid gap-4">
            <!-- Select All Row -->
            <div class="flex items-center gap-2 px-2">
                <input
                    type="checkbox"
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                    class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-600">Select All</span>
            </div>

            <div
                v-for="ticket in data"
                :key="ticket.id"
                class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition p-5 flex items-center justify-between"
            >
                <!-- Checkbox -->
                <div class="mr-3">
                    <input
                        type="checkbox"
                        :checked="selectedIds.has(ticket.id)"
                        @change="toggleSelection(ticket.id)"
                        class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                    />
                </div>

                <!-- Left (clickable) -->
                <div class="space-y-2 flex-1 cursor-pointer" @click="selectedTicket = ticket">
                    <div class="flex items-center gap-2 text-gray-900 font-semibold">
                        <MessageSquareIcon class="text-indigo-500" :size="18" />
                        {{ ticket.subject }}
                    </div>
                    <div class="flex items-center gap-1 mt-4 text-sm text-gray-500">
                        <UserIcon :size="14" />
                        <span>{{ ticket.user?.name }}</span>
                        <ClockIcon :size="14" class="ml-2" />
                        <span>{{ formatDate(ticket.created_at) }}</span>
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <!-- Status Label -->
                    <span
                        :class="[
                            'px-4 py-1 text-xs font-medium rounded-full capitalize',
                            statusColorStyles[ticket.status_color] || 'bg-gray-100 text-gray-700'
                        ]"
                    >
                        {{ ticket.status }}
                    </span>

                    <!-- Confirm Button -->
                    <button
                        v-if="canShowConfirmButton(ticket)"
                        @click.stop="openSingleConfirmModal(ticket.id)"
                        class="px-3 py-1 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
                    >
                        Confirm
                    </button>

                    <!-- Reject Button -->
                    <button
                        v-if="canShowRejectButton(ticket)"
                        @click.stop="openSingleRejectModal(ticket.id)"
                        class="px-3 py-1 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition"
                    >
                        Reject
                    </button>

                    <!-- Close Button -->
                    <button
                        v-if="canShowCloseButton(ticket)"
                        @click.stop="openSingleCloseModal(ticket.id)"
                        class="px-3 py-1 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
                    >
                        Close
                    </button>

                    <ChevronRightIcon class="text-gray-400 cursor-pointer" :size="20" @click="selectedTicket = ticket" />
                </div>
            </div>
        </div>

        <!-- Ticket Detail Modal -->
        <AdminTicketModal
            v-if="selectedTicket"
            :ticket="selectedTicket"
            @close="selectedTicket = null"
        />

        <!-- Confirm Modal (Single & Bulk) -->
        <div
            v-if="showConfirmModal"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black/50"
            @click.self="closeConfirmModal"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ isBulkConfirm ? 'Confirm Multiple Tickets' : 'Confirm Ticket' }}
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    <template v-if="isBulkConfirm">
                        Are you sure you want to confirm <strong>{{ pendingConfirmIds.length }}</strong> ticket(s)? Status will be changed to next level.
                    </template>
                    <template v-else>
                        Are you sure you want to confirm this ticket? The status will be changed to next level.
                    </template>
                </p>
                <div class="flex justify-end gap-3 mt-5">
                    <button
                        @click="closeConfirmModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitConfirm"
                        :disabled="isConfirming"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ isConfirming ? 'Confirming...' : 'Yes, Confirm' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Modal (Single & Bulk) -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black/50"
            @click.self="closeRejectModal"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ isBulkReject ? 'Reject Multiple Tickets' : 'Reject Ticket' }}
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    Please provide a reason for rejection (optional):
                </p>
                <textarea
                    v-model="rejectMessage"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Enter rejection reason..."
                ></textarea>
                <div class="flex justify-end gap-3 mt-5">
                    <button
                        @click="closeRejectModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitReject"
                        :disabled="isRejecting"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ isRejecting ? 'Rejecting...' : 'Confirm Reject' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Close Modal (Single & Bulk) -->
        <div
            v-if="showCloseModal"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black/50"
            @click.self="closeCloseModal"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ isBulkClose ? 'Close Multiple Tickets' : 'Close Ticket' }}
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    <template v-if="isBulkClose">
                        Are you sure you want to close <strong>{{ pendingCloseIds.length }}</strong> ticket(s)? This action cannot be undone.
                    </template>
                    <template v-else>
                        Are you sure you want to close this ticket? This action cannot be undone.
                    </template>
                </p>
                <div class="flex justify-end gap-3 mt-5">
                    <button
                        @click="closeCloseModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitClose"
                        :disabled="isClosing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isClosing ? 'Closing...' : 'Confirm Close' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <ToastContainer ref="toastContainer" />
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '../../../lib/axios'
import {
    User as UserIcon,
    MessageSquare as MessageSquareIcon,
    Clock as ClockIcon,
    ChevronRight as ChevronRightIcon,
    Search as SearchIcon,
} from 'lucide-vue-next'
import AdminTicketModal from '../components/AdminTicketModal.vue'
import ToastContainer from '../../../components/ToastContainer.vue'
import { useAuthStore } from "../../../context/useAuth"

const authStore = useAuthStore()
const userId = authStore.user?.id
const userRole = authStore.user?.role

const queryClient = useQueryClient()
const selectedTicket = ref(null)
const toastContainer = ref(null)

// Filter state
const filters = ref({
    search: '',
    status: '',
    priority: '',
})

const debounce = (fn, delay) => {
    let timeoutId
    return (...args) => {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => fn(...args), delay)
    }
}

const debouncedSearch = debounce(() => {
    applyFilters()
}, 300)

const applyFilters = () => {
    queryClient.invalidateQueries({ queryKey: ['adminTickets', filters] })
}

const resetFilters = () => {
    filters.value = { search: '', status: '', priority: '' }
    applyFilters()
}

// Fetch tickets
const { data, isLoading } = useQuery({
    queryKey: ['adminTickets', filters],
    queryFn: async () => {
        const params = new URLSearchParams()
        if (filters.value.search) params.append('filter[search]', filters.value.search)
        if (filters.value.status) params.append('filter[status]', filters.value.status)
        if (filters.value.priority) params.append('filter[priority]', filters.value.priority)
        const res = await api.get(`/api/v1/admin/tickets?${params.toString()}`)
        return res.data.data
    },
})

// Selection state
const selectedIds = ref(new Set())

watch(filters, () => clearSelection(), { deep: true })

const isAllSelected = computed(() => {
    if (!data.value) return false
    return data.value.length > 0 && selectedIds.value.size === data.value.length
})

const toggleSelection = (id) => {
    if (selectedIds.value.has(id)) selectedIds.value.delete(id)
    else selectedIds.value.add(id)
    selectedIds.value = new Set(selectedIds.value)
}

const toggleSelectAll = () => {
    if (isAllSelected.value) selectedIds.value.clear()
    else selectedIds.value = new Set(data.value.map(t => t.id))
    selectedIds.value = new Set(selectedIds.value)
}

const clearSelection = () => {
    selectedIds.value.clear()
    selectedIds.value = new Set(selectedIds.value)
}

// Helper: Role + Status based button visibility
const canShowConfirmButton = (ticket) => {
    if (userRole === 'admin-level-1' && ticket.status === 'pending level1') return true
    if (userRole === 'admin-level-2' && ticket.status === 'pending level2') return true
    return false
}
const canShowRejectButton = (ticket) => canShowConfirmButton(ticket)
const canShowCloseButton = (ticket) => {
    if (userRole !== 'admin-level-2') return false
    return ['approved', 'rejected', 'failed'].includes(ticket.status)
}

// ------------------------------------------------------------
// Confirm (Single & Bulk)
const showConfirmModal = ref(false)
const isBulkConfirm = ref(false)
const currentConfirmTicketId = ref(null)
let pendingConfirmIds = []
const isConfirming = ref(false)

const openSingleConfirmModal = (ticketId) => {
    currentConfirmTicketId.value = ticketId
    isBulkConfirm.value = false
    showConfirmModal.value = true
}

const openBulkConfirmModal = () => {
    const eligibleIds = Array.from(selectedIds.value).filter(id => {
        const ticket = data.value?.find(t => t.id === id)
        return ticket && canShowConfirmButton(ticket)
    })
    if (eligibleIds.length === 0) {
        alert('No selected tickets can be confirmed with your role and current status.')
        return
    }
    pendingConfirmIds = eligibleIds
    isBulkConfirm.value = true
    showConfirmModal.value = true
}

const closeConfirmModal = () => {
    showConfirmModal.value = false
    currentConfirmTicketId.value = null
    pendingConfirmIds = []
    isBulkConfirm.value = false
}

const { mutate: confirmSingle } = useMutation({
    mutationFn: async ({ ticketId }) => {
        const res = await api.post(`/api/v1/admin/tickets/${ticketId}/pending`)
        return res.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['adminTickets'] }),
})

const bulkConfirm = async (ids) => {
    const errors = []
    for (const id of ids) {
        try { await api.post(`/api/v1/admin/tickets/${id}/pending`) }
        catch (err) { errors.push(id) }
    }
    if (errors.length) alert(`Failed to confirm ${errors.length} tickets.`)
    await queryClient.invalidateQueries({ queryKey: ['adminTickets'] })
}

const submitConfirm = async () => {
    isConfirming.value = true
    try {
        if (isBulkConfirm.value) {
            await bulkConfirm(pendingConfirmIds)
            clearSelection()
        } else {
            await confirmSingle({ ticketId: currentConfirmTicketId.value })
        }
        closeConfirmModal()
    } finally {
        isConfirming.value = false
    }
}

// ------------------------------------------------------------
// Reject (Single & Bulk)
const showRejectModal = ref(false)
const isBulkReject = ref(false)
const currentRejectTicketId = ref(null)
let pendingRejectIds = []
const rejectMessage = ref('')
const isRejecting = ref(false)

const openSingleRejectModal = (ticketId) => {
    currentRejectTicketId.value = ticketId
    isBulkReject.value = false
    rejectMessage.value = ''
    showRejectModal.value = true
}

const openBulkRejectModal = () => {
    const eligibleIds = Array.from(selectedIds.value).filter(id => {
        const ticket = data.value?.find(t => t.id === id)
        return ticket && canShowRejectButton(ticket)
    })
    if (eligibleIds.length === 0) {
        alert('No selected tickets can be rejected with your role and current status.')
        return
    }
    pendingRejectIds = eligibleIds
    isBulkReject.value = true
    rejectMessage.value = ''
    showRejectModal.value = true
}

const closeRejectModal = () => {
    showRejectModal.value = false
    currentRejectTicketId.value = null
    pendingRejectIds = []
    rejectMessage.value = ''
    isBulkReject.value = false
}

const { mutate: rejectSingle } = useMutation({
    mutationFn: async ({ ticketId, message }) => {
        const res = await api.post(`/api/v1/admin/tickets/${ticketId}/reject`, { message })
        return res.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['adminTickets'] }),
})

const bulkReject = async (ids, message) => {
    const errors = []
    for (const id of ids) {
        try { await api.post(`/api/v1/admin/tickets/${id}/reject`, { message }) }
        catch (err) { errors.push(id) }
    }
    if (errors.length) alert(`Failed to reject ${errors.length} tickets.`)
    await queryClient.invalidateQueries({ queryKey: ['adminTickets'] })
}

const submitReject = async () => {
    isRejecting.value = true
    try {
        if (isBulkReject.value) {
            await bulkReject(pendingRejectIds, rejectMessage.value)
            clearSelection()
        } else {
            await rejectSingle({ ticketId: currentRejectTicketId.value, message: rejectMessage.value })
        }
        closeRejectModal()
    } finally {
        isRejecting.value = false
    }
}

// ------------------------------------------------------------
// Close (Single & Bulk)
const showCloseModal = ref(false)
const isBulkClose = ref(false)
const currentCloseTicketId = ref(null)
let pendingCloseIds = []
const isClosing = ref(false)

const openSingleCloseModal = (ticketId) => {
    currentCloseTicketId.value = ticketId
    isBulkClose.value = false
    showCloseModal.value = true
}

const openBulkCloseModal = () => {
    const eligibleIds = Array.from(selectedIds.value).filter(id => {
        const ticket = data.value?.find(t => t.id === id)
        return ticket && canShowCloseButton(ticket)
    })
    if (eligibleIds.length === 0) {
        alert('No selected tickets can be closed (only Approved, Rejected, or Failed tickets can be closed).')
        return
    }
    pendingCloseIds = eligibleIds
    isBulkClose.value = true
    showCloseModal.value = true
}

const closeCloseModal = () => {
    showCloseModal.value = false
    currentCloseTicketId.value = null
    pendingCloseIds = []
    isBulkClose.value = false
}

const { mutate: closeSingle } = useMutation({
    mutationFn: async ({ ticketId }) => {
        const res = await api.post(`/api/v1/admin/tickets/${ticketId}/close`)
        return res.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['adminTickets'] }),
})

const bulkClose = async (ids) => {
    const errors = []
    for (const id of ids) {
        try { await api.post(`/api/v1/admin/tickets/${id}/close`) }
        catch (err) { errors.push(id) }
    }
    if (errors.length) alert(`Failed to close ${errors.length} tickets.`)
    await queryClient.invalidateQueries({ queryKey: ['adminTickets'] })
}

const submitClose = async () => {
    isClosing.value = true
    try {
        if (isBulkClose.value) {
            await bulkClose(pendingCloseIds)
            clearSelection()
        } else {
            await closeSingle({ ticketId: currentCloseTicketId.value })
        }
        closeCloseModal()
    } finally {
        isClosing.value = false
    }
}

// ------------------------------------------------------------
// Misc
const statusColorStyles = {
    yellow: 'bg-yellow-100 text-yellow-700',
    red: 'bg-red-100 text-red-700',
    gray: 'bg-gray-200 text-gray-700',
    green: 'bg-green-100 text-green-700'
}

const formatDate = (date) => new Date(date).toLocaleDateString()

// Toast notifications
const fetchAndShowUnreadNotifications = async () => {
    try {
        const response = await api.get(`/api/v1/admin/notifications/${userId}/unread`)
        const notifications = response.data.data || response.data || []
        for (const notif of notifications) {
            let message = ''
            if (notif.data) {
                if (typeof notif.data === 'string') {
                    try {
                        const parsed = JSON.parse(notif.data)
                        message = parsed.message || parsed.title || parsed.body || notif.data
                    } catch { message = notif.data }
                } else {
                    message = notif.data.message || notif.data.title || notif.data.body || 'New notification'
                }
            } else {
                message = 'You have a new notification'
            }
            if (toastContainer.value) {
                toastContainer.value.addToast(message, 'info', 6000, "", `/admin/users/notifications?userId=${userId}`)
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
