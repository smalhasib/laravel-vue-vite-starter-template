<template>
    <div class="bg-white rounded-lg shadow p-6 transition-all duration-200 hover:shadow-md hover:scale-[1.01] cursor-pointer"
        @click="navigateToBot">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">{{ bot.name }}</h3>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1 text-sm rounded-full" :class="{
                            'bg-yellow-100 text-yellow-800': bot.status === 'Awaiting Sources',
                            'bg-green-100 text-green-800': bot.status === 'Active'
                        }">
                            {{ bot.status }}
                        </span>
                        <button @click.stop="$emit('delete', bot)"
                            class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100"
                            title="Delete Bot">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-2 flex items-center space-x-6 text-sm text-gray-500">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                        </svg>
                        <span>Public</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ bot.created_at }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>{{ bot.sources_count }} Sources</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ bot.questions_count }} Questions</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
    bot: {
        type: Object,
        required: true
    }
})

defineEmits(['delete'])

const navigateToBot = () => {
    router.push({
        name: 'bot.view',
        params: { id: props.bot.id },
        state: { bot: props.bot }
    })
}
</script>
