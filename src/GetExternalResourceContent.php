<?php

namespace Root\AnchorElementCrawler;

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

        echo "Requesting: {$url}" . PHP_EOL;

        $statusCode = $response->getStatusCode();
        echo "statusCode: {$statusCode}" . PHP_EOL;

        $contentType = $response->getHeaders()['content-type'][0];
        echo "contentType: {$contentType}" . PHP_EOL;

        return $response->getContent();
    }
}