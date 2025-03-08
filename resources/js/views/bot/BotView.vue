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

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-4">
            <p class="text-gray-500">Loading bot details...</p>
        </div>

        <!-- Bot Details and Sources -->
        <template v-else-if="bot">
            <BotDetailsCard :bot="bot" />

            <!-- Sources List - Always show if exists -->
            <template v-if="bot.sources?.length > 0">
                <BotSourcesList :sources="bot.sources" />
            </template>

            <!-- Add Source Section -->
            <template v-if="showSourcesSection">
                <SourcesSelectionSection @cancel="showSourcesSection = false" @source-added="handleSourceAdded" />
            </template>
            <template v-else>
                <AddSourceSection @add-source="onAddSource" />
            </template>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBotsStore } from '@/stores/botsStore'
import { useToast } from "vue-toastification"
import BotDetailsCard from '@/components/bot/BotDetailsCard.vue'
import AddSourceSection from '@/components/bot/AddSourceSection.vue'
import SourcesSelectionSection from '@/components/bot/SourcesSelectionSection.vue'
import BotSourcesList from '@/components/bot/sources/BotSourcesList.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const botsStore = useBotsStore()

const loading = ref(true)
const bot = computed(() => {
    const currentBot = botsStore.currentBot;
    console.log('Current Bot State:', currentBot);
    return currentBot;
})

// Initialize showSourcesSection based on sources count
const showSourcesSection = ref(false)

const handleSourceAdded = async () => {
    loading.value = true
    showSourcesSection.value = false
    await botsStore.fetchBot(route.params.id)
    loading.value = false
}

const onAddSource = () => {
    console.log('Add source clicked')
    showSourcesSection.value = true
}

onMounted(async () => {
    const routeBot = router.currentRoute.value.state?.bot

    // Set initial bot from route state
    if (routeBot) {
        botsStore.currentBot = routeBot
        // Show sources section initially if no sources
        showSourcesSection.value = !routeBot.sources?.length
        loading.value = false
    }

    // Always fetch fresh bot data
    try {
        loading.value = true
        await botsStore.fetchBot(route.params.id)
        // Show sources section if no sources
        showSourcesSection.value = !botsStore.currentBot.sources?.length
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to fetch bot details")
        router.push('/bots')
    } finally {
        loading.value = false
    }
})

onUnmounted(() => {
    botsStore.clearCurrentBot()
})
</script>
