import axios from "axios";

const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    withCredentials: true, // Important for CSRF token
});

// Add request interceptor to include CSRF token
api.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default {
    async getAllBots() {
        const response = await api.get("/bots");
        return response.data;
    },

    async createBot(botData) {
        const response = await api.post("/bots", botData);
        return response.data;
    },

    async updateBot(id, botData) {
        const response = await api.put(`/bots/${id}`, botData);
        return response.data;
    },

    async deleteBot(id) {
        await api.delete(`/bots/${id}`);
    },
};
