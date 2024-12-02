<?php

namespace Root\AnchorElementCrawler;

class Command
{
    /**
     * Check if the script is run from the command line
     */
    public static function isCommand(): bool
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }

        return false;
    }
}
