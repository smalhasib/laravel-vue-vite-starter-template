<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-6">Account Settings</h2>
        <div class="max-w-xl">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</template>
