## Setting up apie
You should only follow these instructions in case you want to write it framework agnostic or when your framework has no binding to this library.

Make sure you have [composer](https://getcomposer.org) installed and then in a terminal run:
```bash
composer require w2w/apie 
```

The basic usage is using the class Apie which asks for a list of [Apie plugins](05-plugins.md), boolean if it is
debug mode and an optional cache folder location.

The core of Apie comes with a set of default plugins and you can also use the class DefaultApie to set up
a list of common Apie plugins (uuid, value objects, etc.)

Usage:
```php
<?php
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApplicationInfoPlugin;
use W2w\Lib\Apie\Plugins\DateTime\DateTimePlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Lib\Apie\Plugins\Uuid\UuidPlugin;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjectPlugin;

$listOfResourceClasses = [ClassName::class, ClassName2::class];
$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

$factory = new Apie(
    [
        new StaticResourcesPlugin($listOfResourceClasses),
        new StaticConfigPlugin('https://rest-api.nl/'),
        new DateTimePlugin(),
        new UuidPlugin(),
        new ValueObjectPlugin(),
        new ApplicationInfoPlugin(),
        new StatusCheckPlugin([])
    ],
    $debug,
    $cacheFolder
);
```
The list of resource classes is a list of class names that will be used to map them to REST API calls.

The core provides a list of default plugins. We have a DefaultApie class that auto-initializes these.
Then the call will be:

```php
<?php
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;

$listOfResourceClasses = [ClassName::class, ClassName2::class];
$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

$factory = DefaultApie::createDefaultApie(
    $debug,
    [
        new StaticResourcesPlugin($listOfResourceClasses),
        new StaticConfigPlugin('https://rest-api.nl/'),
    ],
    $cacheFolder
);
```

A more extensive example is written down here. It will store an OpenAPI spec by using the Example class
as a resource and https://my-host-api.nl/ as base url of the API.

```php
<?php
require(__DIR__ . '/vendor/autoload.php');

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\Core\DataLayers\NullDataLayer;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticConfigPlugin;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

/**
 * @ApiResource(persistClass=NullDataLayer::class)
 */
class Example {
    /**
     * @var string
     */
    public $example;
}

$apie = DefaultApie::createDefaultApie(
    $debug,
    [
        new StaticResourcesPlugin([Example::class]),
        new StaticConfigPlugin('https://my-host-api.nl/'),
    ],
    $cacheFolder
);
// get the facade to handle REST API calls...
$library = $apie->getApiResourceFacade();
// get the open api spec generator:
$openApiSpecGenerator = $apie->getOpenApiSpecGenerator();

// store the REST api spec as json in file.json:
file_put_contents(__DIR__ . '/file.json', $openApiSpecGenerator->getOpenApiSpec()->toJson());

$request = (new ServerRequest())->withBody(new Stream('data://text/plain,{"example":"test"}'));
// echoes class Example with { example: test }
var_dump($library->post(Example::class, $request)
    ->getResource());
// throws exception method not allowed:
$library->get(Example::class, 1, null);
```

## Providing data layer implementations
To store/retrieve an object with Apie you require to use a data layer class. Since data layer classes
can have dependencies you require to write some code how these data layer classes are instantiated.
Apie uses a [plugin system](05-plugins.md) for this. So we need a plugin that implements ApiResourceFactoryProviderInterface
 and a class that implements ApiResourceFactoryInterface:

```php
<?php
require(__DIR__ . '/vendor/autoload.php');

$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Interfaces\ApiResourceFactoryInterface;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ResourceFactories\ApplicationInfoRetrieverFallbackFactory;
use W2w\Lib\Apie\PluginInterfaces\ApiResourceFactoryProviderInterface;

class CustomApplicationInfoDataLayerPlugin implements ApiResourceFactoryProviderInterface
{
    public function getApiResourceFactory(): ApiResourceFactoryInterface
    {
        return new ApplicationInfoRetrieverFallbackFactory('application name', 'prod', '1.0', false);
    }
}

$apie = DefaultApie::createDefaultApie(
    $debug,
    [
        new StaticResourcesPlugin([ApplicationInfo::class]),
        new StaticConfigPlugin('https://my-host-api.nl/'),
        new CustomApplicationInfoDataLayerPlugin()
    ],
    $cacheFolder,
    false
);

// get the facade to handle REST API calls...
$library = $apie->getApiResourceFacade();
// if we would not have added CustomApplicationInfoDataLayerPlugin we would get an error ApplicationInfoRetriever
// could not be instantiated.
var_dump($library->get(ApplicationInfo::class, 'name', null)->getResource());
```
