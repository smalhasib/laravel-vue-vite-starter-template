<template>
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center space-x-3 mb-6">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <h2 class="text-lg font-medium text-gray-900">URL</h2>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <!-- Left Column - Description -->
                <div>
                    <p class="text-gray-600 text-base">
                        Add the URL of a single webpage to learn from. This can be a blog post, a news article, or
                        any other page on the web. We will download the page, parse the content, and add it to this
                        bot. Also supports PDF and media files.
                    </p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm mt-4 inline-block">
                        Learn how to connect to this source
                    </a>
                </div>

                <!-- Right Column - Form -->
                <div class="space-y-8">
                    <!-- Source URL -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <label class="text-base text-gray-700">Source URL</label>
                            <span class="text-base text-gray-500">Required</span>
                        </div>
                        <div class="mt-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <input type="url" v-model="formData.url" @input="validateUrl"
                                    class="pl-10 block w-full border border-gray-200 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-base py-2"
                                    placeholder="https://example.com/page/">
                            </div>
                            <p class="mt-2 text-gray-500">Clickable URL of source link displayed with answers.</p>
                        </div>
                    </div>

                    <!-- Source Title -->
                    <div>
                        <div class="flex justify-between mb-1">
                            <label class="text-base text-gray-700">Source title</label>
                            <span class="text-base text-gray-500">Optional</span>
                        </div>
                        <div class="mt-1">
                            <input type="text" v-model="formData.title"
                                class="block w-full border border-gray-200 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-base py-2 px-3"
                                placeholder="Enter source title">
                            <p class="mt-2 text-gray-500">Title of source displayed alongside answers. Defaults to page
                                title or file name.</p>
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
                                        <span
                                            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
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
                                                    <span
                                                        :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
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
                            <p class="mt-2 text-gray-500">This will automatically refresh the source at the selected
                                interval.</p>
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
            <button :disabled="!isValidUrl" @click="submitForm" :class="[
                'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                isValidUrl
                    ? 'bg-teal-600 text-white hover:bg-teal-800 focus:ring-teal-400'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            ]">
                Add source
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

const loading = ref(false);
const isValidUrl = ref(false);
const formData = ref({
    type: 'URL',
    title: '',
    url: '',
    refresh_schedule: 'never'
});

const refreshOptions = ['Never', 'Monthly', 'Weekly', 'Daily']

const validateUrl = () => {
    try {
        const url = new URL(formData.value.url)
        isValidUrl.value = url.protocol === 'http:' || url.protocol === 'https:'
    } catch {
        isValidUrl.value = false
    }
}

const closeForm = () => {
    emit('close');
}

const submitForm = async () => {
    loading.value = true;
    try {
        // Create source data with optional title
        const sourceData = {
            type: formData.value.type,
            title: formData.value.title || '', // Send empty string if no title provided
            url: formData.value.url,
            refresh_schedule: formData.value.refresh_schedule.toLowerCase()
        };

        await sourcesStore.addSource(botsStore.currentBot.id, sourceData);
        botsStore.incrementSourcesCount();
        emit('source-added');
    } catch (error) {
        console.error('Error adding source:', error);
    } finally {
        loading.value = false;
    }
};
</script>
