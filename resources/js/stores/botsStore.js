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
        async refreshBotData(botId) {
            try {
                const response = await axios.get(`/api/bots/${botId}`);
                const updatedBot = response.data;

                // Update bot in the bots array
                const botIndex = this.bots.findIndex((bot) => bot.id === botId);
                if (botIndex !== -1) {
                    this.bots[botIndex] = updatedBot;
                }

                // Update currentBot if it's the same bot
                if (this.currentBot && this.currentBot.id === botId) {
                    this.currentBot = updatedBot;
                }

                return updatedBot;
            } catch (error) {
                console.error("Failed to refresh bot data:", error);
                throw error;
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
