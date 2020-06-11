## Subactions
Since apie 3.3 it is possible to add sub actions.
Sub actions  are basically actions you do on Api resources that are normally mapped with an extra path in the url,
for example POST /resource/1/checksum would calculate a checksum for a resource called of type 'resource' with id 1.

Sub actions are just classes with a handle method. The typehint of the method determines on what resources it
can be used and maybe additional arguments that provides in the POST body.

For example a checksum subaction class would be something like this:

```php
<?php
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;

class Checksum 
{
    private $serializer;
    
    public function __construct(ResourceSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    
    public function handle($resource): string
    {
        return md5(json_encode($this->serializer->normalize($resource, 'application/json')));
    }
}
```

Because you typehint it to accept any resource and typehint a return value of string the OpenAPI spec
can be generated easily.

We still need to register it with a plugin that implements SubActionsProviderInterface:

```php
<?php
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\SubActionsProviderInterface;

class ChecksumPlugin implements SubActionsProviderInterface, ApieAwareInterface
 {
    use ApieAwareTrait;
    
    public function getSubActions()
    {
        return [
            'checksum' => new Checksum($this->getApie()->getResourceSerializer()),
        ];
    } 
}
```

Now every resource that can retrieve a single resource will have a POST checksum action.

### Sub actions by interface

We could filter who gets the action for example like this:

```php
<?php
use W2w\Lib\Apie\Interfaces\ResourceSerializerInterface;

interface Checksummable
{
}

class Checksum {
    private $serializer;
    
    public function __construct(ResourceSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    
    public function handle(Checksummable $resource): string
    {
        return md5(json_encode($this->serializer->normalize($resource, 'application/json')));
    }
}
```

Now only resources that implement Checksummable will get the sub action. 

## SupportedAwareSubActionInterface

Another solution would be to let the sub action implement SupportedAwareSubActionInterface:

```php
<?php
use W2w\Lib\Apie\Interfaces\SupportedAwareSubActionInterface;

class Checksum implements SupportedAwareSubActionInterface
{
    
    public function isSupported(string $resourceClass) : bool
    {
        $reflectionClass = new ReflectionClass($resourceClass);
        return $reflectionClass->hasMethod('getId') && $reflectionClass->getMethod('getId')->isPublic();
    }
    
    public function handle($resource): string
    {
        return md5($resource->getId());
    }
}
```

### Additional arguments
There is no sub action interface as then it is not possible to use every typehint in a sub action.
It also allows us to add additional arguments:
```php
<?php
use W2w\Lib\Apie\Interfaces\SupportedAwareSubActionInterface;
use W2w\Lib\Apie\Plugins\ValueObject\ValueObjects\StringEnumTrait;
use W2w\Lib\Apie\Interfaces\ValueObjectInterface;

class ChecksumMethod implements ValueObjectInterface
{
    use StringEnumTrait;
    
    const MD5 = 'MD5';
    
    const CRC32 = 'CRC32';
}

class Checksum implements SupportedAwareSubActionInterface
{
    
    public function isSupported(string $resourceClass) : bool
    {
        $reflectionClass = new ReflectionClass($resourceClass);
        return $reflectionClass->hasMethod('getId') && $reflectionClass->getMethod('getId')->isPublic();
    }
    
    public function handle($resource, ChecksumMethod $method): string
    {
        if ($method->toNative() === ChecksumMethod::MD5) {
            return md5($resource->getId());
        }
        return crc32($resource->getId());
    }
}
```
In the open api spec the body shows you require to add 'method' field with either 'MD5' or 'CRC32'.

Not providing this gives a validation error.
