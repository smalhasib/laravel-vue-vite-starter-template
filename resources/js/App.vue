<template>
    <router-view></router-view>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useAuthStore } from './stores/auth';
import { useBotsStore } from '@/stores/botsStore';
import { useSourcesStore } from '@/stores/sourcesStore';

const authStore = useAuthStore();
const botsStore = useBotsStore();
const sourcesStore = useSourcesStore();

onMounted(() => {
    authStore.initializeAuth();
    if (authStore.isAuthenticated) {
        authStore.getUser();
    }
    // Clean up any existing polling intervals when component unmounts
    onUnmounted(() => {
        sourcesStore.stopAllPolling();
    });
});
</script>
