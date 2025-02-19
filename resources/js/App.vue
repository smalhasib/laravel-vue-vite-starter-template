<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Welcome to Your Laravel + Vue 3 App</h1>
            <p class="text-lg text-gray-600">{{ message }}</p>
            <button @click="ping" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700 mt-4">
                Ping Server
            </button>
            <p v-if="pingResponse" class="mt-4 text-green-600">{{ pingResponse }}</p>
            <p v-if="error" class="mt-4 text-red-600">{{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const message = ref('Hello, Vue 3 with Composition API!');
const pingResponse = ref('');
const error = ref('');

async function ping() {
    try {
        const response = await axios.get('/api/ping');
        pingResponse.value = response.data.message;
        error.value = '';
    } catch (e) {
        error.value = 'Failed to ping server';
        pingResponse.value = '';
    }
}
</script>
