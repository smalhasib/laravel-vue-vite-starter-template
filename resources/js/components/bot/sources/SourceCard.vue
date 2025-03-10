<template>
    <div class="flex items-stretch bg-white rounded-lg cursor-pointer hover:shadow-md transition-shadow"
        @click.stop="$emit('click', source)">
        <div class="flex-shrink-0">
            <div class="w-12 h-full bg-teal-600 rounded-l-lg flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0 py-3 px-3">
            <div class="min-w-0">
                <h3 class="text-sm font-medium text-gray-900 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="truncate">{{ source.type }}</span>
                        <span v-if="source.indexed_chunks_count > 0" class="text-xs text-gray-500">
                            ({{ source.indexed_chunks_count }} chunks)
                        </span>
                    </div>
                    <span :class="statusClass"
                        class="px-2 py-0.5 rounded-full text-xs font-medium flex items-center gap-1">
                        {{ formattedStatus }}
                        <svg v-if="source.status === 'indexing'" class="animate-spin h-3 w-3"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </h3>
                <div class="mt-1">
                    <template v-if="source.type === 'URL'">
                        <p class="text-xs text-gray-500 truncate">{{ displayTitle }}</p>
                    </template>
                    <template v-else-if="source.type === 'URL List'">
                        <p class="text-xs text-gray-500 truncate">
                            {{ source.title }}
                        </p>
                    </template>
                    <template v-else>
                        <p class="text-xs text-gray-500 truncate">
                            {{ source.title || 'Untitled' }}
                        </p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    source: {
        type: Object,
        required: true
    }
});

defineEmits(['click']);

const statusClass = computed(() => {
    switch (props.source.status) {
        case 'queued':
            return 'bg-yellow-100 text-yellow-800';
        case 'indexing':
            return 'bg-blue-100 text-blue-800';
        case 'indexed':
            return 'bg-green-100 text-green-800';
        case 'failed':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const formattedStatus = computed(() => {
    if (!props.source.status) return 'Unknown';

    const statusMap = {
        'queued': 'Queued',
        'indexing': 'Indexing',
        'indexed': 'Indexed',
        'failed': 'Failed'
    };

    return statusMap[props.source.status] || props.source.status.charAt(0).toUpperCase() + props.source.status.slice(1);
});

const displayTitle = computed(() => {
    if (props.source.type !== 'URL') return props.source.title;
    return props.source.title || 'Untitled';
});
</script>
