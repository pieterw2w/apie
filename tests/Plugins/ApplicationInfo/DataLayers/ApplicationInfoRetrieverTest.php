<?php
namespace W2w\Test\Apie\Plugins\ApplicationInfo\DataLayers;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Core\SearchFilters\SearchFilterRequest;
use W2w\Lib\Apie\Plugins\ApplicationInfo\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\Plugins\ApplicationInfo\DataLayers\ApplicationInfoRetriever;

class ApplicationInfoRetrieverTest extends TestCase
{
    public function testRetrieve()
    {
        $testItem = new ApplicationInfoRetriever('Unit test app', 'test', 'hash-123', true);
        $this->assertEquals(
            new ApplicationInfo('Unit test app', 'test', 'hash-123', true),
            $testItem->retrieve(ApplicationInfo::class, 'name', [])
        );
    }

    public function testRetrieveAll()
    {
        $testItem = new ApplicationInfoRetriever('Unit test app', 'test', 'hash-123', true);
        $this->assertEquals(
            [new ApplicationInfo('Unit test app', 'test', 'hash-123', true)],
            $testItem->retrieveAll(ApplicationInfo::class, [], new SearchFilterRequest())
        );
        $this->assertEquals(
            [],
            $testItem->retrieveAll(ApplicationInfo::class, [], new SearchFilterRequest(1, 10))
        );
    }
}
