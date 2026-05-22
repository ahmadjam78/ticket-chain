<template>
    <div>
        <h1 class="text-xl font-bold mb-4">
            Ticket #123 — Cannot login
        </h1>

        <div class="space-y-3 mb-6">
            <div class="bg-white p-3 shadow rounded">
                <strong>You:</strong>
                <p>I cannot login to my account.</p>
            </div>

            <div class="bg-gray-100 p-3 shadow rounded">
                <strong>Support:</strong>
                <p>Please reset your password.</p>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3">
      <textarea
          v-model="replyMessage"
          placeholder="Write your reply..."
          class="border p-2 w-full h-32"
      />

            <button
                type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
            >
                Send Reply
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const replyMessage = ref('')

const handleSubmit = () => {
    if (!replyMessage.value.trim()) {
        alert('Please enter a reply message.')
        return
    }

    console.log('Sending reply:', replyMessage.value)

    // await api.post('/tickets/123/reply', { message: replyMessage.value })

    replyMessage.value = ''
    alert('Reply sent (demo)')
}
</script>
