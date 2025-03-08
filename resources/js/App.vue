<template>
    <router-view></router-view>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import { useBotsStore } from '@/stores/botsStore';

const authStore = useAuthStore();
const botsStore = useBotsStore();

onMounted(() => {
    authStore.initializeAuth();
    if (authStore.isAuthenticated) {
        authStore.getUser();
    }
    botsStore.initializeEventListeners();
});
</script>
