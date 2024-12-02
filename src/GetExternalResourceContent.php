<?php

namespace Root\AnchorElementCrawler;

use Illuminate\Support\Facades\Log;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetExternalResourceContent
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
        
    }

    public function fetchExternalLink(string $url): string
    {
        $response = $this->client->request(
            'GET',
            $url,
            [
                'max_redirects' => 10,
                'headers' => [
                    'Content-Type' => 'text/html',
                ],
                'timeout' => 10,
            ]
        );

        Log::channel('stdout')->debug("Requesting: {$url}");

        $statusCode = $response->getStatusCode();
        Log::channel('stdout')->debug("statusCode: {$statusCode}");

        $contentType = $response->getHeaders()['content-type'][0];
        Log::channel('stdout')->debug("contentType: {$contentType}");

        return $response->getContent();
    }
}