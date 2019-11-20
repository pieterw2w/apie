# apie
[![CircleCI](https://circleci.com/gh/pjordaan/apie.svg?style=svg)](https://circleci.com/gh/pjordaan/apie)
[![codecov](https://codecov.io/gh/pjordaan/apie/branch/master/graph/badge.svg)](https://codecov.io/gh/pjordaan/apie/)
[![Travis](https://api.travis-ci.org/pjordaan/apie.svg?branch=master)](https://travis-ci.org/pjordaan/apie)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pjordaan/apie/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pjordaan/apie/?branch=master)

library to convert simple POPO's (Plain Old PHP Objects), DTO (Data Transfer Objects) and Entities to a REST API with OpenAPI spec. It's still a work in progress,
but there are tons of unit tests and a bridge to integrate the library in [Laravel](https://github.com/pjordaan/laravel-apie).

## setting up apie
You should only follow these instructions in case you want to write it framework agnostic or when your framework has no binding to this library.

Make sure you have [composer](https://getcomposer.org) installed and then in a terminal run:
```bash
composer require w2w/apie 
```

To ease setting up Apie the class ServiceLibraryFactory is created. This class creates a ApiResourceFacade and
an OpenApiSpecGenerator instance with very little setup. In case you do want to add extra functionality, you need
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
use W2w\Lib\Apie\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Retrievers\ApplicationInfoRetriever;
use W2w\Lib\Apie\ServiceLibraryFactory;
$factory = new ServiceLibraryFactory([App::class], true, sys_get_temp_dir());
$factory->setContainer(new class implements ContainerInterface {
    public function get($id)
    {
        if ($id === ApplicationInfoRetriever::class) {
            return new ApplicationInfoRetriever('application name', 'prod', '1.0', false);
        }
        throw new RuntimeException('Service ' . $id . ' not found!');
    }
    
    public function has($id)
    {
        return $id === ApplicationInfoRetriever::class;       
    }
});

// get the facade to handle REST API calls...
$library = $factory->getApiResourceFacade();
// if we would not have called setContainer we would get an error ApplicationInfoRetriever
// could not be instantiated.
var_dump($library->get(ApplicationInfo::class, 'name', null)->getResource());
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
use W2w\Lib\Apie\Retrievers\FileStorageDataLayer;

/**
 * @ApiResource(retrieveClass=FileStorageDataLayer::class)
 */
class Example {
    /**
     * @var Uuid
     */
    public $id;
}
```
If we would check the OpenAPI spec we would get a get single resource and a get all resources route and the response
will only contain an id. FileStorageDataLayer requires a folder where to store a resource, so we require the step at [injecting dependencies](#injecting-dependencies)
to set up a folder:
```php
<?php
require(__DIR__ . '/vendor/autoload.php');

use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Retrievers\FileStorageDataLayer;
use W2w\Lib\Apie\ServiceLibraryFactory;
$factory = new ServiceLibraryFactory([Example::class], true, sys_get_temp_dir());
$factory->setContainer(new class implements ContainerInterface {
    public function get($id)
    {
        if ($id === FileStorageDataLayer::class) {
            return new FileStorageDataLayer(
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'apie-resource',
                PropertyAccess::createPropertyAccessor()
            );
        }
        throw new RuntimeException('Service ' . $id . ' not found!');
    }
    
    public function has($id)
    {
        return $id === FileStorageDataLayer::class;       
    }
});
```
Right now we need to setup the class that will be used to persist this Example resource. This can be done by adding the
property persistClass to the ApiResource annotation.
 ```php
 <?php
 use Ramsey\Uuid\Uuid;
 use W2w\Lib\Apie\Annotations\ApiResource;
 use W2w\Lib\Apie\Retrievers\FileStorageDataLayer;
 
 /**
  * @ApiResource(
  *     retrieveClass=FileStorageDataLayer::class,
  *     persistClass=FileStorageDataLayer::class
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
 FileStorageDataLayer has trouble creating a filename). We also do not want DELETE and do not want to be able to change the
 id on PUT. This is possible with little effort.
 ```php
 <?php
  use Ramsey\Uuid\Uuid;
  use W2w\Lib\Apie\Annotations\ApiResource;
  use W2w\Lib\Apie\Retrievers\FileStorageDataLayer;
 /**
   * @ApiResource(
   *     retrieveClass=FileStorageDataLayer::class,
   *     persistClass=FileStorageDataLayer::class,
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
additional information can be found there.

## Validating fields
Apie assumes that the resource classes created are never in an invalid state and they should take care of never
getting in a inconsistent state, which is a guideline of Domain Driven Design entities.
Because of that you require to validate and clean the input yourself in the resource class.

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
A better solution would be to use value objects. Apie is set up to work with value objects. You require to implement
the interface W2w\Lib\Apie\ValueObjects\ValueObjectInterface.
They will also be mapped correctly in the OpenAPI schema. A value object for e-mail would be written like this.
To assist programmers a W2w\Lib\Apie\ValueObjects\StringEnumTrait and a W2w\Lib\Apie\ValueObjects\StringTrait were
created to make easy value objects.

A simple e-mail address value object would look like this:
```php
<?php
use W2w\Lib\Apie\ValueObjects\StringTrait;
use W2w\Lib\Apie\ValueObjects\ValueObjectInterface;

class Email implements ValueObjectInterface
{
    use StringTrait;
    
    protected function sanitizeValue(string $value): string
    {
        return trim($value);
    }
    
    protected function validValue(string $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return (bool) preg_match('/@apie\.nl$/', $value);
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

In case you have a value object created with an other library, you might need to manually
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
so you require to make your own route binding.
With a library like [nikic/fast-route](https://github.com/nikic/FastRoute) it is very easy to make a framework agnostic REST API.
 
It contains the following controllers:
- **W2w\Lib\Apie\Controllers\DeleteController**: handles ```DELETE /{resource class}/{id}``` requests to delete a single resource
- **W2w\Lib\Apie\Controllers\DocsController**: returns the OpenAPI spec as a response.
- **W2w\Lib\Apie\Controllers\GetAllController**: handles ```GET /{resource class}/``` requests to get all resources
- **W2w\Lib\Apie\Controllers\GetController**: handles ```GET /{resource class}/{id}``` requests to get a single resource
- **W2w\Lib\Apie\Controllers\PostController**: handles ```POST /{resource class}/``` requests to create a new resource
- **W2w\Lib\Apie\Controllers\PutController**: handles ```PUT /{resource class}/{id}``` requests to modify an existing resource

## Adding search filters
Normally if you do the REST API call to get all resources you will only get pagination and no filtering.

It is possible to add filtering that will also be added in the OpenAPI specs. If you use one of the default classes in this
library, you get them for free. You only need to manually set up which fields can be filtered.

 ```php
 <?php
  use Ramsey\Uuid\Uuid;
  use W2w\Lib\Apie\Annotations\ApiResource;
  use W2w\Lib\Apie\Retrievers\FileStorageDataLayer;
 /**
   * @ApiResource(
   *     retrieveClass=FileStorageDataLayer::class,
   *     persistClass=FileStorageDataLayer::class,
   *     context={
   *         "search": {
   *             "email": "string"       
   *         }
   *     }
   * )
   */
  class Example {
      private $id;

      /**
       * @var string 
       */
      public $email;

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

Now if you check the OpenAPI spec an 'email' search filter is added and marked as a string. It is possible to compare value
objects with strings/integers.

If you have your own retriever class, you require to add the filtering manually by implementing the interface W2w\Lib\Apie\Retrievers\SearchFilterProviderInterface
and do something with the SearchFilterRequest you get in retrieveAll. An implementation that gets the search filters
from the class configuration is in the trait W2w\Lib\Apie\Retrievers\SearchFilterFromMetadataTrait.

If your retriever always returns all records
the filtering can be easily programmed with W2w\Lib\Apie\Searchilters\SearchFilterHelper::applySearchFilter():

```php
<?php
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use W2w\Lib\Apie\Retrievers\ApiResourceRetrieverInterface;
use W2w\Lib\Apie\Retrievers\SearchFilterFromMetadataTrait;
use W2w\Lib\Apie\Retrievers\SearchFilterProviderInterface;
use W2w\Lib\Apie\SearchFilters\SearchFilterHelper;
use W2w\Lib\Apie\SearchFilters\SearchFilterRequest;

class ExampleRetriever implements ApiResourceRetrieverInterface, SearchFilterProviderInterface
{
    use SearchFilterFromMetadataTrait;
    
    private $propertyAccess;
    
    public function __construct(PropertyAccessorInterface $propertyAccess)
    {
        $this->propertyAccess = $propertyAccess;
    }
    
    public function retrieve(string $resourceClass, $id, array $context)
    {
        // implementation..
    }
    
    public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest): iterable
    {
        $allRecords  = $this->methodThatRetrievesAll();
        return SearchFilterHelper::applySearchFilter($allRecords, $searchFilterRequest, $this->propertyAccess);
    }
}
```

## Apie vs. Api Platform
This library is heavily inspired by the Symfony Api Platform, but there are some changes:
- This library is framework agnostic and requires a wrapper library to make it work in a framework. Api Platform core is framework agnostic, but it is hard to setup outside the symfony framework.
- In the Api Platform a resource provider or persister determines if it can persist or retrieve a specific resource with a supports() method. For Apie the resource class is explicitly linked to a service making it easier to select which HTTP methods are available.
- Both libraries use the symfony serializer component with their own object normalizer, but Apie is closer to the default ObjectNormalizer. This could change in the future since a ValidationException with proper RFC standard would require a change here.
- API Platform has no default serialization group if no serialization group is selected.
- So far APIE has less functionality for standards (JSON+LD, HAL) and no GraphQL support. Eventually we might add it.
- APIE is better capable of having api resources without an id.
