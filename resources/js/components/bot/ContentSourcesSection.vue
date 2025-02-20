<template>
    <!-- URL Source Form -->
    <UrlSourceForm v-if="selectedSource === 'URL'" @cancel="selectedSource = null" />

    <!-- Source Types Description -->
    <p v-else class="mt-6 text-base text-gray-700">
        Add any content sources you want your bot to be able to answer questions about. You can always add more
        later on.
    </p>

    <div v-else class="bg-white rounded-lg shadow-lg">
        <!-- Source Types Section -->
        <div class="p-6">
            <h2 class="text-base font-medium text-gray-900 mb-4">Source types</h2>

            <!-- Expandable Source Type Sections -->
            <div class="space-y-2">
                <div v-for="type in sourceTypes" :key="type.name">
                    <div class="bg-gray-100 rounded-lg" :class="{ 'rounded-b-none': expandedSections[type.name] }">
                        <div @click="toggleSection(type.name)"
                            class="flex items-center justify-between p-4 hover:bg-gray-200 rounded-lg cursor-pointer"
                            :class="{ 'rounded-b-none': expandedSections[type.name] }">
                            <div class="flex items-center">
                                <component :is="type.icon" class="w-6 h-6 text-gray-400" />
                                <span class="ml-3 text-gray-900">{{ type.name }}</span>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-200"
                                :class="{ 'rotate-180': expandedSections[type.name] }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Expanded Content with transition -->
                    <transition enter-active-class="transition-all duration-300 ease-in-out"
                        enter-from-class="transform opacity-0 max-h-0"
                        enter-to-class="transform opacity-100 max-h-[1000px]"
                        leave-active-class="transition-all duration-200 ease-in-out"
                        leave-from-class="transform opacity-100 max-h-[1000px]"
                        leave-to-class="transform opacity-0 max-h-0">
                        <div v-if="expandedSections[type.name]" class="overflow-hidden">
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="source in type.sources" :key="source.name"
                                        @click="selectSource(source.name)"
                                        class="bg-white rounded-lg p-4 hover:shadow-md transition-shadow border border-gray-200 cursor-pointer">
                                        <div class="flex items-start">
                                            <component :is="source.icon" class="w-6 h-6 text-gray-400" />
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-gray-900">{{ source.name }}</h3>
                                                <p class="text-sm text-gray-500">{{ source.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-4 rounded-b-lg border-t border-gray-100">
            <button @click="$emit('cancel')"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Cancel
            </button>
            <button
                class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-md hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-400">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Add source
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import UrlSourceForm from './sources/UrlSourceForm.vue'

defineEmits(['cancel'])

const expandedSections = ref({})
const selectedSource = ref(null)

const toggleSection = (sectionName) => {
    expandedSections.value[sectionName] = !expandedSections.value[sectionName]
}

const selectSource = (sourceName) => {
    selectedSource.value = sourceName
}

const sourceTypes = [
    {
        name: 'Web',
        icon: 'WebIcon',
        sources: [
            {
                name: 'URL',
                icon: 'LinkIcon',
                description: 'Answer from the content from a single public webpage'
            },
            {
                name: 'URL List',
                icon: 'ListIcon',
                description: 'Index all content from a bulk list of URLs'
            },
            {
                name: 'WordPress',
                icon: 'WordPressIcon',
                description: 'Upload a WordPress XML export file of your content'
            }
        ]
    }
]
</script>
