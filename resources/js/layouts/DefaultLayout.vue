<template>
    <div class="min-h-screen bg-gray-100 flex">
        <Sidebar />
        <div class="flex-1 ml-64">
            <!-- New navbar -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-semibold">{{ $route.name }}</div>
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
import { useRouter } from 'vue-router'
import Sidebar from '../components/Sidebar.vue'
import UserMenu from '../components/UserMenu.vue'

const router = useRouter()
</script>
