<?php


namespace W2w\Lib\Apie\Plugins\ApplicationInfo\Guesser;

use W2w\Lib\Apie\Apie;

/**
 * Gues the values for ApplicationInfo. It's better to provide them to the plugin though for performance reasons.
 */
final class AppGuesser
{
    public static function determineApp(): string
    {
        $name = 'Apie ';
        $version = Apie::VERSION;
        $locations = [
            __DIR__ . '/../../../../../../../composer.json',
            __DIR__ . '/../../../../composer.json',
        ];
        foreach ($locations as $location) {
            if (is_readable($location)) {
                $result = json_decode(file_get_contents($location), true);
                if (is_array($result)) {
                    $name = $result['name'] ?? $name;
                    $version = $result['version'] ?? $version;
                    return $name . ' ' . $version;
                }
            }
        }
        return $name . $version;
    }

    public static function determineEnvironment(bool $debug = false): string
    {
        $envs = ['env', 'environment', 'ENV', 'ENVIRONMENT', 'APP_ENV'];
        foreach ($envs as $env) {
            if (isset($_ENV[$env])) {
                return $_ENV[$env];
            }
        }
        return $debug ? 'dev' : 'prod';
    }

    public static function determineHash(): string
    {
        $locations = [
            __DIR__ . '/../../../../../../../.git/ORIG_HEAD',
            __DIR__ . '/../../../../.git/ORIG_HEAD',
        ];
        foreach ($locations as $location) {
            if (is_readable($location)) {
                $result = trim(file_get_contents($location));
                if ($result) {
                    return $result;
                }
            }
        }
        return '-';
    }
}
