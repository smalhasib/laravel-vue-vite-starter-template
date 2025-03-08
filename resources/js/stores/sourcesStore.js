import { defineStore } from "pinia";
import axios from "axios";
import { useToast } from "vue-toastification";

export const useSourcesStore = defineStore("sources", {
    state: () => ({
        loading: false,
        error: null,
        pollingIntervals: new Map(), // Store polling intervals by source ID
    }),

    actions: {
        async addSource(botId, sourceData) {
            const toast = useToast();
            this.loading = true;
            try {
                const formData = new FormData();

                // Format boolean value explicitly
                const formattedData = {
                    ...sourceData,
                    process_images: sourceData.process_images ? "1" : "0",
                };

                Object.keys(formattedData).forEach((key) => {
                    formData.append(key, formattedData[key]);
                });

                const response = await axios.post(
                    `/api/bots/${botId}/sources`,
                    formData
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
            this.loading = true;
            try {
                await axios.delete(`/api/bots/${botId}/sources/${sourceId}`);
                // Stop polling for this source
                this.stopPolling(sourceId);
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
            // If already polling, clear the interval
            this.stopPolling(sourceId);

            // Create new polling interval
            const intervalId = setInterval(async () => {
                try {
                    const response = await axios.get(
                        `/api/bots/${botId}/sources/${sourceId}/status`
                    );
                    const source = response.data;

                    // If source is in a final state, stop polling
                    if (
                        source.status === "indexed" ||
                        source.status === "failed"
                    ) {
                        this.stopPolling(sourceId);

                        // If indexed, calculate total chunks
                        if (source.status === "indexed") {
                            const totalChunks = source.documents.reduce(
                                (sum, doc) =>
                                    sum + (doc.indexed_chunks_count || 0),
                                0
                            );

                            // Emit event with chunks count
                            document.dispatchEvent(
                                new CustomEvent("source-indexed", {
                                    detail: {
                                        sourceId,
                                        botId,
                                        totalChunks,
                                    },
                                })
                            );
                        }
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
    },
});
