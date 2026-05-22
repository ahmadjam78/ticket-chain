<template>
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden flex flex-col">
            <!-- ===== Header ===== -->
            <div class="flex justify-between items-center p-5 border-b bg-blue-600 text-white rounded-t-2xl">
                <div>
                    <h2 class="text-xl font-semibold">{{ ticket.subject }}</h2>
                    <p class="text-sm opacity-90 mt-0.5">Ticket #{{ ticket.id }}</p>
                </div>
                <button @click="emit('close')" class="hover:bg-white/20 rounded-full p-2 transition">
                    <XIcon :size="20" />
                </button>
            </div>

            <!-- ===== Messages ===== -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-4 max-h-[350px]">
                <div v-if="isLoading" class="text-gray-500">Loading messages...</div>

                <template v-else>
                    <div
                        v-for="msg in data?.messages"
                        :key="msg.id"
                        :class="['flex', isCustomer(msg) ? 'justify-end' : 'justify-start']"
                    >
                        <div
                            :class="[
                                'max-w-[75%] rounded-2xl px-4 py-3 flex flex-col shadow',
                                isCustomer(msg)
                                    ? 'bg-blue-100 text-gray-900 rounded-br-md rounded-tl-2xl'
                                    : 'bg-gray-100 text-gray-800 border border-gray-300 rounded-bl-md rounded-tr-2xl'
                            ]"
                        >
                            <!-- Sender Info -->
                            <div class="flex items-center gap-2 text-xs opacity-80 mb-1">
                                <UserIcon :size="12" />
                                <span>{{ msg.user?.name || 'User' }}</span>
                                <ClockIcon :size="12" />
                                <span>{{ formatDate(msg.created_at) }}</span>
                            </div>

                            <!-- Message Text -->
                            <p class="text-sm whitespace-pre-line">{{ msg.message }}</p>

                            <!-- Attachments -->
                            <div v-if="msg.attachments?.length > 0" class="mt-2 flex flex-col gap-1">
                                <a
                                    v-for="file in msg.attachments"
                                    :key="file.id"
                                    :href="file.original_url"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="flex items-center justify-between bg-white hover:bg-gray-50 px-3 py-2 rounded-lg text-sm transition"
                                >
                                    <div class="flex items-center gap-2 truncate max-w-[200px]">
                                        <PaperclipIcon :size="14" />
                                        <span class="truncate">{{ file.file_name }}</span>
                                    </div>
                                    <DownloadIcon :size="16" />
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ===== Reply Box ===== -->
            <div class="p-5 bg-gray-50">
                <!-- Message shown when ticket is not yet approved -->
                <div v-if="isReplyDisabled" class="text-center text-amber-600 bg-amber-50 rounded-xl p-3 text-sm">
                    ⚠️ This ticket is not yet approved. You cannot reply until it is approved.
                </div>

                <!-- Reply box (only shown when ticket status is approved or any other status that allows reply) -->
                <div v-else class="flex items-center gap-2">
                    <textarea
                        v-model="message"
                        placeholder="Write your reply..."
                        class="flex-1 border border-gray-300 rounded-xl p-3 shadow-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none bg-white placeholder-gray-400"
                        rows="2"
                    />

                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        accept="image/*,application/pdf"
                        class="hidden"
                        @change="handleFileChange"
                    />
                    <button
                        @click="openFilePicker"
                        class="p-3 bg-gray-200 rounded-xl hover:bg-gray-300 cursor-pointer transition"
                    >
                        <PaperclipIcon :size="20" />
                    </button>

                    <button
                        @click="handleSend"
                        :disabled="isSending"
                        class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center justify-center shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
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

const queryClient = useQueryClient()
const message = ref('')
const attachments = ref([])
const isSending = ref(false)
const fileInput = ref(null)

// Disabled state of reply box (based on ticket being approved or not)
const isReplyDisabled = computed(() => props.ticket.status !== 'approved')

// Fetch ticket messages (unchanged)
const { data, isLoading } = useQuery({
    queryKey: ['ticket', props.ticket.id],
    queryFn: async () => {
        const res = await api.get(`/api/v1/customer/tickets/${props.ticket.id}`)
        return res.data
    }
})

const isCustomer = (msg) => msg.user?.role === 'customer'
const formatDate = (date) => new Date(date).toLocaleString()

const openFilePicker = () => {
    if (isReplyDisabled.value) return  // Do not allow file selection if reply is disabled
    fileInput.value?.click()
}

const handleFileChange = (event) => {
    if (isReplyDisabled.value) {
        event.target.value = ''
        return
    }
    const files = Array.from(event.target.files)
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']
    const invalidFiles = files.filter(file => !allowedTypes.includes(file.type))

    if (invalidFiles.length > 0) {
        alert('Only image files (JPEG, PNG, GIF, WebP) and PDF are allowed.')
        event.target.value = ''
        attachments.value = []
        return
    }
    attachments.value = files
}

const handleSend = async () => {
    // Check ticket status
    if (isReplyDisabled.value) {
        alert('This ticket is not yet approved. You cannot send a reply.')
        return
    }

    if (!message.value && attachments.value.length === 0) return

    const formData = new FormData()
    formData.append('message', message.value)
    attachments.value.forEach(file => formData.append('attachments[]', file))

    isSending.value = true
    try {
        await api.post(`/api/v1/customer/tickets/${props.ticket.id}/reply`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        message.value = ''
        attachments.value = []
        if (fileInput.value) fileInput.value.value = ''
        queryClient.invalidateQueries({ queryKey: ['ticket', props.ticket.id] })
    } catch (err) {
        console.error(err)
        alert('Error sending reply')
    } finally {
        isSending.value = false
    }
}
</script>
