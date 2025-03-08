<!-- Document Modal -->
<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="$emit('close')" class="relative z-[60]">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/75" aria-hidden="true" />
            </TransitionChild>

            <div class="fixed inset-0 flex items-center justify-center p-4" @click.self="$emit('close')">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel class="w-full max-w-4xl rounded-xl bg-white p-6 transform transition-all overflow-y-auto max-h-[90vh]" @click.stop>
                        <!-- Close button -->
                        <button @click="$emit('close')"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Header with title and source URL -->
                        <div class="mb-4">
                            <DialogTitle as="h3" class="text-xl font-semibold text-gray-900">
                                {{ document.title }}
                            </DialogTitle>
                            <a :href="document.source" target="_blank" rel="noopener noreferrer" 
                               class="mt-1 text-sm text-blue-500 hover:text-blue-700 inline-block">
                                {{ document.source }}
                            </a>
                        </div>

                        <!-- Document content in markdown -->
                        <div class="prose max-w-none dark:prose-invert" v-dompurify-html="renderedContent">
                        </div>

                        <!-- Footer with metadata -->
                        <div class="mt-6 flex justify-between items-center text-sm text-gray-500">
                            <span>{{ document.indexed_chunks_count }} chunks indexed</span>
                            <span>Last updated: {{ formatDate(document.updated_at) }}</span>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { marked } from 'marked';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    document: {
        type: Object,
        required: true
    }
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    try {
        return new Date(date).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return 'Invalid date';
    }
};

const renderedContent = computed(() => {
    return marked(props.document.content || '');
});

defineEmits(['close']);
</script>

<style>
.prose {
    font-size: 1rem;
    line-height: 1.75;
}

.prose :where(h1):not(:where([class~="not-prose"] *)) {
    font-size: 2.25em;
    margin-top: 0;
    margin-bottom: 0.8888889em;
    line-height: 1.1111111;
}

.prose :where(h2):not(:where([class~="not-prose"] *)) {
    font-size: 1.5em;
    margin-top: 2em;
    margin-bottom: 1em;
    line-height: 1.3333333;
}

.prose :where(h3):not(:where([class~="not-prose"] *)) {
    font-size: 1.25em;
    margin-top: 1.6em;
    margin-bottom: 0.6em;
    line-height: 1.6;
}

.prose :where(p):not(:where([class~="not-prose"] *)) {
    margin-top: 1.25em;
    margin-bottom: 1.25em;
}

.prose :where(a):not(:where([class~="not-prose"] *)) {
    color: #2563eb;
    text-decoration: underline;
    font-weight: 500;
}

.prose :where(strong):not(:where([class~="not-prose"] *)) {
    font-weight: 600;
}

.prose :where(ul):not(:where([class~="not-prose"] *)) {
    margin-top: 1.25em;
    margin-bottom: 1.25em;
    padding-left: 1.625em;
    list-style-type: disc;
}

.prose :where(ol):not(:where([class~="not-prose"] *)) {
    margin-top: 1.25em;
    margin-bottom: 1.25em;
    padding-left: 1.625em;
    list-style-type: decimal;
}

.prose :where(code):not(:where([class~="not-prose"] *)) {
    color: #1a1a1a;
    background-color: #f5f5f5;
    padding: 0.2em 0.4em;
    border-radius: 3px;
    font-size: 0.875em;
}

.prose :where(pre):not(:where([class~="not-prose"] *)) {
    color: #1a1a1a;
    background-color: #f5f5f5;
    overflow-x: auto;
    font-size: 0.875em;
    line-height: 1.7142857;
    margin-top: 1.7142857em;
    margin-bottom: 1.7142857em;
    border-radius: 0.375rem;
    padding: 0.8571429em 1.1428571em;
}
</style>
