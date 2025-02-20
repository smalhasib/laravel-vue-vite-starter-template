<template>
    <div class="min-h-screen bg-gray-100 flex">
        <Sidebar />
        <div class="flex-1 ml-64">
            <!-- Navbar -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="text-lg">
                            <Breadcrumb :breadcrumbs="currentBreadcrumbs" />
                        </div>
                        <UserMenu />
                    </div>
                </div>
            </nav>

            <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useBotsStore } from '@/stores/botsStore'
import Sidebar from '../components/Sidebar.vue'
import UserMenu from '../components/UserMenu.vue'
import Breadcrumb from '../components/Breadcrumb.vue'

const router = useRouter()
const route = useRoute()
const botsStore = useBotsStore()

const currentBreadcrumbs = computed(() => {
    // If route has meta breadcrumb function, use it
    if (route.meta.breadcrumb) {
        if (typeof route.meta.breadcrumb === 'function') {
            return route.meta.breadcrumb(route)
        }
        return route.meta.breadcrumb
    }

    const breadcrumbs = []

    // Handle different routes
    switch (route.name) {
        case 'Dashboard':
            breadcrumbs.push({
                name: 'Dashboard',
                current: true
            })
            break

        case 'bots':
            breadcrumbs.push({
                name: 'Bots',
                current: true
            })
            break

        case 'bot.view':
            breadcrumbs.push({
                name: 'Bots',
                to: '/bots'
            })
            breadcrumbs.push({
                name: botsStore.currentBot?.name || 'Bot Details',
                current: true
            })
            break

        case 'Team':
            breadcrumbs.push({
                name: 'Team',
                current: true
            })
            break

        case 'Account':
            breadcrumbs.push({
                name: 'Account',
                current: true
            })
            break

        case 'API/Integrations':
            breadcrumbs.push({
                name: 'API/Integrations',
                current: true
            })
            break
    }

    return breadcrumbs
})

// Clear currentBot when navigating away from bot details
watch(() => route.name, (newRouteName) => {
    if (newRouteName !== 'bot.view') {
        botsStore.clearCurrentBot()
    }
})
</script>
