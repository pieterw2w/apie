# apie
[![CircleCI](https://circleci.com/gh/pjordaan/apie.svg?style=svg)](https://circleci.com/gh/pjordaan/apie)

library to convert simple POPO's (Plain Old PHP Objects) to a REST API with OpenAPI spec. It's still a work in progress,
but there are tons of unit tests and a bridge to integrate the library in [Laravel](https://github.com/pjordaan/laravel-apie).

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
// get the facade to handle REST API calls...
$library = $factory->getApiResourceFacade();
// get the open api spec generator:
$openApiSpecGenerator = $factory->getOpenApiSpecGenerator('https://my-host-api.nl/');

// store the REST api spec as json in file.json:
file_put_contents(__DIR__ . '/file.json', $openApiSpecGenerator->getOpenApiSpec()->toJson());

$request = (new ServerRequest())->withBody(new Stream('data://text/plain,{"example":"test"}'));
// echoes class Example with { example: test }
var_dump($library->post(Example::class, $request)
    ->getResource());
// throws exception method not allowed:
$library->get(Example::class, 1, null);
```

## Injecting dependencies
As long the ServiceLibraryFactory has not instantiated a service you can inject dependencies to integrate it with
custom files. For example sometimes you want to use a service to persist your class with a database which requires a
dependency to a database connection service for example. Because most frameworks use a service container for this,
you can inject a service container. The service container injected is only used to get classes
to retrieve or persist domain objects. You need to call different setters if different dependencies
are required to be injected.

```php
<?php
require(__DIR__ . '/vendor/autoload.php');

use Psr\Container\ContainerInterface;
use W2w\Lib\Apie\ApiResources\App;
use W2w\Lib\Apie\Retrievers\AppRetriever;
use W2w\Lib\Apie\ServiceLibraryFactory;
$factory = new ServiceLibraryFactory([App::class], true, sys_get_temp_dir());
$factory->setContainer(new class implements ContainerInterface {
    public function get($id)
    {
        if ($id === AppRetriever::class) {
            return new AppRetriever('application name', 'prod', '1.0', false);
        }
        throw new RuntimeException('Service ' . $id . ' not found!');
    }
    
    public function has($id)
    {
        return $id === AppRetriever::class;       
    }
});

// get the facade to handle REST API calls...
$library = $factory->getApiResourceFacade();
// if we would not have called setContainer we would get an error AppRetriever
// could not be instantiated.
var_dump($library->get(App::class, 'name', null)->getResource());
```

## PSR Controllers
If your framework supports PSR7 request/responses, then the framework can use one of the predefined controllers
in W2w\Lib\Apie\Controllers. Every controller has an __invoke() method.

It contains the following controllers:
- **W2w\Lib\Apie\Controllers\DeleteController**: handles ```DELETE /resource/{id}``` requests to delete a singel resource
- **W2w\Lib\Apie\Controllers\DocsController**: returns the OpenAPI spec as a response.
- **W2w\Lib\Apie\Controllers\GetAllController**: handles ```GET /resource/``` requests to get all resources
- **W2w\Lib\Apie\Controllers\GetController**: handles ```GET /resource/{id}``` requests to get a single resource
- **W2w\Lib\Apie\Controllers\PostController**: handles ```POST /resource/``` requests to create a new resource
- **W2w\Lib\Apie\Controllers\PutController**: handles ```PUT /resource/{id}``` requests to modify an existing resource
