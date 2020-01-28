## Versioning
It's very common to have versioning for your REST API or that your application has more than one REST API. Often they share the same configuration
and only the base url and the available api resources are different. Since version 3 this is very easy to add.

```php
<?php
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;

$debug = true;
$commonPlugins = [];
$cacheFolder = sys_get_temp_dir() . '/apie-cache';

$baseApie = DefaultApie::createDefaultApie($debug, $commonPlugins, $cacheFolder);
$apieVersion1 = $baseApie->createContext(
    [
        new StaticConfigPlugin('/v1'),
        new StaticResourcesPlugin([App\V1\Class1::class, App\V1\Class2::class]),
    ]
);
$apieVersion2 = $baseApie->createContext(
    [
        new StaticConfigPlugin('/v2'),
        new StaticResourcesPlugin([App\V2\Class1::class, App\V2\Class2::class]),
    ]
);

// you can always get the parent Apie instance like this, because Apie is an Apie plugin itself.
assert($apieVersion1->getPlugin(Apie::class) === $baseApie);
```

