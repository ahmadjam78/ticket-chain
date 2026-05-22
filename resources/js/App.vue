<template>
    <RouterView />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuth } from './context/useAuth'

const { fetchUser } = useAuth()

onMounted(() => {
    fetchUser()
})
</script>
