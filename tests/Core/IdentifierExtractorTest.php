<?php
namespace W2w\Test\Apie\Core;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\Core\IdentifierExtractor;
use W2w\Lib\ApieObjectAccessNormalizer\ObjectAccess\ObjectAccess;
use W2w\Test\Apie\Mocks\ApiResources\FullRestObject;
use W2w\Test\Apie\Mocks\ApiResources\SimplePopo;

class IdentifierExtractorTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new IdentifierExtractor(new ObjectAccess());
    }

    public function testGetIdentifierKey()
    {
        $testObject = new SimplePopo();
        $this->assertEquals('id', $this->testItem->getIdentifierKey($testObject, []));
    }

    public function testGetIdentifierKey_with_context()
    {
        $testObject = new SimplePopo();
        $this->assertEquals(
            'createdAt',
            $this->testItem->getIdentifierKey($testObject, ['identifier' => 'createdAt'])
        );
    }

    public function testGetIdentifierValue()
    {
        $testObject = new SimplePopo();
        $this->assertEquals($testObject->getId(), $this->testItem->getIdentifierValue($testObject, []));
    }

    public function testGetIdentifierValue_with_context()
    {
        $testObject = new SimplePopo();
        $this->assertEquals(
            $testObject->getCreatedAt(),
            $this->testItem->getIdentifierValue($testObject, ['identifier' => 'createdAt'])
        );
    }

    public function testGetIdentifierKeyOfClass()
    {
        $this->assertEquals('id', $this->testItem->getIdentifierKeyOfClass(SimplePopo::class));
        $this->assertEquals('uuid', $this->testItem->getIdentifierKeyOfClass(FullRestObject::class));
        $this->assertEquals(null, $this->testItem->getIdentifierKeyOfClass(__CLASS__));
        $this->assertEquals('pizza', $this->testItem->getIdentifierKeyOfClass(__CLASS__, ['identifier' => 'pizza']));
    }
}
