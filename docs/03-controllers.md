## PSR Controllers and routing
If your framework supports PSR7 request/responses, then the framework can use one of the predefined controllers
in W2w\Lib\Apie\Controllers. Every controller has an __invoke() method. The library has no url match functionality,
so you require to make your own route binding.
With a library like [nikic/fast-route](https://github.com/nikic/FastRoute) it is very easy to make a framework agnostic REST API.

You require to assign the request attribute 'resource' to get the resource name and request attribute 'id' to get an id 
to make these work.
 
It contains the following controllers:
- **W2w\Lib\Apie\Controllers\DeleteController**: handles ```DELETE /{resource class}/{id}``` requests to delete a single resource
- **W2w\Lib\Apie\Controllers\DocsController**: returns the OpenAPI spec as a response.
- **W2w\Lib\Apie\Controllers\GetAllController**: handles ```GET /{resource class}/``` requests to get all resources
- **W2w\Lib\Apie\Controllers\GetController**: handles ```GET /{resource class}/{id}``` requests to get a single resource
- **W2w\Lib\Apie\Controllers\PostController**: handles ```POST /{resource class}/``` requests to create a new resource
- **W2w\Lib\Apie\Controllers\PutController**: handles ```PUT /{resource class}/{id}``` requests to modify an existing resource

## Resource name
the resource name is mapping to a specific resource class and throws a HTTPException with status code 404 if it could not
be found. The class used to do this mapping is W2w\Lib\Apie\Core\ClassResourceConverter.

An Apie plugin that implements W2w\Lib\Apie\PluginInterfaces\SymfonyComponentProviderInterface can be created to override the
behaviour.

```php
<?php
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use W2w\Lib\Apie\Plugins\Core\CorePlugin;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\SymfonyComponentProviderInterface;

class DifferentResourceNamingPlugin implements SymfonyComponentProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    public function getClassMetadataFactory(): ClassMetadataFactoryInterface
    {
        return $this->getApie()->getPlugin(CorePlugin::class)->getClassMetadataFactory();
    }
    public function getPropertyConverter(): NameConverterInterface
    {
        return new CamelCaseToSnakeCaseNameConverter();
    }
    
    public function getPropertyAccessor(): PropertyAccessor
    {
        return $this->getApie()->getPlugin(CorePlugin::class)->getPropertyAccessor();
    }
}

```
