<?php

namespace Root\AnchorElementCrawler;

use Symfony\Component\DomCrawler\Crawler as ExternalCrawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
            echo "###Found match###" . PHP_EOL;
        
            $anchorCrawler = new ExternalCrawler($anchor);
        
            if ($anchorCrawler->attr('rel') !== null) {
                $relValue = $anchorCrawler->attr('rel');
                echo "The 'rel' attribute exists and its value is: " . $relValue . "\n";
            } else {
                echo "The 'rel' attribute does not exist on this anchor tag.\n";
            }
        
            if ($anchorCrawler->attr('href') !== null) {
                $hrefValue = $anchorCrawler->attr('href');
                echo "The 'href' attribute exists and its value is: " . $hrefValue . "\n";
            } else {
                echo "The 'href' attribute does not exist on this anchor tag.\n";
            }
        
            echo "Tag: {$anchor->nodeName}" . PHP_EOL;
            echo "Context: {$anchor->textContent}" . PHP_EOL;
        }
    }
}