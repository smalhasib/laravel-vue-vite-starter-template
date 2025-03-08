<template>
  <div class="source-status">
    <div class="status-indicator" :class="statusClass">
      {{ source.status }}
    </div>
    <div v-if="source.last_refresh_at" class="text-sm text-gray-600">
      Last refreshed: {{ formatDate(source.last_refresh_at) }}
    </div>
    <div v-if="source.next_refresh_at" class="text-sm text-gray-600">
      Next refresh: {{ formatDate(source.next_refresh_at) }}
    </div>
    <button
      @click="refreshSource"
      :disabled="source.status === 'Indexing' || source.status === 'Queued'"
      class="refresh-button"
      :class="{ 'opacity-50 cursor-not-allowed': source.status === 'Indexing' || source.status === 'Queued' }"
    >
      Refresh Now
    </button>
  </div>
</template>

<script>
export default {
  props: {
    initialSource: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      source: { ...this.initialSource }
    }
  },

  computed: {
    statusClass() {
      return {
        'bg-yellow-500': this.source.status === 'Queued',
        'bg-blue-500 animate-pulse': this.source.status === 'Indexing',
        'bg-green-500': this.source.status === 'Indexed',
        'bg-red-500': this.source.status === 'Failed'
      }
    }
  },

  mounted() {
    this.listenForUpdates();
  },

  methods: {
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },

    listenForUpdates() {
      Echo.private(`user.${window.userId}`)
          .listen('.source.status.updated', (data) => {
            if (data.id === this.source.id) {
              this.source = { ...this.source, ...data };
            }
          });
    },

    async refreshSource() {
      try {
        const response = await axios.post(`/api/sources/${this.source.id}/refresh`);
        this.source = { ...this.source, ...response.data.source };
      } catch (error) {
        console.error('Error refreshing source:', error);
      }
    }
  }
}
</script>

<style scoped>
.source-status {
  padding: 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.status-indicator {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  color: white;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.refresh-button {
  margin-top: 0.5rem;
  padding: 0.25rem 0.75rem;
  background-color: #4f46e5;
  color: white;
  border-radius: 0.25rem;
  font-weight: 500;
}
</style>
