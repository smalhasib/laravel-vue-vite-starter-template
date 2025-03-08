<template>
    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-900">Sources</h2>
        <div class="grid grid-cols-3 gap-4">
            <SourceCard v-for="source in sources" :key="source.id" :source="source" @click="openSourceModal" />
        </div>

        <SourceModal :is-open="isModalOpen" :source="selectedSource" @close="closeModal" @save="saveSource"
            @refresh="refreshSource" @delete="deleteSource" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useSourcesStore } from '@/stores/sourcesStore';
import { useBotsStore } from '@/stores/botsStore';
import SourceCard from './SourceCard.vue';
import SourceModal from './SourceModal.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const props = defineProps({
    sources: {
        type: Array,
        required: true
    }
});

const sourcesStore = useSourcesStore();
const botsStore = useBotsStore();

const isModalOpen = ref(false);
const selectedSource = ref(null);

// Handle source status updates
const handleSourceStatusUpdate = (event) => {
    const updatedSource = event.detail;
    const sourceIndex = props.sources.findIndex(s => s.id === updatedSource.id);

    if (sourceIndex !== -1) {
        // Update the source while maintaining the order
        const updatedSources = [...props.sources];
        updatedSources[sourceIndex] = {
            ...updatedSources[sourceIndex],
            ...updatedSource
        };
        // Sort sources by created_at in descending order
        updatedSources.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        props.sources.splice(0, props.sources.length, ...updatedSources);

        // If this source is currently selected in the modal, update it
        if (selectedSource.value?.id === updatedSource.id) {
            selectedSource.value = {
                ...selectedSource.value,
                ...updatedSource
            };
        }
    }
};

onMounted(() => {
    // Sort initial sources by created_at in descending order
    props.sources.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    // Start polling for any non-final status sources
    props.sources.forEach(source => {
        if (source.status !== 'indexed' && source.status !== 'failed') {
            sourcesStore.startPolling(botsStore.currentBot.id, source.id);
        }
    });

    // Listen for status updates
    document.addEventListener('source-status-updated', handleSourceStatusUpdate);
});

onUnmounted(() => {
    // Clean up all polling intervals
    sourcesStore.stopAllPolling();
    document.removeEventListener('source-status-updated', handleSourceStatusUpdate);
});

const openSourceModal = async (source) => {
    if (!source) return;
    try {
        // Fetch full source details including documents
        const response = await axios.get(`/api/bots/${botsStore.currentBot.id}/sources/${source.id}`);
        selectedSource.value = response.data;
        isModalOpen.value = true;
    } catch (error) {
        console.error('Failed to fetch source details:', error);
        toast.error('Failed to load source details');
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedSource.value = null;
    }, 200); // Clear after transition ends
};

const refreshSource = async () => {
    if (!selectedSource.value) return;
    try {
        await sourcesStore.refreshSource(botsStore.currentBot.id, selectedSource.value.id);
        // Start polling for the refreshed source
        sourcesStore.startPolling(botsStore.currentBot.id, selectedSource.value.id);
        botsStore.fetchBot(botsStore.currentBot.id);
    } catch (error) {
        console.error('Failed to refresh source:', error);
    }
};

const saveSource = async () => {
    if (!selectedSource.value) return;
    try {
        await sourcesStore.updateSource(botsStore.currentBot.id, selectedSource.value.id, {
            refresh_schedule: selectedSource.value.refresh_schedule
        });
        closeModal();
        botsStore.fetchBot(botsStore.currentBot.id);
    } catch (error) {
        console.error('Failed to save source:', error);
    }
};

const deleteSource = async (sourceId) => {
    try {
        await sourcesStore.deleteSource(botsStore.currentBot.id, sourceId);
        closeModal();
        botsStore.fetchBot(botsStore.currentBot.id);
    } catch (error) {
        console.error('Failed to delete source:', error);
    }
};
</script>
