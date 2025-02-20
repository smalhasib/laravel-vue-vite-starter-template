import { defineStore } from "pinia";
import axios from "axios";
import { useToast } from "vue-toastification";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        errors: {},
    }),

    getters: {
        isAuthenticated: (state) =>
            !!state.user || !!localStorage.getItem("token"),
    },

    actions: {
        async login(credentials) {
            const toast = useToast();
            try {
                await axios.get("/sanctum/csrf-cookie");
                const response = await axios.post("/api/login", credentials);

                // Set state and storage in order
                localStorage.setItem("token", response.data.token);
                localStorage.setItem(
                    "user",
                    JSON.stringify(response.data.user)
                );
                this.user = response.data.user;
                this.errors = {};

                toast.success("Login successful!");
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                    Object.values(error.response.data.errors).forEach(
                        (errors) => {
                            errors.forEach((error) => toast.error(error));
                        }
                    );
                } else if (error.response?.data?.message) {
                    toast.error(error.response.data.message);
                } else {
                    toast.error("An error occurred during login");
                }
                console.error("Login error details:", error); // Add detailed logging
                return false; // Add return value for failure
            }
        },

        async register(credentials) {
            const toast = useToast();
            try {
                await axios.get("/sanctum/csrf-cookie");
                const response = await axios.post("/api/register", credentials);
                this.user = response.data.user;
                localStorage.setItem("token", response.data.token);
                localStorage.setItem(
                    "user",
                    JSON.stringify(response.data.user)
                );
                this.errors = {};
                toast.success("Registration successful!");
                return response;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                    Object.values(error.response.data.errors).forEach(
                        (errors) => {
                            errors.forEach((error) => toast.error(error));
                        }
                    );
                } else {
                    toast.error("An error occurred during registration");
                }
                throw error;
            }
        },

        async logout() {
            const toast = useToast();
            try {
                await axios.post("/api/logout");
                toast.success("Logged out successfully");
            } catch (error) {
                console.error("Logout failed:", error);
                toast.error("Logout failed");
            } finally {
                this.user = null;
                localStorage.removeItem("token");
                localStorage.removeItem("user");
            }
        },

        initializeAuth() {
            const user = localStorage.getItem("user");
            const token = localStorage.getItem("token");
            if (user && token) {
                this.user = JSON.parse(user);
                return true;
            }
            return false;
        },

        async getUser() {
            if (!this.isAuthenticated) return;

            try {
                const response = await axios.get("/api/user");
                this.user = response.data;
                localStorage.setItem("user", JSON.stringify(response.data));
            } catch (error) {
                if (error.response?.status === 401) {
                    this.logout();
                }
            }
        },

        async checkAuth() {
            if (!this.isAuthenticated) return false;

            const toast = useToast();
            try {
                const response = await axios.get("/api/user");
                this.user = response.data;
                localStorage.setItem("user", JSON.stringify(response.data));
                return true;
            } catch (error) {
                if (error.response?.status === 401) {
                    this.logout();
                    toast.error("Session expired. Please login again.");
                }
                return false;
            }
        },
    },
});
