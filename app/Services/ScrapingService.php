<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapingService
{
    /**
     * Scrape content from a URL using the local scraping service
     *
     * @param string $url
     * @return array
     * @throws \Exception
     */
    public function scrapeUrl(string $url): array
    {
        Log::info("Sending request to scraping service", ['url' => $url]);

        // Call the local scraping endpoint
        $response = Http::post('http://localhost:4000/scrape/url', [
            'url' => $url
        ]);

        if (!$response->successful()) {
            throw new \Exception("Failed to scrape URL: HTTP status " . $response->status());
        }

        $data = $response->json();

        if (empty($data['content'])) {
            throw new \Exception("No content received from scraping service");
        }

        return [
            'url' => $data['url'],
            'title' => $data['title'],
            'content' => $data['content']
        ];
    }
}
