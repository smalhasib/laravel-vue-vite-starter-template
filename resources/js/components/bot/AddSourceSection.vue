<template>
    <div class="flex flex-col items-center justify-center py-12">
        <!-- Add Source Icon -->
        <div class="mb-4">
            <svg class="w-16 h-16 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 13h6m-3-3v6m-9-6h.01M9 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2h-2M9 4V3a1 1 0 011-1h4a1 1 0 011 1v1M9 4h6" />
            </svg>
        </div>

        <!-- Title -->
        <h2 class="text-lg font-medium text-gray-900 mb-2">Add source</h2>

        <!-- Description -->
        <p class="text-sm text-gray-600 text-center max-w-md mb-8">
            Add source content to Test Bot that you want your bot to be able to answer questions about. Don't
            index the same content multiple times.
        </p>

        <!-- New Source Button -->
        <button @click="$emit('add-source')"
            class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-base font-medium rounded-md hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-400">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            New Source
        </button>
    </div>
</template>

<script setup>
defineEmits(['add-source'])
</script>
