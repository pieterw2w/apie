## Apie terminology
- Apie works with a list of simple PHP objects and maps them to a resource-based REST API.
- A data layer is needed to persist and/or retrieve simple PHP objects.
- A simple PHP object requires a field as identifier, in most cases called 'id' or 'uuid' to find/retrieve the same object.
- If a simple PHP object has no configured identifier or no field with 'id' or 'uuid' GET, PUT and DELETE of a single resource is not possible.
- If a simple PHP object has no data layer to persist, it can not do POST, PUT and DELETE
- If a simple PHP object has no data layer to retrieve, it can not do GET, PUT and DELETE
- GET /resource/ is always provided, but returns an empty array if there is no way to retrieve it. This is done because many openapi generators expect this call to be available.
- Resource objects are always used for input and output.
- The response given is the symfony serializer serializing the object back using the public getters and properties of an object.

Apie uses these simple rules:
- A POST /resource/ call creates a simple PHP object how you would create an object in PHP with something like ```new SimpleObject($input['requiredArgument'])``` and then use the data layer to store it.
- A GET /resource/{id} call asks the  data layer to retrieve an object with an identifier
- A GET /resource/ call asks the data layer for all objects
- A PUT /resource/{id} call asks the data layer to retrieve an object with an identifier and calls setter to modify the object. Then the data layer persists the changes.
- A DELETE /resource/{id} call asks the data layer to retrieve an object with an identifier and then calls the data layer to remove it from persistence.

## How does the mapping work:
First of all we put a class annotation of type ApiResource on the class to configure the class for APIE.
With the FakeAnnotationsPlugin it is possible to configure it without annotations.

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
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;

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
will only contain an id. FileStorageDataLayer requires a folder where to store a resource, so we require the
FileStoragePlugin to configure a folder for storing resources.
```php
<?php
require(__DIR__ . '/vendor/autoload.php');

$debug = true;
$cacheFolder = sys_get_temp_dir() . '/apie';

use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\FileStorage\FileStoragePlugin;

$apie = DefaultApie::createDefaultApie(
    $debug,
    [
        new StaticResourcesPlugin([Example::class]),
        new StaticConfigPlugin('https://my-host-api.nl/'),
        new FileStoragePlugin(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'apie-resources')
    ],
    $cacheFolder
);
```
Right now we need to setup the class that will be used to persist this Example resource. This can be done by adding the
property persistClass to the ApiResource annotation.
 ```php
 <?php
 use Ramsey\Uuid\Uuid;
 use W2w\Lib\Apie\Annotations\ApiResource;
 use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;
 
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
A better solution would be to use value objects. Apie is set up to work with value objects if the ValueObjectPlugin is added. This is already done
if you use DefaultApie::createDefaultApie()

You require to implement the interface ValueObjectInterface.
They will also be mapped correctly in the OpenAPI schema. A value object for e-mail would be written like this.
To assist programmers a StringEnumTrait and a StringTrait were
created to make easy value objects.

A simple e-mail address value object would look like this:
```php
<?php

use W2w\Lib\Apie\Interfaces\ValueObjectInterface;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjects\StringTrait;

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

In case you have a value object created with an other library, you require to write an Apie plugin for it.
In general this requires a plugin class that implements SchemaProviderInterface to provide OpenAPI schemas
and NormalizerProviderInterface to normalize/denormalize the value from/to a primitive value and a value object. 

Right now we have the [apie-domain-plugin](https://github.com/pjordaan/apie-domain-plugin) to integrate
Domain objects created with jeremykendall/php-domain-parser.

```php
<?php
use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\PluginInterfaces\NormalizerProviderInterface;
use W2w\Lib\Apie\PluginInterfaces\SchemaProviderInterface;

class ValueObjectExamplePlugin implements SchemaProviderInterface, NormalizerProviderInterface
{
    public function getNormalizers(): array
    {
        return [new ClassNameNormalizer()];
    }
    
    public function getDefinedStaticData(): array
    {
        return [
            ExampleValueObject::class => new Schema(['type' => 'string', 'format' => 'uuid', 'example' => 'Example value']);
        ];
    }
    
    public function getDynamicSchemaLogic(): array
    {
        return [];
    }
}
```
