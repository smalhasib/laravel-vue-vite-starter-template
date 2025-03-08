<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexingService
{
    /**
     * Index the scraped data using the remote database endpoint
     *
     * @param int $botId
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function indexData(int $botId, array $data): array
    {
        try {
            Log::info("Starting to index data for bot", ['bot_id' => $botId]);

            // Prepare the data for the remote endpoint
            $requestData = [
                'userId' => $data['user_id'],
                'botId' => $botId,
                'sourceId' => $data['source_id'],
                'documentId' => $data['document_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'url' => $data['url']
            ];

            // Send to remote database
            $response = Http::post(
                'https://fluent-ai-backend.support-ai.workers.dev/fluent-bot/import',
                $requestData
            );

            if (!$response->successful()) {
                throw new \Exception("Failed to index data: HTTP status " . $response->status());
            }

            $responseData = $response->json();

            Log::info("Data indexed successfully", [
                'bot_id' => $botId,
                'source_id' => $data['source_id'],
                'document_id' => $data['document_id'],
                'chunks' => $responseData['chunks'] ?? 0
            ]);

            return [
                'status' => 'success',
                'chunks' => $responseData['chunks'] ?? 0,
                'metadata' => [
                    'indexed_at' => now()->toIso8601String(),
                    'source_url' => $data['url']
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error indexing data", [
                'bot_id' => $botId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Delete source data from the remote backend
     *
     * @param int $userId User ID
     * @param int $botId Bot ID
     * @param int $sourceId Source ID
     * @param array $documents Array of document IDs to delete
     * @return array Response containing deletion details
     * @throws \Exception If deletion fails
     */
    public function deleteSource(
        int $userId,
        int $botId,
        int $sourceId,
        array $documents
    ): array {
        $response = Http::delete('https://fluent-ai-backend.support-ai.workers.dev/fluent-bot/source', [
            'userId' => $userId,
            'botId' => $botId,
            'sourceId' => $sourceId,
            'documents' => $documents
        ]);

        if ($response->failed()) {
            Log::error('Source deletion failed', [
                'sourceId' => $sourceId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Failed to delete source: " . $response->status());
        }

        return $response->json();
    }

    /**
     * Delete a single document from the remote backend
     *
     * @param int $userId User ID
     * @param int $botId Bot ID
     * @param int $sourceId Source ID
     * @param int $documentId Document ID
     * @param int $chunks Number of chunks to delete
     * @return array Response containing deletion details
     * @throws \Exception If deletion fails
     */
    public function deleteDocument(
        int $userId,
        int $botId,
        int $sourceId,
        int $documentId,
        int $chunks
    ): array {
        $response = Http::delete('https://fluent-ai-backend.support-ai.workers.dev/fluent-bot/source-document', [
            'userId' => $userId,
            'botId' => $botId,
            'sourceId' => $sourceId,
            'documentId' => $documentId,
            'chunks' => $chunks
        ]);

        if ($response->failed()) {
            Log::error('Document deletion failed', [
                'documentId' => $documentId,
                'sourceId' => $sourceId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Failed to delete document: " . $response->status());
        }

        return $response->json();
    }

    /**
     * Delete all sources data from the remote backend for a bot
     *
     * @param string $userId User ID
     * @param string $botId Bot ID
     * @param array $sources Array of sources with sourceId and document IDs
     * @return array Response containing deletion details
     * @throws \Exception If deletion fails
     */
    public function deleteAllSources(
        string $userId,
        string $botId,
        array $sources
    ): array {
        $response = Http::delete('https://fluent-ai-backend.support-ai.workers.dev/fluent-bot/all-sources', [
            'userId' => $userId,
            'botId' => $botId,
            'sources' => array_map(function ($source) {
                return [
                    'sourceId' => $source['sourceId'],
                    'documents' => $source['documents'] ?? []
                ];
            }, $sources)
        ]);

        if ($response->failed()) {
            Log::error('Bulk source deletion failed', [
                'botId' => $botId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Failed to delete all sources: " . $response->status());
        }

        return $response->json();
    }
}
