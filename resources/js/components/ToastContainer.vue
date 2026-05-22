<template>
    <div class="fixed top-4 right-4 z-50 space-y-2 w-80">
        <TransitionGroup name="toast" appear>
            <div
                v-for="toast in activeToasts"
                :key="toast.id"
                class="bg-white rounded-lg shadow-lg border-l-4 cursor-pointer transition-all duration-300 ease-in-out"
                :class="{
          'border-green-500': toast.type === 'success',
          'border-blue-500': toast.type === 'info',
          'border-yellow-500': toast.type === 'warning',
          'border-red-500': toast.type === 'error',
        }"
                @click="handleToastClick(toast)"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-1">
                            <p class="text-sm text-gray-800">{{ toast.message }}</p>
                            <p v-if="toast.detail" class="text-xs text-gray-500 mt-1">{{ toast.detail }}</p>
                        </div>
                        <button
                            @click.stop="removeToast(toast.id)"
                            class="ml-3 text-gray-400 hover:text-gray-600"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const activeToasts = ref([])      // Toasts currently being displayed
const pendingQueue = ref([])      // Queue of toasts waiting to be shown
let isAdding = false              // Whether we are currently scheduling a toast addition
let nextId = 0
const DELAY_BETWEEN_ADD = 500     // Delay between each toast being added (milliseconds)

// Main function: add a toast to the queue
const addToast = (message, type = 'info', duration = 5000, detail = '', onClickPath = '/notifications') => {
    const id = nextId++
    const toast = { id, message, type, duration, detail, onClickPath }
    pendingQueue.value.push(toast)
    processQueue()
}

// Process the queue: gradually add toasts
const processQueue = () => {
    if (isAdding) return
    if (pendingQueue.value.length === 0) return

    isAdding = true
    const nextToast = pendingQueue.value.shift()

    // Add to the active display list
    activeToasts.value.push(nextToast)

    // Schedule automatic removal of this toast
    const removalTimer = setTimeout(() => {
        removeToast(nextToast.id)
    }, nextToast.duration)
    timers.push(removalTimer)

    // Schedule the addition of the next toast (with delay)
    const nextTimer = setTimeout(() => {
        isAdding = false
        processQueue()
    }, DELAY_BETWEEN_ADD)
    timers.push(nextTimer)
}

// Remove a toast from the active list (by click or timeout)
const removeToast = (id) => {
    const index = activeToasts.value.findIndex(t => t.id === id)
    if (index !== -1) activeToasts.value.splice(index, 1)
}

// Click on toast: navigate and remove
const handleToastClick = (toast) => {
    if (toast.onClickPath) {
        router.push(toast.onClickPath)
    }
    removeToast(toast.id)
}

let timers = []

defineExpose({ addToast, removeToast })

onUnmounted(() => {
    timers.forEach(clearTimeout)
    timers = []
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
