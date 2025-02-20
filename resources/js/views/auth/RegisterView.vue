<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-8">
        <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
            <h2 class="mb-6 text-center text-2xl font-bold text-gray-800">Create your account</h2>
            <form @submit.prevent="handleSubmit">
                <div class="mb-4">
                    <label class="block text-gray-700">Email address</label>
                    <input v-model="form.email" type="email" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="Email address" />
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Password</label>
                    <input v-model="form.password" type="password" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="Password" />
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="Confirm password" />
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">
                    Register
                </button>
                <div class="mt-4 text-center">
                    <RouterLink to="/login" class="text-blue-600 hover:underline">
                        Already have an account? Sign in
                    </RouterLink>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = reactive({
    email: '',
    password: '',
    password_confirmation: ''
})

const handleSubmit = async () => {
    try {
        await auth.register(form)
        router.push('/')
    } catch (error) {
        console.error('Registration failed:', error)
    }
}
</script>
