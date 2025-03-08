<template>
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center space-x-3 mb-6">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h2 class="text-lg font-medium text-gray-900">URL List</h2>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <!-- Left Column - Description -->
                <div>
                    <p class="text-gray-600 text-base">
                        Upload a simple txt or csv file with a list of urls. We will download all html pages in the list,
                        parse the content, and add it to this bot index.
                    </p>
                    <div class="space-y-2 mt-4">
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm block">
                            Learn how to connect to this source
                        </a>
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm block">
                            Download an example CSV urls file
                        </a>
                    </div>
                </div>

                <!-- Right Column - Form -->
                <div class="space-y-8">
                    <!-- Source File Upload -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <label class="text-base text-gray-700">Source file</label>
                            <span class="text-base text-gray-500">Required</span>
                        </div>
                        <div class="mt-1">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
                                :class="{ 'border-red-300': error }"
                                @click="triggerFileInput"
                                @dragover.prevent
                                @drop.prevent="handleFileDrop">
                                <input
                                    type="file"
                                    ref="fileInput"
                                    class="hidden"
                                    accept=".csv,.txt"
                                    @change="handleFileSelect"
                                >
                                <div class="space-y-2">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" :class="{ 'text-red-400': error }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <div v-if="!selectedFile">
                                        <p class="text-gray-700">Upload your source file</p>
                                        <p class="text-sm text-gray-500">CSV, TXT</p>
                                    </div>
                                    <div v-else class="text-gray-700">
                                        {{ selectedFile.name }}
                                    </div>
                                    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Refresh -->
                    <div>
                        <label class="block text-base text-gray-700 mb-1">Scheduled refresh</label>
                        <div class="mt-1">
                            <Listbox v-model="formData.refresh_schedule">
                                <div class="relative">
                                    <ListboxButton
                                        class="relative w-full border border-gray-200 rounded-md py-2 pl-3 pr-10 text-left focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <span class="block truncate text-base">{{ formData.refresh_schedule }}</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                        </span>
                                    </ListboxButton>

                                    <transition leave-active-class="transition duration-100 ease-in"
                                        leave-from-class="opacity-100" leave-to-class="opacity-0">
                                        <ListboxOptions
                                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none">
                                            <ListboxOption v-for="refresh in refreshOptions" :key="refresh"
                                                :value="refresh" v-slot="{ active, selected }">
                                                <li :class="[
                                                    active ? 'text-white bg-blue-600' : 'text-gray-900',
                                                    'relative cursor-default select-none py-2 pl-3 pr-9'
                                                ]">
                                                    <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                                                        {{ refresh }}
                                                    </span>
                                                    <span v-if="selected"
                                                        :class="[active ? 'text-white' : 'text-blue-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                                                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                                    </span>
                                                </li>
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </transition>
                                </div>
                            </Listbox>
                            <p class="mt-2 text-gray-500">This will automatically refresh the source at the selected interval.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-4 rounded-b-lg border-t border-gray-100">
            <button @click="closeForm"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Cancel
            </button>
            <button :disabled="!selectedFile || loading" @click="submitForm" :class="[
                'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                selectedFile && !loading
                    ? 'bg-teal-600 text-white hover:bg-teal-800 focus:ring-teal-400'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            ]">
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ loading ? 'Adding source...' : 'Add source' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/24/solid'
import { useSourcesStore } from '@/stores/sourcesStore';
import { useBotsStore } from '@/stores/botsStore';

const emit = defineEmits(['cancel', 'source-added']);
const sourcesStore = useSourcesStore();
const botsStore = useBotsStore();

const fileInput = ref(null);
const selectedFile = ref(null);
const loading = ref(false);
const error = ref(null);

const formData = ref({
    type: 'URL List',
    refresh_schedule: 'Never',
});

const refreshOptions = ['Never', 'Monthly', 'Weekly', 'Daily'];

const validateFile = (file) => {
    // Check file type only
    const validTypes = ['text/csv', 'text/plain'];
    if (!validTypes.includes(file.type)) {
        error.value = 'Please upload a CSV or TXT file';
        return false;
    }

    return true;
};

const triggerFileInput = () => {
    error.value = null;
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    error.value = null;
    const file = event.target.files[0];

    if (file && validateFile(file)) {
        selectedFile.value = file;
    } else {
        event.target.value = ''; // Clear the input
        selectedFile.value = null;
    }
};

const handleFileDrop = (event) => {
    error.value = null;
    const file = event.dataTransfer.files[0];

    if (file && validateFile(file)) {
        selectedFile.value = file;
    }
};

const closeForm = () => {
    emit('cancel');
};

const submitForm = async () => {
    if (!selectedFile.value) return;

    loading.value = true;
    error.value = null;

    try {
        const formDataToSend = new FormData();
        formDataToSend.append('file', selectedFile.value);
        formDataToSend.append('type', formData.value.type);
        formDataToSend.append('refresh_schedule', formData.value.refresh_schedule.toLowerCase());
        formDataToSend.append('title', selectedFile.value.name);

        await sourcesStore.addSource(botsStore.currentBot.id, formDataToSend);
        botsStore.incrementSourcesCount();
        emit('source-added');
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to upload file';
    } finally {
        loading.value = false;
    }
};
</script>
