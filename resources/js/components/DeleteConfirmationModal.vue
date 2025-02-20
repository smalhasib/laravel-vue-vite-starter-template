<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="close" class="relative z-50">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95">
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 text-left align-middle shadow-xl transition-all">
                            <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                Delete bot {{ botName }}?
                            </DialogTitle>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">
                                    Once you delete this bot, all indexed sources will be deleted and any embedded chats for
                                    this bot will no longer work.
                                </p>
                            </div>

                            <div class="mt-6 flex justify-start space-x-3">
                                <button @click="close"
                                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                                    Cancel
                                </button>
                                <button @click="confirm"
                                    class="inline-flex justify-center rounded-md border border-transparent bg-red-100 px-4 py-2 text-sm font-medium text-red-900 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    Delete
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'

defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    botName: {
        type: String,
        required: true
    }
})

const emit = defineEmits(['close', 'confirm'])

const close = () => {
    emit('close')
}

const confirm = () => {
    emit('confirm')
}
</script>
