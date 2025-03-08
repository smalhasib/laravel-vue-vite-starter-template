import { defineStore } from "pinia";
import botService from "@/services/botService";
import { useToast } from "vue-toastification";
import axios from "axios";

const toast = useToast();

export const useBotsStore = defineStore("bots", {
    state: () => ({
        bots: [],
        currentBot: null,
        loading: false,
        error: null,
    }),

    getters: {
        hasBots: (state) => state.bots.length > 0,
    },

    actions: {
        initializeEventListeners() {
            document.addEventListener("source-indexed", (event) => {
                const { botId, totalChunks } = event.detail;
                this.updateBotChunksCount(botId, totalChunks);
            });
        },

        updateBotChunksCount(botId, newChunks) {
            const botIndex = this.bots.findIndex((bot) => bot.id === botId);
            if (botIndex !== -1) {
                this.bots[botIndex].total_indexed_chunks_count += newChunks;
            }

            if (this.currentBot && this.currentBot.id === botId) {
                this.currentBot.total_indexed_chunks_count += newChunks;
            }
        },

        async fetchBots() {
            try {
                const response = await axios.get("/api/bots");
                this.bots = response.data;
            } catch (error) {
                throw error;
            }
        },

        async fetchBot(id) {
            try {
                const response = await axios.get(`/api/bots/${id}`);
                console.log("Bot API Response:", response.data);
                this.currentBot = response.data;
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch bot";
                toast.error(this.error);
                throw error;
            }
        },

        async addBot(botData) {
            try {
                const newBot = await botService.createBot(botData);
                this.bots.push(newBot);
                toast.success("Bot created successfully");
            } catch (error) {
                toast.error("Failed to create bot");
                throw error;
            }
        },

        async updateBot(id, botData) {
            try {
                const updatedBot = await botService.updateBot(id, botData);
                const index = this.bots.findIndex((bot) => bot.id === id);
                if (index !== -1) {
                    this.bots[index] = updatedBot;
                }
                toast.success("Bot updated successfully");
            } catch (error) {
                toast.error("Failed to update bot");
                throw error;
            }
        },

        async deleteBot(id) {
            try {
                await botService.deleteBot(id);
                this.bots = this.bots.filter((bot) => bot.id !== id);
                toast.success("Bot deleted successfully");
            } catch (error) {
                toast.error("Failed to delete bot");
                throw error;
            }
        },

        incrementSourcesCount() {
            if (this.currentBot) {
                this.currentBot.sources_count++;
                const botIndex = this.bots.findIndex(
                    (b) => b.id === this.currentBot.id
                );
                if (botIndex !== -1) {
                    this.bots[botIndex].sources_count++;
                }
            }
        },

        clearCurrentBot() {
            this.currentBot = null;
        },
    },
});
