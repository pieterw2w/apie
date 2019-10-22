# apie
[![CircleCI](https://circleci.com/gh/pjordaan/apie.svg?style=svg)](https://circleci.com/gh/pjordaan/apie)
[![codecov](https://codecov.io/gh/pjordaan/apie/branch/master/graph/badge.svg)](https://codecov.io/gh/pjordaan/apie/)

library to convert simple POPO's (Plain Old PHP Objects) to a REST API with OpenAPI spec. It's still a work in progress,
but there are tons of unit tests and a bridge to integrate the library in [Laravel](https://github.com/pjordaan/laravel-apie).

## setting up apie
You should only follow these instructions in case you want to write it framework agnostic or when your framework has no binding to this library.

Make sure you have [composer](https://getcomposer.org) installed and then in a terminal run:
```bash
composer require w2w/apie 
```

To ease setting up Apie the class ServiceLibraryFactory is created. This class creates a ApiResourceFacade and
a OpenApiSpecGenerator instance with very little setup. In case you do want to add extra functionality, you need
to call setters on ServiceLibraryFactory.

Usage:
```php
<?php
use W2w\Lib\Apie\ServiceLibraryFactory;
$listOfResourceClasses = [ClassName::class, ClassName2::class];
$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

$factory = new ServiceLibraryFactory($listOfResourceClasses, $debug, $cacheFolder);
```
The list of resource classes is a list of class names that will be used to map them to REST API calls.

A more extensive example is written down here. It will store an OpenAPI spec by using the Example class
as a resource and https://my-host-api.nl/ as base url of the API.

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
you can inject a service container.

The service container injected is only used to get classes
to retrieve or persist domain objects. Overriding different parts (Annotation Reader, Serializer instance) is done
with different setters in ServiceLibraryFactory.

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

## How does the mapping work:
Now that we know how the library can be instantiated we will look a bit closer how APIE maps a simple PHP object to
a REST API.
First of all we put a class annotation of type ApiResource on the class to configure the class for APIE.
```php
<?php
use W2w\Lib\Apie\Annotations\ApiResource;

/**
* @ApiResource()
 */
class Example {
}
```
The example above does not do anything. Apie does not know how to retrieve instances of Example and does not know how to persist them.
It also has no fields.
The only REST API call that would show up in an OpenAPI spec would be to retrieve all Example instances which will return
an empty array. In the ApiResource we need to configure a class to persist or a class to retrieve. We also need to provide
an identifier in our object.

So let's add a retrieveClass option and an id property.
```php
<?php
use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Retrievers\FileStorageRetriever;

/**
 * @ApiResource(retrieveClass=FileStorageRetriever::class)
 */
class Example {
    /**
     * @var Uuid
     */
    public $id;
}
```
If we would check the OpenAPI spec we would get a get single resource and a get all resources route and the response
will only contain an id. FileStorageRetriever requires a folder where to store a resource, so we require the step at [injecting dependencies](#injecting-dependencies)
to set up a folder:
```php
<?php
require(__DIR__ . '/vendor/autoload.php');

use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Retrievers\FileStorageRetriever;
use W2w\Lib\Apie\ServiceLibraryFactory;
$factory = new ServiceLibraryFactory([Example::class], true, sys_get_temp_dir());
$factory->setContainer(new class implements ContainerInterface {
    public function get($id)
    {
        if ($id === FileStorageRetriever::class) {
            return new FileStorageRetriever(
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'apie-resource',
                PropertyAccess::createPropertyAccessor()
            );
        }
        throw new RuntimeException('Service ' . $id . ' not found!');
    }
    
    public function has($id)
    {
        return $id === FileStorageRetriever::class;       
    }
});
```
Right now we need to setup the class that will be used to persist this Example resource. This can be done by adding the
property persistClass to the ApiResource annotation.
 ```php
 <?php
 use Ramsey\Uuid\Uuid;
 use W2w\Lib\Apie\Annotations\ApiResource;
 use W2w\Lib\Apie\Retrievers\FileStorageRetriever;
 
 /**
  * @ApiResource(
  *     retrieveClass=FileStorageRetriever::class,
  *     persistClass=FileStorageRetriever::class
  * )
  */
 class Example {
     /**
      * @var Uuid
      */
     public $id;
 }
 ```
 The OpenAPI spec will add routes for DELETE, PUT and POST. There are still two problems. We want to make POST
 indempotent and want to make the id required on POST (even though a lack of id will already fail because
 FileStorageRetriever has trouble creating a filename). We also do not want DELETE and do not want to be able to change the
 id on PUT. This is possible with little effort.
 ```php
 <?php
  use Ramsey\Uuid\Uuid;
  use W2w\Lib\Apie\Annotations\ApiResource;
  use W2w\Lib\Apie\Retrievers\FileStorageRetriever;
 /**
   * @ApiResource(
   *     retrieveClass=FileStorageRetriever::class,
   *     persistClass=FileStorageRetriever::class,
   *     disabledMethods={"delete"}
   * )
   */
  class Example {
      private $id;

      public function __construct(Uuid $id)
      {
          $this->id = $id;
      }      
      
      public function getId(): Uuid
      {
          return $this->id;
      }
  }
```
With disabledMethods we disable the DELETE route.
Id is now a required constructor argument and is required for POST to create an instance of Example. It will also throw a
normalization error if id is not in a uuid format.
Id can also not be changed when it is constructed, so PUT no longer allows us to change the id with PUT.
This serialization is done with [the symfony serializer](https://symfony.com/doc/current/components/serializer.html) and
additional information can be found here.

## Validating fields
Apie assumes that the resource classes created are always in a robust state and they should take care of never
getting in a inconsistent state. Because of that you require to validate and clean the input yourself in the resource class.

```php
<?php
use Symfony\Component\HttpKernel\Exception\HttpException;

class Example {
    private $email;
    public function setEmail(string $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException(422, 'invalid e-mail "' . $email .'""');
        }
        $this->email = $email;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
}
```
A better solution would be to use value objects. Apie is set up to work with value objects created with the composer library
[bruli/php-value-objects](https://github.com/bruli/php-value-objects). They will also be mapped correctly in the OpenAPI schema as a string. The library has an e-mail value
object, but let's assume we want to always lowercase e-mail addresses and only allow @apie.nl:
```php
<?php
use PhpValueObjects\AbstractValueObject;

class Email extends AbstractValueObject
{
    public function __construct(string $value)
    {
        parent::__construct(strtolower($value));
    }
    protected function guard($value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->throwException($value);
        }
        if (!preg_match('/@apie\.nl$/', $value)) {
            $this->throwException($value);
        }
    }
}
```

We will change the setter and getter accordingly:
```php
<?php
class Example {
    private $email;
    public function setEmail(Email $email) {
        $this->email = $email;
    }
    
    public function getEmail(): Email {
        return $this->email;
    }
}
```

In case you have a value object that does not extend PhpValueObjects\AbstractValueObject you might need to manually
change the OpenAPI schema for this class to a string. You might also require to [create a (de)normalizer](https://symfony.com/doc/current/serializer/custom_normalizer.html#creating-a-new-normalizer) for the symfony serializer 
to convert this object from/to a string.
```php
<?php
use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\ServiceLibraryFactory;

$factory = new ServiceLibraryFactory($listOfResourceClasses, $debug, $cacheFolder);
// register the normalizer to normalize/denormalize strings for your value object.
$factory->setAdditionalNormalizers([new ClassNameNormalizer()]);
// make sure the OpenAPI spec is correct for your value object:
$factory->getSchemaGenerator()->defineSchemaForResource(ClassName::class, new Schema(['type' => 'string']));
```

## PSR Controllers and routing
If your framework supports PSR7 request/responses, then the framework can use one of the predefined controllers
in W2w\Lib\Apie\Controllers. Every controller has an __invoke() method. The library has no url match functionality,
so you require to make your own route binding. With a library like [nikic/fast-route](https://github.com/nikic/FastRoute) it is very easy to make a framework agnostic REST API.
 
It contains the following controllers:
- **W2w\Lib\Apie\Controllers\DeleteController**: handles ```DELETE /{resource class}/{id}``` requests to delete a single resource
- **W2w\Lib\Apie\Controllers\DocsController**: returns the OpenAPI spec as a response.
- **W2w\Lib\Apie\Controllers\GetAllController**: handles ```GET /{resource class}/``` requests to get all resources
- **W2w\Lib\Apie\Controllers\GetController**: handles ```GET /{resource class}/{id}``` requests to get a single resource
- **W2w\Lib\Apie\Controllers\PostController**: handles ```POST /{resource class}/``` requests to create a new resource
- **W2w\Lib\Apie\Controllers\PutController**: handles ```PUT /{resource class}/{id}``` requests to modify an existing resource

## Apie vs. Api Platform
This library is heavily inspired by the Symfony Api Platform, but there are some changes:
- This library is framework agnostic and requires a wrapper library to make it work in a framework. Api Platform core is framework agnostic, but it is hard to setup outside the symfony framework.
- In the Api Platform a resource provider or persister determines if it can persist or retrieve a specific resource with a supports() method. For Apie the resource class is explicitly linked to a service making it easier to select which HTTP methods are available.
- Both libraries use the symfony serializer component with their own object normalizer, but Apie is closer to the default ObjectNormalizer.
- API Platform has no default serialization group if no serialization group is selected.
- So far APIE has less functionality for standards (JSON+LD, HAL) and no GraphQL support. Eventually we might add it.
- APIE is better capable of having api resources without an id.
