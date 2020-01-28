# Apie plugins
Since version 3, Apie consists of a list of plugins that adds functionality. If you want you can even make Apie
instances without the CorePlugin being used.
A plugin implements one or more interfaces from the namespace W2w\Lib\Apie\PluginInterfaces\PluginInterfaces.

Common used interfaces:
- **ApieConfigInterface**: Apie configuration, for example base url, etc.
- **ApiResourceFactoryProviderInterface**: Provider of ApiResourcePersisterInterface and ApiResourceRetrieverInterface instances.
- **NormalizerProviderInterface**: Provider of normalizing data from a class to a primitive value and vice versa.
- **ResourceProviderInterface**: Provide a list of classes that will be used as API resources.
- **SchemaProviderInterface**: Provide schema for OpenAPI for specific classes.

Other interfaces:
- **AnnotationReaderProviderInterface**: Reading configurations for API resources.
- **CacheItemPoolProviderInterface**: Provider of a PSR6 Cache Item Pool.
- **EncoderProviderInterface**: Provider of new encoders used to convert an array to a string representation and vice versa.
- **OpenApiEventProviderInterface**: Event listener on the generated OpenAPI spec so changes can be made.
- **OpenApiInfoProviderInterface**: Provide the info section of an OpenAPI spec
- **SerializerProviderInterface**: Provide a resource serializer that converts classes in arrays and vice versa.
- **SymfonyComponentProviderInterface**: Provide several symfony component instances.

## Default Apie plugins
There is a list of default Apie plugins. These are already set by default with DefaultApie::createDefaultApie().

- **CorePlugin**: Contains most core logic that is probably not going to be overwritten by any other plugin.
- **DateTimePlugin**: Contains logic to map dates correctly.
- **CarbonPlugin**: Same as DateTimePlugin but prefers [Carbon](https://carbon.nesbot.com/) instances. Ignored if Carbon is not installed.
- **UuidPlugin**: Maps uuids correctly from [ramsey/uuid](https://github.com/ramsey/uuid).
- **ApplicationInfoPlugin**: Adds an application_info endpoint to tell what environment/application this is.
- **StatusCheckPlugin**: Adds a status endpoint to do a health check on the REST api.

## Other core plugins
There is also a list of plugins in the apie core that are very convenient but not enabled by default:
- **FakeAnnotationsPlugin**: Add configuration for api resources as an array and not as annotations.
- **FileStoragePlugin**: Adds FileStorageDataLayer. Requires a path where to store it.
- **MockPlugin**: Override the api resource factories to use a mock data layer to change the REST API in a mocked REST API
- **StaticConfigPlugin**: Add configuration for Apie, for example a base url.
- **StaticResourcesPlugin**: Adds a list of class names that should be used as API resources. 

## Other plugins
With composer additional plugins can be installed with also additional dependencies:
- **[ApieDomainPlugin](https://github.com/pjordaan/apie-domain-plugin)**: Adds Domain value objects with correct check on tld.

## Apie aware plugins
Plugins that require services from the Apie instances (for example services from a different plugin) require to know
the current Apie instance. To make this possible there is an ApieAwareInterface and a ApieAwareTrait.

```php
<?php
use W2w\Lib\Apie\PluginInterfaces\ApieAwareInterface;
use W2w\Lib\Apie\PluginInterfaces\ApieAwareTrait;
use W2w\Lib\Apie\PluginInterfaces\ResourceProviderInterface;

/**
 * Some imaginary plugin that creates mock class instances of every resource created by the IlluminatePlugin.
 */
class ExamplePlugin implements ResourceProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;
    
    private $generator;
    
    public function __construct(MockClassGenerator $generator)
    {
        $this->generator = $generator;
    }
    
    public function getResources(): array
    {
        return array_map(
            function ($resourceClass) {
                return $this->generator->createMockClass($resourceClass);
            },
            $this->getApie()->getPlugin(IlluminatePlugin::class)->getResources()
        );
    }
}
```
