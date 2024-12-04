<?php

namespace Root\AnchorElementCrawler;

use Illuminate\Support\Facades\Log;

class CrawlerSupport
{
    public static function debugLog(string $message): void
    {
        if (self::isFacadeAvailable('Log')) {
            Log::channel('stdout')->debug($message);
        } elseif (Command::isCommand()) {
            // If the Log facade is not available, just output to the console.
            echo $message . PHP_EOL;
        }
    }

    /**
     * Check if the facade root has been set.
     */
    private static function isFacadeAvailable($facadeName): bool
    {
        $facadeClass = 'Illuminate\\Support\\Facades\\' . $facadeName;
        return class_exists($facadeClass) && !is_null($facadeClass::getFacadeRoot());
    }
}
