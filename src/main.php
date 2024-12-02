<?php

require __DIR__.'/../vendor/autoload.php';

use Root\AnchorElementCrawler\Command;
use Root\AnchorElementCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;

echo "\nRun crawler" . PHP_EOL;

$html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <p class="message">Hello World!</p>
        <p>Hello Crawler!</p>
        <a>What is SEO!</a>
    </body>
</html>
HTML;

// Define an array to store the flags and their values
$flags = [];

if (Command::isCommand()) {
    // Loop through each command-line argument
    foreach ($argv as $arg) {
        // Check if the argument is a flag with a value
        if (preg_match('/^--(\w+)(?:=(.+))?$/i', $arg, $matches)) {
            $flag = $matches[1];
            $value = $matches[2] ?? true; // Default to true if no value is provided
            $flags[$flag] = $value;
        }
    }
}

// Check if the --url flag is set
if (isset($flags['url'])) {
    // Do something with the --url flag
    echo "The --url flag was provided with the value: " . $flags['url'] . "\n";
} else {
    return;
}

$c = new Crawler(HttpClient::create());

$c->parse($flags['url']);