<?php

namespace Root\AnchorElementCrawler;

class Command
{
    public static function isCommand(): bool
    {
        // Check if the script is run from the command line
        if (php_sapi_name() === 'cli') {
            return true;
        } else {
            echo "This script should be run from the command line.\n";
            return false;
        }

        return false;
    }
}