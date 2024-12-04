<?php

namespace Root\AnchorElementCrawler;

use Symfony\Component\DomCrawler\Crawler as ExternalCrawler;
use GuzzleHttp\Client;

class Crawler
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function parse(string $url): void
    {
        $resources = new GetExternalResourceContent($this->client);

        $resources->setUrls([$url]);
        $resources->wait();

        $this->handle($resources);
    }

    private function handle(GetExternalResourceContent $resources): void
    {
        foreach ($resources->responses() as $response) {
            if ($response['state'] === 'fulfilled') {
                // Process successful response
                $content = $response['value']->getBody()->getContents();
                $crawler = new ExternalCrawler($content);
                $links = $crawler->filterXPath('descendant-or-self::body//a');

                foreach ($links as $anchor) {
                    $anchorCrawler = new ExternalCrawler($anchor);
                
                    if ($anchorCrawler->attr('rel') !== null) {
                        $relValue = $anchorCrawler->attr('rel');
                        CrawlerSupport::debugLog("The 'rel' attribute exists and its value is: " . $relValue);
                    } else {
                        CrawlerSupport::debugLog("The 'rel' attribute does not exist on this anchor tag.");
                    }
                
                    if ($anchorCrawler->attr('href') !== null) {
                        $hrefValue = $anchorCrawler->attr('href');
                        CrawlerSupport::debugLog("The 'href' attribute exists and its value is: " . $hrefValue);
                    } else {
                        CrawlerSupport::debugLog("The 'href' attribute does not exist on this anchor tag.");
                    }
        
                    CrawlerSupport::debugLog("Tag: {$anchor->nodeName}");
                    CrawlerSupport::debugLog("Context: {$anchor->textContent}");
                }
            } else {
                $reason = $response['reason'];
                CrawlerSupport::debugLog("ERROR: {$reason}");
            }
        }
    }
}
