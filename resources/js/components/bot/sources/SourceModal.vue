<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="$emit('close')" class="relative z-50">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/75" aria-hidden="true" />
            </TransitionChild>

            <div class="fixed inset-0 flex items-center justify-center p-4" @click.self="$emit('close')">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel class="w-full max-w-4xl rounded-xl bg-white p-6 transform transition-all" @click.stop>
                        <!-- Close button -->
                        <button @click="$emit('close')"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Header with title and type -->
                        <div class="mb-4">
                            <DialogTitle as="h3" class="text-xl font-semibold text-gray-900">
                                {{ displayTitle }}
                                <span class="text-sm font-medium px-2 py-0.5 bg-gray-100 rounded-md ml-2">
                                    {{ source?.type }}
                                </span>
                            </DialogTitle>
                            <p v-if="source?.type === 'URL' && source?.documents?.[0]?.source"
                                class="mt-1 text-sm text-gray-500">
                                {{ source.documents[0].source }}
                            </p>
                        </div>

                        <!-- Status row -->
                        <div class="flex justify-between items-center mb-6 text-sm text-gray-500">
                            <span :class="statusClass" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                {{ formattedStatus }}
                            </span>
                            <span v-if="source?.indexed_chunks_count" class="text-gray-600">
                                {{ source.indexed_chunks_count }} chunks indexed
                            </span>
                            <span>
                                Updated: {{ new Date().toLocaleString() }}
                            </span>
                        </div>

                        <!-- Scheduled refresh section -->
                        <div v-if="!showDeleteConfirmation" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scheduled refresh</label>
                            <div class="flex items-start space-x-4">
                                <div class="w-64">
                                    <Listbox v-model="source.refresh_schedule">
                                        <div class="relative">
                                            <ListboxButton
                                                class="relative w-full cursor-pointer rounded-lg bg-white py-2 pl-3 pr-10 text-left border border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 sm:text-sm">
                                                <span class="block truncate">{{ source.refresh_schedule }}</span>
                                                <span
                                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                    <ChevronUpDownIcon class="h-5 w-5 text-gray-400"
                                                        aria-hidden="true" />
                                                </span>
                                            </ListboxButton>
                                            <transition leave-active-class="transition duration-100 ease-in"
                                                leave-from-class="opacity-100" leave-to-class="opacity-0">
                                                <ListboxOptions
                                                    class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                                    <ListboxOption
                                                        v-for="schedule in ['never', 'hourly', 'daily', 'weekly']"
                                                        :key="schedule" :value="schedule" v-slot="{ active, selected }">
                                                        <li
                                                            :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-900', 'relative cursor-pointer select-none py-2 pl-10 pr-4']">
                                                            <span
                                                                :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">{{
                                                                    schedule }}</span>
                                                            <span v-if="selected"
                                                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600">
                                                                <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                                            </span>
                                                        </li>
                                                    </ListboxOption>
                                                </ListboxOptions>
                                            </transition>
                                        </div>
                                    </Listbox>
                                </div>
                                <p class="text-sm text-gray-500 pt-1.5">
                                    {{ refreshDescription }}
                                </p>
                            </div>
                        </div>

                        <!-- Delete confirmation card -->
                        <div v-if="showDeleteConfirmation" class="mb-6">
                            <div class="rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete source URL?</h3>
                                <p class="text-gray-600 mb-6">
                                    By choosing to delete this source, you'll be removing all of its pages from your
                                    bot's index. Please be aware that it might take a little while for the changes to
                                    take effect across our services, and for the sources to be fully removed from chat
                                    results. Are you certain you want to proceed with deleting this source?
                                </p>
                                <div class="flex space-x-3">
                                    <button @click="showDeleteConfirmation = false"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-gray-100 text-gray-900 h-9 px-4 py-2 hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button @click="handleDelete"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-600 text-white h-9 px-4 py-2 hover:bg-red-700">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Footer with actions -->
                        <div v-if="!showDeleteConfirmation" class="flex justify-between items-center pt-4">
                            <div class="flex items-center space-x-2">
                                <button @click="showDeleteConfirmation = true"
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-4 py-2 hover:bg-red-50 text-red-600 hover:text-red-700">
                                    Delete
                                </button>
                                <span class="text-xs text-gray-400 truncate max-w-md">
                                    {{ source?.type === 'URL' ? source?.documents?.[0]?.source : source?.title }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button @click="$emit('refresh')"
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-gray-100 text-gray-900 h-9 px-4 py-2 hover:bg-gray-200">
                                    Refresh
                                </button>
                                <button @click="$emit('save')"
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-gray-900 text-gray-50 h-9 px-4 py-2 hover:bg-gray-800">
                                    Save
                                </button>
                            </div>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild, Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

const showDeleteConfirmation = ref(false);

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    source: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'save', 'refresh', 'delete']);

const handleDelete = () => {
    emit('delete', props.source?.id);
    showDeleteConfirmation.value = false;
};

const statusClass = computed(() => {
    switch (props.source?.status) {
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
    if (!props.source?.status) return 'Unknown';

    const statusMap = {
        'queued': 'Queued',
        'indexing': 'Indexing',
        'indexed': 'Indexed',
        'failed': 'Failed'
    };

    return statusMap[props.source.status] || props.source.status.charAt(0).toUpperCase() + props.source.status.slice(1);
});

const refreshDescription = computed(() => {
    switch (props.source?.refresh_schedule) {
        case 'never':
            return 'This source will not be refreshed.';
        case 'hourly':
            return 'This source will be refreshed every hour.';
        case 'daily':
            return 'This source will be refreshed once a day.';
        case 'weekly':
            return 'This source will be refreshed once a week.';
        default:
            return '';
    }
});

const displayTitle = computed(() => {
    if (!props.source) return '';
    if (props.source.type !== 'URL') return props.source.title;

    return props.source.title || // Source title if exists
        props.source.documents?.[0]?.title || // Document title if exists
        props.source.documents?.[0]?.source || // Document source (URL) if exists
        'Untitled'; // Fallback
});
</script>
