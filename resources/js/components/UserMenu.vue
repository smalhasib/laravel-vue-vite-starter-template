<template>
    <div ref="menuRef" class="relative">
        <button @click="toggleMenu"
            class="flex items-center gap-2 text-gray-700 hover:text-gray-900 rounded-full hover:bg-gray-100 py-1 px-2 transition-colors duration-200">
            <!-- Avatar -->
            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium shadow-sm">
                {{ getUsernameFromEmail(auth.user?.email)?.[0]?.toUpperCase() }}
            </div>

            <!-- Username (hidden on mobile) -->
            <span class="hidden md:block font-medium">
                {{ getUsernameFromEmail(auth.user?.email) }}
            </span>

            <!-- Dropdown arrow -->
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isMenuOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown menu -->
        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <div v-if="isMenuOpen"
                class="absolute right-0 mt-1 w-48 rounded-md shadow-lg bg-white divide-y divide-gray-100 z-50">
                <!-- User info section -->
                <div class="px-4 py-3">
                    <p class="text-sm text-gray-500">Signed in as</p>
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ auth.user?.email }}
                    </p>
                </div>

                <!-- Menu items -->
                <div class="py-1">
                    <button @click="handleLogout"
                        class="group flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter, useRoute } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const isMenuOpen = ref(false)
const menuRef = ref<HTMLElement | null>(null)

// Computed property to check if we're on the Fluent Docs page
const isFluentDocsPage = computed(() => route.path === '/fluent-docs')

function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value
}

async function handleLogout() {
    await auth.logout()
    router.push('/login')
}

function getUsernameFromEmail(email?: string): string {
    if (!email) return ''
    return email.split('@')[0]
}

// Close menu when clicking outside
function handleClickOutside(event: MouseEvent) {
    if (menuRef.value && !menuRef.value.contains(event.target as Node)) {
        isMenuOpen.value = false
    }
}

// Close menu when pressing escape
function handleEscape(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        isMenuOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleEscape)
})
</script>

<style scoped>
.relative {
    position: relative;
    z-index: 40;
}

.absolute {
    z-index: 45;
}
</style>
