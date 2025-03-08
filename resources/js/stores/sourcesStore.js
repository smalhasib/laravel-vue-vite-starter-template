import { defineStore } from "pinia";
import axios from "axios";
import { useToast } from "vue-toastification";
import { useBotsStore } from "./botsStore";

export const useSourcesStore = defineStore("sources", {
    state: () => ({
        sources: [],
        loading: false,
        error: null,
        pollingIntervals: new Map(), // Store polling intervals by source ID
    }),

    actions: {
        async addSource(botId, sourceData) {
            const toast = useToast();
            const botsStore = useBotsStore();
            this.loading = true;
            this.error = null;

            try {
                const config = {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                };

                const response = await axios.post(
                    `/api/bots/${botId}/sources`,
                    sourceData,
                    config
                );
                // Start polling for this source
                this.startPolling(botId, response.data.data.id);

                toast.success("Source added successfully");
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to add source";
                toast.error(this.error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteSource(botId, sourceId) {
            const toast = useToast();
            const botsStore = useBotsStore();
            this.loading = true;
            try {
                await axios.delete(`/api/bots/${botId}/sources/${sourceId}`);
                // Stop polling for this source
                this.stopPolling(sourceId);
                // Refresh bot data to get updated counts
                await botsStore.refreshBotData(botId);
                toast.success("Source deleted successfully");
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to delete source";
                toast.error(this.error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Start polling for a source's status
        startPolling(botId, sourceId) {
            const botsStore = useBotsStore();
            // If already polling, clear the interval
            this.stopPolling(sourceId);

            // Create new polling interval
            const intervalId = setInterval(async () => {
                try {
                    const response = await axios.get(
                        `/api/bots/${botId}/sources/${sourceId}/status`
                    );
                    const source = response.data;

                    // If source is in a final state, stop polling and refresh bot data
                    if (
                        source.status === "indexed" ||
                        source.status === "failed"
                    ) {
                        this.stopPolling(sourceId);
                        await botsStore.refreshBotData(botId);
                    }

                    // Emit status update event
                    document.dispatchEvent(
                        new CustomEvent("source-status-updated", {
                            detail: source,
                        })
                    );
                } catch (error) {
                    console.error("Failed to fetch source status:", error);
                    // Stop polling on error
                    this.stopPolling(sourceId);
                }
            }, 5000); // Poll every 5 seconds

            // Store the interval ID
            this.pollingIntervals.set(sourceId, intervalId);
        },

        // Stop polling for a source
        stopPolling(sourceId) {
            const intervalId = this.pollingIntervals.get(sourceId);
            if (intervalId) {
                clearInterval(intervalId);
                this.pollingIntervals.delete(sourceId);
            }
        },

        // Stop all polling intervals
        stopAllPolling() {
            this.pollingIntervals.forEach((intervalId) => {
                clearInterval(intervalId);
            });
            this.pollingIntervals.clear();
        },

        async getSources(botId) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/bots/${botId}/sources`);
                this.sources = response.data;
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch sources";
                throw error;
            } finally {
                this.loading = false;
            }
        },
    },
});
