<template>
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-center p-5 border-b bg-indigo-600 text-white">
                <div>
                    <h2 class="text-xl font-semibold">{{ ticket.subject }}</h2>
                    <p class="text-sm opacity-90">Ticket #{{ ticket.id }}</p>
                </div>
                <button @click="emit('close')" class="hover:bg-white/20 rounded-full p-2 transition">
                    <XIcon :size="20" />
                </button>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-4 max-h-[350px]">
                <div v-if="isLoading" class="p-8 text-center text-gray-500">Loading messages...</div>

                <template v-else>
                    <div
                        v-for="msg in data?.messages"
                        :key="msg.id"
                        :class="['flex', isCustomer(msg) ? 'justify-start' : 'justify-end']"
                    >
                        <div
                            :class="[
                                'max-w-[75%] rounded-2xl px-4 py-3 shadow',
                                isCustomer(msg)
                                    ? 'bg-white border border-gray-300'
                                    : 'bg-indigo-100'
                            ]"
                        >
                            <div class="flex items-center gap-2 text-xs opacity-80 mb-1">
                                <UserIcon :size="12" />
                                <span>{{ msg.user?.name }}</span>
                                <ClockIcon :size="12" />
                                <span>{{ formatDate(msg.created_at) }}</span>
                            </div>

                            <p class="text-sm whitespace-pre-line">{{ msg.message }}</p>

                            <div v-if="msg.attachments?.length">
                                <a
                                    v-for="file in msg.attachments"
                                    :key="file.id"
                                    :href="file.original_url"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="mt-2 flex items-center justify-between bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-sm"
                                >
                                    <span class="truncate">{{ file.file_name }}</span>
                                    <DownloadIcon :size="16" />
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Reply Box -->
            <div class="p-5 bg-gray-50">
                <!-- Permission denied message for admin-level-1 -->
                <div v-if="isAdminLevelOne" class="text-center text-amber-600 bg-amber-50 rounded-xl p-3 text-sm">
                    ⚠️ Your role (Admin Level1) does not have permission to reply to tickets.
                </div>

                <!-- Reply box (only for allowed roles) -->
                <div v-else class="flex items-center gap-2">
                    <textarea
                        v-model="message"
                        placeholder="Reply to customer..."
                        class="flex-1 border rounded-xl p-3 resize-none"
                        rows="2"
                    />

                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        class="hidden"
                        @change="handleFileChange"
                    />

                    <button
                        @click="openFilePicker"
                        class="p-3 bg-gray-200 rounded-xl cursor-pointer hover:bg-gray-300 transition"
                    >
                        <PaperclipIcon :size="20" />
                    </button>

                    <button
                        @click="handleSend"
                        :disabled="isSending"
                        class="p-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition"
                    >
                        <SendIcon :size="20" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuery, useQueryClient } from '@tanstack/vue-query'
import api from '../../../lib/axios'
import { useAuthStore } from '../../../context/useAuth'
import {
    X as XIcon,
    User as UserIcon,
    Clock as ClockIcon,
    Download as DownloadIcon,
    Paperclip as PaperclipIcon,
    Send as SendIcon
} from 'lucide-vue-next'

const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const authStore = useAuthStore()
const userRole = computed(() => authStore.user?.role)

// If role is admin-level-1, does not have permission to reply
const isAdminLevelOne = computed(() => userRole.value === 'admin-level-1')

const queryClient = useQueryClient()
const message = ref('')
const attachments = ref([])
const isSending = ref(false)
const fileInput = ref(null)

// Fetch ticket details
const { data, isLoading } = useQuery({
    queryKey: ['adminTicket', props.ticket.id],
    queryFn: async () => {
        const res = await api.get(`/api/v1/admin/tickets/${props.ticket.id}`)
        return res.data
    }
})

const isCustomer = (msg) => msg.user?.role === 'customer'
const formatDate = (date) => new Date(date).toLocaleString()

const openFilePicker = () => {
    if (isAdminLevelOne.value) return  // Prevent file picker from opening if no permission
    fileInput.value?.click()
}

const handleFileChange = (event) => {
    if (isAdminLevelOne.value) {
        event.target.value = ''
        return
    }
    const files = Array.from(event.target.files)
    attachments.value = files
}

const handleSend = async () => {
    // Check permission
    if (isAdminLevelOne.value) {
        alert('You do not have permission to reply to tickets.')
        return
    }

    if (!message.value && attachments.value.length === 0) return

    const formData = new FormData()
    formData.append('message', message.value)
    attachments.value.forEach(file => {
        formData.append('attachments[]', file)
    })

    isSending.value = true
    try {
        await api.post(
            `/api/v1/admin/tickets/${props.ticket.id}/reply`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        )

        message.value = ''
        attachments.value = []
        if (fileInput.value) fileInput.value.value = ''

        queryClient.invalidateQueries({ queryKey: ['adminTicket', props.ticket.id] })
    } catch (error) {
        console.error(error)
        alert('Error sending reply')
    } finally {
        isSending.value = false
    }
}
</script>
