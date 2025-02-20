<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                <div v-if="index > 0" class="flex items-center">
                    <span class="mx-2 text-gray-400">&gt;</span>
                </div>
                <div v-if="crumb.to && !crumb.current" class="font-semibold text-gray-900">
                    <router-link :to="crumb.to">{{ crumb.name }}</router-link>
                </div>
                <div v-else class="font-semibold text-gray-900"
                    :class="{ 'font-normal text-gray-600': !crumb.current }">
                    {{ crumb.name }}
                </div>
            </li>
        </ol>
    </nav>
</template>

<script setup>
defineProps({
    breadcrumbs: {
        type: Array,
        required: true,
        default: () => []
    }
})
</script>
