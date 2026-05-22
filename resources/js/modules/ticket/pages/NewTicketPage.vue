<template>
    <div class="max-w-5xl mx-auto mt-2 px-4">
        <div class="flex items-center mb-6">
            <button
                @click="goBack"
                class="flex items-center gap-1 text-gray-600 hover:text-gray-800 transition"
            >
                <ArrowLeftIcon :size="20" />
                Back
            </button>
            <h1 class="text-2xl font-bold ml-4">Create New Ticket</h1>
        </div>

        <!-- ===== Category & Subcategory ===== -->
        <div class="space-y-4 mb-6">
            <!-- Category -->
            <div class="relative">
                <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                    <ChevronDownIcon :size="16" />
                    Category
                </label>
                <select
                    v-model="selectedCategory"
                    class="w-full mt-1 p-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
                    <option value="">Select category</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>

            <!-- Subcategory -->
            <div v-if="subcategories.length > 0" class="relative">
                <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                    <ChevronDownIcon :size="16" />
                    Subcategory
                </label>
                <select
                    v-model="selectedSubcategory"
                    class="w-full mt-1 p-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                >
                    <option value="">Select subcategory</option>
                    <option v-for="sub in subcategories" :key="sub.id" :value="sub.id">
                        {{ sub.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- ===== Message when Category/Subcategory not selected ===== -->
        <div
            v-if="!showForm"
            class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-4 rounded-lg mb-4 transition-opacity duration-300"
        >
            Please select both Category and Subcategory to continue.
        </div>

        <!-- ===== Form Fields (Fade-In) ===== -->
        <div
            :class="[
                'transition-all duration-300 ease-in-out',
                showForm ? 'opacity-100 max-h-[2000px] mb-6' : 'opacity-0 max-h-0'
            ]"
        >
            <form @submit.prevent="onSubmit" class="bg-white shadow-xl rounded-2xl border border-gray-100 p-8 space-y-6">
                <!-- Subject -->
                <div class="relative">
                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                        <FileTextIcon :size="16" />
                        Subject
                    </label>
                    <input
                        v-model="form.subject"
                        type="text"
                        placeholder="Enter ticket subject"
                        class="mt-2 w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                        required
                    />
                </div>

                <!-- Description -->
                <div class="relative">
                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                        <FileIcon :size="16" />
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        placeholder="Describe your issue..."
                        class="mt-2 w-full px-4 pt-2.5 pb-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition h-40 resize-none"
                        required
                    />
                </div>

                <!-- Attachment -->
                <div class="relative">
                    <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                        <UploadIcon :size="16" />
                        Attachment
                    </label>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*,application/pdf"
                        class="hidden"
                        @change="handleFileChange"
                    />
                    <button
                        type="button"
                        @click="openFilePicker"
                        class="mt-2 flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                    >
                        <FileIcon class="text-gray-500" :size="16" />
                        {{ attachmentName || 'Choose a file...' }}
                    </button>
                    <p class="text-xs text-gray-400 mt-1">
                        Required. You must attach an image or PDF.   <!-- تغییر متن -->
                    </p>
                </div>

                <!-- Priority -->
                <div class="relative">
                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                        <ChevronDownIcon :size="16" />
                        Priority
                    </label>
                    <select
                        v-model="form.priority"
                        class="w-full mt-1 p-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="!canSubmit"
                    :class="[
                        'w-full flex items-center justify-center gap-2 text-white font-semibold py-2.5 rounded-lg shadow-sm transition',
                        canSubmit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'
                    ]"
                >
                    <SendIcon :size="20" />
                    {{ isSubmitting ? 'Submitting...' : 'Submit Ticket' }}
                </button>

                <!-- Error message (if any) -->
                <p v-if="errorMessage" class="text-red-600 text-sm text-center">
                    {{ errorMessage }}
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMutation } from '@tanstack/vue-query'
import api from '../../../lib/axios'
import {
    ArrowLeft as ArrowLeftIcon,
    ChevronDown as ChevronDownIcon,
    FileText as FileTextIcon,
    File as FileIcon,
    Upload as UploadIcon,
    Send as SendIcon
} from 'lucide-vue-next'

const router = useRouter()
const fileInput = ref(null)
const selectedCategory = ref('')
const selectedSubcategory = ref('')
const categories = ref([])
const subcategories = ref([])
const errorMessage = ref('')
const attachmentFile = ref(null)

const form = reactive({
    subject: '',
    description: '',
    priority: 'medium'
})

const attachmentName = computed(() => attachmentFile.value?.name || '')

onMounted(async () => {
    try {
        const res = await api.get('/api/v1/customer/ticket-categories')
        categories.value = res.data.data
    } catch (err) {
        console.error(err)
    }
})

watch(selectedCategory, (newCat) => {
    if (!newCat) {
        subcategories.value = []
        selectedSubcategory.value = ''
        return
    }
    const category = categories.value.find(c => c.id === parseInt(newCat))
    subcategories.value = category?.children || []
    selectedSubcategory.value = ''
})

const showForm = computed(() => selectedCategory.value && selectedSubcategory.value)

const { mutate: createTicket, isPending: isSubmitting } = useMutation({
    mutationFn: async (formData) => {
        const res = await api.post('/api/v1/customer/tickets', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        return res.data
    },
    onSuccess: () => {
        alert('Ticket created successfully!')
        form.subject = ''
        form.description = ''
        form.priority = 'medium'
        attachmentFile.value = null
        selectedCategory.value = ''
        selectedSubcategory.value = ''
        errorMessage.value = ''
        if (fileInput.value) fileInput.value.value = ''
        router.push('/tickets')
    },
    onError: (err) => {
        console.error(err)
        alert('Failed to create ticket')
    }
})
// -----------------------------


const canSubmit = computed(() =>
    selectedCategory.value &&
    selectedSubcategory.value &&
    form.subject.trim() !== '' &&
    form.description.trim() !== '' &&
    !isSubmitting.value &&
    attachmentFile.value !== null
)

const openFilePicker = () => {
    fileInput.value?.click()
}

const handleFileChange = (event) => {
    const file = event.target.files?.[0]
    if (!file) {
        attachmentFile.value = null
        return
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']
    if (!allowedTypes.includes(file.type)) {
        alert('Only image files (JPEG, PNG, GIF, WebP) and PDF are allowed.');
        event.target.value = ''
        attachmentFile.value = null
        return
    }

    attachmentFile.value = file
}

const onSubmit = () => {
    if (!selectedCategory.value || !selectedSubcategory.value) {
        errorMessage.value = 'Please select Category and Subcategory first.'
        return
    }
    if (!form.subject.trim()) {
        errorMessage.value = 'Subject is required.'
        return
    }
    if (!form.description.trim()) {
        errorMessage.value = 'Description is required.'
        return
    }
    if (!attachmentFile.value) {
        errorMessage.value = 'Please attach an image or PDF file.'
        return
    }

    const formData = new FormData()
    formData.append('subject', form.subject)
    formData.append('description', form.description)
    formData.append('priority', form.priority)
    formData.append('category_id', parseInt(selectedSubcategory.value))
    formData.append('attachments[]', attachmentFile.value)

    createTicket(formData)
}

const goBack = () => {
    router.go(-1)
}
</script>
