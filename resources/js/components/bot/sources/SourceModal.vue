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
                    <DialogPanel
                        class="w-full max-w-4xl rounded-xl bg-white p-6 transform transition-all max-h-[85vh] overflow-y-auto"
                        @click.stop>
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
                            <span :class="statusClass"
                                class="px-2 py-0.5 rounded-full text-xs font-medium flex items-center gap-1">
                                {{ formattedStatus }}
                                <svg v-if="source?.status === 'indexing'" class="animate-spin h-3 w-3"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            <span v-if="source?.indexed_chunks_count" class="text-gray-600">
                                {{ source.indexed_chunks_count }} chunks indexed
                            </span>
                            <span>
                                Updated: {{ new Date().toLocaleString() }}
                            </span>
                        </div>

                        <!-- Document Content Section -->
                        <div class="mb-6">
                            <!-- Add Document Button - Only for URL List -->
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-medium text-gray-700">
                                    Documents
                                    <span v-if="source?.type === 'URL List' && source?.documents?.length > 0"
                                        class="text-gray-500">
                                        ({{ filteredDocuments.length }} of {{ source.documents.length }})
                                    </span>
                                </h4>
                                <button v-if="source?.type === 'URL List'" @click="showAddDocumentForm = true"
                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-teal-600 text-white rounded-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Document
                                </button>
                            </div>

                            <!-- Add Document Form - Only for URL List -->
                            <div v-if="showAddDocumentForm && source?.type === 'URL List'"
                                class="mb-4 p-4 border border-gray-200 rounded-lg">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Document URL</label>
                                        <input type="url" v-model="newDocument.url"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                                            placeholder="https://example.com/document">
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <button @click="showAddDocumentForm = false"
                                            class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800">
                                            Cancel
                                        </button>
                                        <button @click="addDocument"
                                            class="px-3 py-1.5 text-sm bg-teal-600 text-white rounded-md hover:bg-teal-700">
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- For URL type sources -->
                            <div v-if="source?.type === 'URL' && source?.documents?.[0]"
                                class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 cursor-pointer"
                                        @click="selectedDocument = source.documents[0]; showDocumentModal = true">
                                        <h5 class="text-sm font-medium text-gray-900">{{ source.documents[0].title }}
                                        </h5>
                                        <p class="text-xs text-gray-500 mt-1">{{ source.documents[0].source }}</p>
                                    </div>
                                    <button @click.stop="confirmDeleteDocument(source.documents[0])"
                                        class="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-gray-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- For URL List type sources -->
                            <div v-else-if="source?.type === 'URL List' && source?.documents?.length > 0">
                                <!-- Search box moved outside container -->
                                <div class="mb-4">
                                    <div class="relative">
                                        <input type="text" v-model="searchQuery" placeholder="Search documents..."
                                            class="w-full px-4 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent" />
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Documents container with background -->
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2">
                                        <div v-for="doc in filteredDocuments" :key="doc.id"
                                            class="p-3 border border-gray-200 rounded-lg bg-white hover:bg-gray-50">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1 cursor-pointer"
                                                    @click="selectedDocument = doc; showDocumentModal = true">
                                                    <h5 class="text-sm font-medium text-gray-900">{{ doc.title }}</h5>
                                                    <p class="text-xs text-gray-500 mt-1">{{ doc.source }}</p>
                                                </div>
                                                <button @click.stop="confirmDeleteDocument(doc)"
                                                    class="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-gray-100">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Modal -->
                        <DocumentModal v-if="showDocumentModal" :is-open="showDocumentModal"
                            :document="selectedDocument" @close="showDocumentModal = false" />

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

                        <!-- Document Delete confirmation card -->
                        <div v-if="showDocumentDeleteConfirmation" class="mb-6">
                            <div class="rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete document?</h3>
                                <p class="text-gray-600 mb-6">
                                    Are you sure you want to delete this document? This will remove the document from
                                    your bot's index. This action cannot be undone.
                                </p>
                                <div class="flex space-x-3">
                                    <button @click="showDocumentDeleteConfirmation = false"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-gray-100 text-gray-900 h-9 px-4 py-2 hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button @click="deleteDocument"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-600 text-white h-9 px-4 py-2 hover:bg-red-700">
                                        Delete
                                    </button>
                                </div>
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
import { ref, computed, watch, onUnmounted } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild, Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';
import { marked } from 'marked';
import DocumentModal from './DocumentModal.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { useBotsStore } from '@/stores/botsStore';
import { useSourcesStore } from '@/stores/sourcesStore';

const toast = useToast();
const botsStore = useBotsStore();
const sourcesStore = useSourcesStore();
const showDeleteConfirmation = ref(false);
const showDocumentDeleteConfirmation = ref(false);
const showDocumentModal = ref(false);
const selectedDocument = ref(null);
const searchQuery = ref('');
const showAddDocumentForm = ref(false);
const documentToDelete = ref(null);
const pollingInterval = ref(null);
const newDocument = ref({
    url: ''
});
const processingDocumentId = ref(null);

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

// Polling functions
const pollSourceStatus = async () => {
    try {
        const response = await axios.get(`/api/bots/${props.source.bot_id}/sources/${props.source.id}/status`);
        const updatedSource = response.data;

        // If we're tracking a specific document
        if (processingDocumentId.value) {
            const processedDoc = updatedSource.documents?.find(doc => doc.id === processingDocumentId.value);
            if (processedDoc && updatedSource.status === 'indexed') {
                // Update bot's total chunks with just this document's chunks
                botsStore.updateBotChunksCount(props.source.bot_id, processedDoc.indexed_chunks_count);
                processingDocumentId.value = null; // Reset the tracking
                emit('refresh');
            }
        }

        // Update source properties
        props.source.status = updatedSource.status;
        props.source.indexed_chunks_count = updatedSource.documents?.reduce((total, doc) => total + (doc.indexed_chunks_count || 0), 0) || 0;
        props.source.documents = updatedSource.documents;

        // Stop polling if processing is complete
        if (updatedSource.status === 'indexed' || updatedSource.status === 'failed') {
            stopPolling();
        }
    } catch (error) {
        console.error('Failed to poll source status:', error);
        stopPolling(); // Stop polling on error
    }
};

const startPolling = () => {
    if (!pollingInterval.value) {
        pollSourceStatus();
        pollingInterval.value = setInterval(pollSourceStatus, 5000); // Poll every 5 seconds
    }
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

// Watch for status changes
watch(() => props.source?.status, (newStatus) => {
    if (newStatus === 'queued' || newStatus === 'indexing') {
        startPolling();
    } else {
        stopPolling();
    }
}, { immediate: true });

// Cleanup on unmount
onUnmounted(() => {
    stopPolling();
});

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

    // For URL type sources, prioritize source title if it exists
    return props.source.title || // Source title if exists
        props.source.documents?.[0]?.title || // Document title if exists
        'Untitled'; // Fallback
});

const renderedContent = computed(() => {
    return marked(props.source?.documents?.[0]?.content || '');
});

const filteredDocuments = computed(() => {
    if (!props.source?.documents) return [];

    // Documents are already sorted by created_at desc from the backend
    if (!searchQuery.value) return props.source.documents;

    const query = searchQuery.value.toLowerCase();
    return props.source.documents.filter(doc =>
        doc.title.toLowerCase().includes(query) ||
        doc.source.toLowerCase().includes(query)
    );
});

const addDocument = async () => {
    if (!newDocument.value.url) {
        toast.error('Please enter a document URL');
        return;
    }

    try {
        const response = await axios.post(`/api/bots/${props.source.bot_id}/sources/${props.source.id}/documents`, {
            url: newDocument.value.url
        });

        // Store the ID of the document we're processing
        processingDocumentId.value = response.data.id;

        // Reset form
        newDocument.value = { url: '' };
        showAddDocumentForm.value = false;

        // Update source status and start polling
        props.source.status = 'queued';

        // Start polling both in the component and store
        startPolling();
        sourcesStore.startPolling(props.source.bot_id, props.source.id);

        toast.success('Document added to processing queue. It will be scraped and indexed shortly.');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to add document');
    }
};

const confirmDeleteDocument = (document) => {
    documentToDelete.value = document;
    showDocumentDeleteConfirmation.value = true;
};

const deleteDocument = async () => {
    if (!documentToDelete.value) return;

    try {
        await axios.delete(
            `/api/bots/${props.source.bot_id}/sources/${props.source.id}/documents/${documentToDelete.value.id}`
        );

        // Update source status and start polling
        props.source.status = 'indexing';

        // Start polling both in the component and store
        startPolling();
        sourcesStore.startPolling(props.source.bot_id, props.source.id);

        toast.success('Document deleted successfully');

        // Reset state
        documentToDelete.value = null;
        showDocumentDeleteConfirmation.value = false;
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to delete document');
    }
};
</script>
