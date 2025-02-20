<template>
    <div class="space-y-4">
        <!-- Back Button -->
        <div class="mb-4">
            <router-link to="/bots" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </router-link>
        </div>

        <!-- Bot Details Card with v-if -->
        <template v-if="bot">
            <BotDetailsCard :bot="bot" />

            <!-- Content Sources or Empty State -->
            <ContentSourcesSection v-if="showSourcesSection" @cancel="showSourcesSection = false" />
            <AddSourceEmptyState v-else @click="showSourcesSection = true" />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBotsStore } from '@/stores/botsStore'
import { useToast } from "vue-toastification"
import BotDetailsCard from '@/components/bot/BotDetailsCard.vue'
import AddSourceEmptyState from '@/components/bot/AddSourceEmptyState.vue'
import ContentSourcesSection from '@/components/bot/ContentSourcesSection.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const botsStore = useBotsStore()

// Get bot from router state
const bot = computed(() => {
    // Try to get from router state first
    const routeBot = router.currentRoute.value.state?.bot
    if (routeBot) {
        botsStore.currentBot = routeBot // Update store
        return routeBot
    }
    // Fallback to store
    return botsStore.currentBot
})

// State to control which view to show
const showSourcesSection = ref(false)

onMounted(async () => {
    // If no bot data in route state or store, fetch it
    if (!bot.value) {
        try {
            await botsStore.fetchBot(route.params.id)
            if (!botsStore.currentBot) {
                toast.error("Bot not found")
                router.push('/bots')
                return
            }
        } catch (error) {
            toast.error(error.response?.data?.message || "Failed to fetch bot details")
            router.push('/bots')
            return
        }
    }

    // Show sources section only on initial navigation when no sources
    const hasNoSources = !bot.value?.sources?.length
    const isInitialNavigation = router.options.history.state.forward === null
    showSourcesSection.value = hasNoSources && isInitialNavigation
})
</script>
