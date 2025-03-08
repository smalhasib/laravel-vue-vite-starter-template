<template>
    <div class="space-y-6">
        <!-- Header and Description -->
        <div v-if="hasBots">
            <p class="text-gray-600 mb-6">
                These are your custom trained DocsBots. You can create a new one, or train them with new sources.
            </p>
        </div>

        <!-- Bot List -->
        <div v-if="hasBots" class="space-y-4">
            <BotCard
                v-for="bot in bots"
                :key="bot.id"
                :bot="bot"
                @delete="openDeleteModal"
            />
        </div>

        <!-- Create New Bot Section -->
        <div class="flex justify-center" :class="{ 'mt-12': !hasBots }">
            <button @click="handleCreateBot" data-create-bot
                class="w-full max-w-md border-2 border-dashed border-gray-300 rounded-lg p-8 hover:border-teal-500 hover:bg-teal-50/50 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-teal-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Create a new bot</h3>
                    <p class="mt-1 text-sm text-gray-500">Train a new bot with your own documentation and content.</p>
                </div>
            </button>
        </div>

        <!-- Create Bot Panel -->
        <CreateBotPanel :is-open="showCreatePanel" @close="handleClosePanel" @create="handleCreateSubmit" />

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmationModal :is-open="showDeleteModal" :bot-name="botToDelete?.name" @close="closeDeleteModal"
            @confirm="confirmDelete" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBotsStore } from '@/stores/botsStore'
import CreateBotPanel from '@/components/bot/CreateBotPanel.vue'
import DeleteConfirmationModal from '@/components/bot/DeleteConfirmationModal.vue'
import BotCard from '@/components/bot/BotCard.vue'

const router = useRouter()
const botsStore = useBotsStore()
const showCreatePanel = ref(false)
const showDeleteModal = ref(false)
const botToDelete = ref(null)

const bots = computed(() => botsStore.bots)
const hasBots = computed(() => botsStore.hasBots)

const handleCreateBot = () => {
    showCreatePanel.value = true
}

onMounted(async () => {
    await botsStore.fetchBots()
})

const handleCreateSubmit = async (data) => {
    try {
        await botsStore.addBot(data)
        showCreatePanel.value = false
    } catch (error) {
        console.error('Failed to create bot:', error)
    }
}

const handleClosePanel = () => {
    showCreatePanel.value = false
}

const openDeleteModal = (bot) => {
    botToDelete.value = bot
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    botToDelete.value = null
}

const confirmDelete = async () => {
    try {
        await botsStore.deleteBot(botToDelete.value.id)
        closeDeleteModal()
    } catch (error) {
        console.error('Failed to delete bot:', error)
    }
}
</script>
