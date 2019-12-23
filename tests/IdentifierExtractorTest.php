<?php

namespace W2w\Test\Apie;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use W2w\Lib\Apie\IdentifierExtractor;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class IdentifierExtractorTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new IdentifierExtractor(PropertyAccess::createPropertyAccessor());
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
            'created_at',
            $this->testItem->getIdentifierKey($testObject, ['identifier' => 'created_at'])
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
            $this->testItem->getIdentifierValue($testObject, ['identifier' => 'created_at'])
        );
    }

}
