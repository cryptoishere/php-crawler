<?php

namespace Root\AnchorElementCrawler;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Promise;

class GetExternalResourceContent
{
    private $promises = [];
    private $responses = [];
    private $options = [];
    private $urls = [];

    public function __construct(
        private Client $client,
    ) {
        
    }

    /**
     * Wait for all the requests to complete; throws a ConnectException if any of the requests fail
     */
    public function wait(): void
    {
        $this->wrap();

        if (empty($this->promises)) {
            return;
        }

        try {
            CrawlerSupport::debugLog("Wait");

            foreach (Promise\Utils::settle($this->promises)->wait() as $url => $result) {
                $this->responses[$url] = $result;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function responses(): array
    {
        return $this->responses;
    }

    public function setUrls(array $urls)
    {
        $this->urls = $urls;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    private function getAsync(string|UriInterface $url, array $options = []): PromiseInterface
    {
        return $this->client->getAsync($url, $options);
    }

    private function pushToCollection(string $url, PromiseInterface $request): void
    {
        $this->promises[$url] = $request;
    }

    private function wrap()
    {
        foreach ($this->urls as $url) {
            CrawlerSupport::debugLog("Requesting: {$url}");
            // TODO: Encode key
            $this->pushToCollection($url, $this->getAsync($url, $this->options));
        }
    }
}
