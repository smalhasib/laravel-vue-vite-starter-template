<template>
    <router-view></router-view>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();

onMounted(() => {
    authStore.initializeAuth();
    if (authStore.isAuthenticated) {
        authStore.getUser();
    }
});
</script>
