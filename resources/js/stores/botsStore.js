import { defineStore } from "pinia";
import botService from "@/services/botService";
import { useToast } from "vue-toastification";

const toast = useToast();

export const useBotsStore = defineStore("bots", {
    state: () => ({
        bots: [],
        loading: false,
        error: null,
    }),

    getters: {
        hasBots: (state) => state.bots.length > 0,
    },

    actions: {
        async fetchBots() {
            try {
                this.loading = true;
                this.bots = await botService.getAllBots();
            } catch (error) {
                toast.error("Failed to fetch bots");
                this.error = error.message;
            } finally {
                this.loading = false;
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
    },
});
