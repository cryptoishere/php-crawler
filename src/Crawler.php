<?php

namespace Root\AnchorElementCrawler;

use Symfony\Component\DomCrawler\Crawler as ExternalCrawler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use stdClass;

class Crawler
{
    public function __construct(
        private Client $client,
        private Collection $buffer,
    ) {
    }

    public function parse(string $url, string $targetUrl, string $linkText): Collection
    {
        $resources = new GetExternalResourceContent($this->client);

        $resources->setUrls([$url]);
        $resources->wait();

        $this->handle($resources, $targetUrl, $linkText);

        return $this->buffer;
    }

    private function handle(GetExternalResourceContent $resources, string $targetUrl, string $linkText): void
    {
        $parseResult = new stdClass();

        foreach ($resources->responses() as $response) {
            if ($response['state'] === 'fulfilled') {
                // Process successful response
                if (!$this->isOk($response['value'])) {
                    $parseResult->failed = true;
                    continue;
                }

                try {
                    $content = $response['value']->getBody()->getContents();
                } catch (\Throwable $th) {
                    $parseResult->failed = true;
                    continue;
                }

                $crawler = new ExternalCrawler($content);
                $resourceLinks = $crawler->filterXPath('descendant-or-self::body//a');

                $tagNode = null;

                foreach ($resourceLinks as $anchor) {
                    $anchorCrawler = new ExternalCrawler($anchor);

                    if ($anchorCrawler->attr('href') !== null) {
                        $hrefValue = $anchorCrawler->attr('href');
                        // CrawlerSupport::debugLog("The 'href' attribute exists and its value is: " . $hrefValue);
                        if ($hrefValue === $targetUrl) {
                            CrawlerSupport::debugLog("Link match");
                            $parseResult->targetLinkMatched = true;
                            $tagNode = $anchor;
                            break;
                        } else {
                            CrawlerSupport::debugLog("Link mo match");
                        }
                    }
                }

                if ($tagNode) {
                    $matchedTag = new ExternalCrawler($tagNode);

                    if ($matchedTag->attr('rel') !== null) {
                        $relValue = $matchedTag->attr('rel');
                        CrawlerSupport::debugLog("The 'rel' attribute exists and its value is: " . $relValue);
                        $parseResult->isDoFollow = false;
                    } else {
                        CrawlerSupport::debugLog("The 'rel' attribute does not exist on this anchor tag");
                        $parseResult->isDoFollow = true;
                    }
        
                    CrawlerSupport::debugLog("Context: {$tagNode->textContent}");
                    if ($tagNode->textContent === $linkText) {
                        CrawlerSupport::debugLog("Link text match");
                        $parseResult->textMatched = true;
                    } else {
                        CrawlerSupport::debugLog("Link text no match");
                        $parseResult->textMatched = false;
                    }
                }
            } else {
                $reason = $response['reason'];
                CrawlerSupport::debugLog("ERROR: {$reason}");

                $parseResult->failed = true;
            }
        }

        $this->buffer->add($parseResult);
    }

    private function isOk(Response $response): bool
    {
        return $response->getStatusCode() === 200;
    }
}
