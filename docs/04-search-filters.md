## Adding search filters
Normally if you do the REST API call to get all resources you will only get pagination and no filtering.

It is possible to add filtering that will also be added in the OpenAPI specs. If you use one of the default classes in this
library, you get them for free. You only need to manually set up which fields can be filtered and the type of the field for OpenAPI specs.

 ```php
<?php
use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\Plugins\FileStorage\DataLayers\FileStorageDataLayer;
 
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
objects with strings/integers/bools.

If you have your own retriever class, you require to add the filtering manually by implementing the interface W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface
and do something with the SearchFilterRequest you get in retrieveAll. An implementation that gets the search filters
from the class configuration is in the trait W2w\Lib\Apie\Retrievers\SearchFilterFromMetadataTrait.

If your retriever always returns all records
the filtering can be easily programmed with W2w\Lib\Apie\Core\SearchFilters\SearchFilterHelper::applySearchFilter():

```php
<?php
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterFromMetadataTrait;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterHelper;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Interfaces\ApiResourceRetrieverInterface;  
use W2w\Lib\Apie\Interfaces\SearchFilterProviderInterface;

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
