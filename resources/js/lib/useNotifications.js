import { ref, onMounted } from 'vue'
import api from '../lib/axios'
import {useAuthStore} from "../context/useAuth";

export function useNotifications() {
    const unreadCount = ref(0)
    const authStore = useAuthStore()
    const userId = authStore.user?.id

    const fetchUnreadCount = async () => {
        try {
            const response = await api.get('/api/v1/customer/notifications/'+userId+'/unread-count')
            unreadCount.value = response.data.count || 0
        } catch (err) {
            console.error('Failed to fetch unread count:', err)
        }
    }

    onMounted(() => {
        fetchUnreadCount()
    })

    return { unreadCount, fetchUnreadCount }
}
