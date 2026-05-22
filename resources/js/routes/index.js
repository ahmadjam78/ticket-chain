import { createRouter, createWebHistory } from 'vue-router'
import {useAuth, useAuthStore} from '../context/useAuth'

// Pages
import LoginPage from '../modules/auth/pages/LoginPage.vue'
import RegisterPage from '../modules/auth/pages/RegisterPage.vue'
import TicketListPage from '../modules/ticket/pages/TicketListPage.vue'
import NewTicketPage from '../modules/ticket/pages/NewTicketPage.vue'
import AdminTicketListPage from '../modules/ticket/pages/AdminTicketListPage.vue'
import AdminUserListPage from '../modules/user/pages/AdminUserListPage.vue'
import AdminNotificationsPage from '../modules/user/pages/AdminNotificationsPage.vue'
import AdminLogsPage from '../modules/log/pages/AdminLogsPage.vue'
import CustomerNotificationsPage from '../modules/notification/pages/CustomerNotificationsPage.vue'

// Layouts
import CustomerLayout from '../layouts/CustomerLayout.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import api from "../lib/axios";

const routes = [
    { path: '/', component: LoginPage },
    { path: '/login', component: LoginPage },
    { path: '/register', component: RegisterPage },

    // Customer
    {
        path: '/tickets',
        component: CustomerLayout,
        meta: { role: 'customer' },
        children: [
            { path: '', component: TicketListPage },
            { path: 'new', component: NewTicketPage },
        ],
    },

    {
        path: '/notifications',
        component: CustomerLayout,
        meta: { role: 'customer' },
        children: [
            { path: '', component: CustomerNotificationsPage },
        ],
    },

    // Admin
    {
        path: '/admin',
        component: AdminLayout,
        // No role meta at parent level; each child defines its own role
        children: [
            {
                path: 'tickets',
                component: AdminTicketListPage,
                meta: { role: ['admin-level-1', 'admin-level-2'] } // both levels have access
            },
            {
                path: 'users',
                component: AdminUserListPage,
                meta: { role: 'admin-level-2' },
                children: [
                    { path: 'notifications', component: AdminNotificationsPage, meta: { role: ['admin-level-1', 'admin-level-2'] }},
                ],
            },
            {
                path: 'logs',
                component: AdminLogsPage,
                meta: { role: 'admin-level-2' }
            }
        ]
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to, from, next) => {
    const authStore = useAuth()
    const user = authStore.user
    const initialized = authStore.initialized

    if (!initialized.value) {
        let requiredRole = to.meta.role
        let userRole = undefined;
        try {
            const res = await api.get('/api/v1/user')
            user.value = res.data.data
            userRole = user?.value?.role

            if (requiredRole) {
                if (!userRole) {
                    next('/login')
                    return
                }

                // Check allowed role (array or string)
                const allowedRoles = Array.isArray(requiredRole) ? requiredRole : [requiredRole]
                if (!allowedRoles.includes(userRole)) {
                    next('/login')
                    return
                }
            }
        } catch (err) {
            next('/login')
            return
        }
        if (userRole && (to.path === '/' || to.path === '/login' || to.path === '/register')) {
            if (userRole === 'customer') {
                return next('/tickets')
            } else if (userRole === 'admin-level-1') {
                return next('/admin/tickets')
            } else if (userRole === 'admin-level-2') {
                return next('/admin/tickets')
            }
        }
        next()
        return
    }

    const requiredRole = to.meta.role
    const userRole = user?.value?.role

    if (requiredRole) {
        if (!userRole) {
            // Not logged in
            next('/login')
            return
        }

        // Check allowed role (array or string)
        const allowedRoles = Array.isArray(requiredRole) ? requiredRole : [requiredRole]
        if (!allowedRoles.includes(userRole)) {
            // Unauthorized access → redirect to login (or error page)
            next('/login')
            return
        }
    }

    // If user is logged in and trying to access login/register pages → redirect to appropriate dashboard
    if (userRole && (to.path === '/' || to.path === '/login' || to.path === '/register')) {
        if (userRole === 'customer') {
            return next('/tickets')
        } else if (userRole === 'admin-level-1') {
            return next('/admin/tickets')
        } else if (userRole === 'admin-level-2') {
            return next('/admin/tickets')
        }
    }

    next()
})

export default router
