<?php

namespace Root\AnchorElementCrawler;

use Illuminate\Support\Facades\Log;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler as ExternalCrawler;

class Crawler
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    public function parse(string $url): void
    {
        $request = new GetExternalResourceContent($this->client);
        
        $html = $request->fetchExternalLink($url);
        
        $crawler = new ExternalCrawler($html);
        
        $links = $crawler->filterXPath('descendant-or-self::body//a');
        
        foreach ($links as $anchor) {
            $anchorCrawler = new ExternalCrawler($anchor);
        
            if ($anchorCrawler->attr('rel') !== null) {
                $relValue = $anchorCrawler->attr('rel');
                Log::channel('stdout')->debug("The 'rel' attribute exists and its value is: " . $relValue);
            } else {
                Log::channel('stdout')->debug("The 'rel' attribute does not exist on this anchor tag.");
            }
        
            if ($anchorCrawler->attr('href') !== null) {
                $hrefValue = $anchorCrawler->attr('href');
                Log::channel('stdout')->debug("The 'href' attribute exists and its value is: " . $hrefValue);
            } else {
                Log::channel('stdout')->debug("The 'href' attribute does not exist on this anchor tag.");
            }

            Log::channel('stdout')->debug("Tag: {$anchor->nodeName}");
            Log::channel('stdout')->debug("Context: {$anchor->textContent}");
        }
    }
}