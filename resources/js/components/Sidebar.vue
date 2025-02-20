<template>
    <div class="w-64 bg-gray-800 h-screen fixed left-0 top-0">
        <div class="h-16 flex items-center px-6">
            <h1 class="text-white text-xl font-bold">FluentBot</h1>
        </div>

        <nav class="mt-6">
            <div v-for="item in navItems" :key="item.name" class="px-3 py-2">
                <button @click="navigate(item.route)"
                    class="flex items-center w-full px-3 py-2 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white transition-colors"
                    :class="{ 'bg-gray-700 text-white': $route.path === item.route }">
                    <span v-html="item.icon" class="mr-3"></span>
                    <span>{{ item.name }}</span>
                </button>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

/**
 * @typedef {Object} NavItem
 * @property {string} name
 * @property {string} icon
 * @property {string} route
 */

const router = useRouter()
const navItems = ref([
    {
        name: 'Dashboard',
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>`,
        route: '/dashboard'
    },
    {
        name: 'Bots',
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>`,
        route: '/bots'
    },
    {
        name: 'Team',
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>`,
        route: '/team'
    },
    {
        name: 'Account',
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`,
        route: '/account'
    },
    {
        name: 'API/Integrations',
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>`,
        route: '/integrations'
    }
])

const navigate = (route) => {
    router.push(route)
}
</script>
