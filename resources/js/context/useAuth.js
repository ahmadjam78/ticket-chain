import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../lib/axios'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const loading = ref(true)
    const initialized = ref(false)

    const fetchUser = async () => {
        try {
            const res = await api.get('/api/v1/user')
            user.value = res.data.data
        } catch (err) {
            user.value = null
        } finally {
            loading.value = false
            initialized.value = true
        }
    }

    const setUser = (newUser) => {
        user.value = newUser
    }

    const clearUser = () => {
        user.value = null
        // If the token is stored in localStorage, remove it as well
        localStorage.removeItem('auth_token')
        // If needed, clear other authentication data
    }

    const setUserAndInit = (newUser) => {
        user.value = newUser
        initialized.value = true
    }

    return { user, loading, initialized, fetchUser, setUser, clearUser, setUserAndInit }
})


import { storeToRefs } from 'pinia'

export function useAuth() {
    const authStore = useAuthStore()
    // Extract refs for reactivity in components
    const { user, loading, initialized } = storeToRefs(authStore)
    const { fetchUser, setUser, clearUser, setUserAndInit  } = authStore

    return { user, loading, initialized, fetchUser, setUser, clearUser, setUserAndInit }
}
