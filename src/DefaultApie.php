<?php

namespace W2w\Lib\Apie;

use Carbon\Carbon;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApplicationInfoPlugin;
use W2w\Lib\Apie\Plugins\Carbon\CarbonPlugin;
use W2w\Lib\Apie\Plugins\DateTime\DateTimePlugin;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusCheckPlugin;
use W2w\Lib\Apie\Plugins\Uuid\UuidPlugin;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjectPlugin;

/**
 * Helper class to make a general Apie instance with common plugins active.
 */
class DefaultApie
{
    public static function createDefaultApie(bool $debug = false, array $additionalPlugins = [], ?string $cacheFolder = null, bool $defaultResources = true): Apie
    {
        $plugins = $additionalPlugins;
        $plugins[] = class_exists(Carbon::class) ? new CarbonPlugin() : new DateTimePlugin();
        $plugins[] = new UuidPlugin();
        $plugins[] = new ValueObjectPlugin();
        if ($defaultResources) {
            $plugins[] = new ApplicationInfoPlugin();
            $plugins[] = new StatusCheckPlugin([]);
        }
        return new Apie($plugins, $debug, $cacheFolder);
    }
}
