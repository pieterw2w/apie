# apie
[![CircleCI](https://circleci.com/gh/pjordaan/apie.svg?style=svg)](https://circleci.com/gh/pjordaan/apie)

library to convert simple POPO's (Plain Old PHP Objects) to a REST API with OpenAPI spec. WIP

## setting up apie
To set up Apie easily in your project the class ServiceLibraryFactory is created to ease setting it up.
This special has setters in case you want to inject external dependencies. If none are set
you get a simple Apie service.

Make sure you have [composer](https://getcomposer.org) installed and then in a terminal run:
```bash
composer require w2w/apie 
```

Make a PHP file like this to have a functional library instance.
```php
<?php
require(__DIR__ . '/vendor/autoload.php');

use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Persisters\NullPersister;
use W2w\Lib\Apie\ServiceLibraryFactory;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

/**
 * @ApiResource(persistClass=NullPersister::class)
 */
class Example {
    /**
     * @var string
     */
    public $example;
}

$factory = new ServiceLibraryFactory([Example::class], $debug, $cacheFolder);
$library = $factory->getApiResourceFacade();

$request = (new ServerRequest())->withBody(new Stream('data://text/plain,{"example":"test"}'));

// echoes class Example with { example: test }
var_dump($library->post(Example::class, $request)
    ->getResource());

// store the REST api spec as json in file.json:
$openApiSpecGenerator = $factory->getOpenApiSpecGenerator('https://my-host-api.nl/');
file_put_contents(__DIR__ . '/file.json', $openApiSpecGenerator->getOpenApiSpec()->toJson());
```
