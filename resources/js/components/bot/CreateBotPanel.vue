<template>
    <Transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-x-full"
        enter-to-class="translate-x-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-x-0" leave-to-class="translate-x-full">
        <div v-if="isOpen" class="fixed inset-y-0 right-0 w-[480px] bg-white shadow-xl flex flex-col z-[100]">
            <!-- Header -->
            <div class="bg-teal-600 text-white">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-semibold">Create a new Bot</h2>
                    <button @click="handleClose" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 pb-4 text-sm text-white/90">
                    Fill in the information below to create your new bot. Once created you can add source documentation
                    and
                    content then start chatting!
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    <form @submit.prevent="handleCreate" class="space-y-6 pb-20">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Name</label>
                            <input v-model="name" type="text" placeholder="What would you like to call your bot?"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                required />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                            <textarea v-model="description" rows="4"
                                placeholder="(optional) Describe what your bot will do and how it will be used, e.g. 'Ask me anything about my product!'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                        </div>

                        <!-- Privacy -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Privacy</label>
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="isPublic" :value="true"
                                        class="h-4 w-4 text-teal-600 focus:ring-2 focus:ring-teal-500 border-gray-300" />
                                    <span class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Public access</span>
                                        <span class="block text-sm text-gray-500">Allows for embedding on the frontend
                                            of
                                            websites.</span>
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="isPublic" :value="false"
                                        class="h-4 w-4 text-teal-600 focus:ring-2 focus:ring-teal-500 border-gray-300" />
                                    <span class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Private
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">Paid</span>
                                        </span>
                                        <span class="block text-sm text-gray-500">Authenticated API access only. Good
                                            for
                                            internal company content.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- OpenAI Model -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">OpenAI Model</label>
                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="model" value="gpt-4-advanced"
                                        class="h-4 w-4 text-teal-600 focus:ring-2 focus:ring-teal-500 border-gray-300" />
                                    <span class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">GPT-4o - Most Advanced
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800">Paid</span>
                                        </span>
                                        <span class="block text-sm text-gray-500">
                                            Newest (&lt;$0.01/question) model with Oct 2023 knowledge cutoff for
                                            advanced
                                            reasoning or content creation needs. 2x faster than GPT-4 Turbo.
                                            <a href="#" class="ml-1 text-teal-600 hover:text-teal-700">Get access</a>
                                        </span>
                                    </span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" v-model="model" value="gpt-4-mini"
                                        class="h-4 w-4 text-teal-600 focus:ring-2 focus:ring-teal-500 border-gray-300" />
                                    <span class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">GPT-4o Mini - Best Value
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-500 text-white">New!</span>
                                        </span>
                                        <span class="block text-sm text-gray-500">
                                            Newest & most affordable model good for most use cases.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Language -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Language</label>
                            <div class="relative">
                                <select v-model="language"
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md appearance-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                    <option v-for="lang in languages" :key="lang" :value="lang">{{ lang }}</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Note -->
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            You can change these settings later.
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="absolute bottom-0 left-0 right-0 bg-white border-t border-gray-200/60 px-6 py-4">
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="handleClose"
                        class="min-w-[80px] px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="handleCreate"
                        class="min-w-[100px] px-4 py-2 text-sm font-medium text-white bg-teal-500 rounded-md hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        Create Bot
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    isOpen: Boolean
})

const emit = defineEmits(['close', 'create'])

const name = ref('')
const description = ref('')
const isPublic = ref(true)
const model = ref('gpt-4-mini')
const language = ref('English')

const languages = computed(() => [
    'English',
    'Spanish',
    'French',
    'German'
])

const handleCreate = () => {
    const botData = {
        name: name.value,
        description: description.value,
        is_public: isPublic.value,
        model_type: model.value,
        language: language.value
    }
    console.log('Sending bot data:', botData)
    emit('create', botData)
}

const handleClose = () => {
    emit('close')
}
</script>

<style scoped>
.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.translate-x-full {
    transform: translateX(100%);
}

.translate-x-0 {
    transform: translateX(0);
}
</style>
