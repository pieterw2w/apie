<?php


namespace W2w\Test\Apie\Exceptions;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Exceptions\InvalidIdException;
use W2w\Lib\Apie\Exceptions\InvalidPageLimitException;
use W2w\Lib\Apie\Exceptions\InvalidValueForValueObjectException;
use W2w\Lib\Apie\Exceptions\MethodNotAllowedException;
use W2w\Lib\Apie\Exceptions\PageIndexShouldNotBeNegativeException;
use W2w\Test\Apie\Mocks\ValueObjects\ObjectWithCollection;

class LocalizationableTest extends TestCase
{
    public function testPageIndexNegative()
    {
        $testItem = new PageIndexShouldNotBeNegativeException();
        $actual = $testItem->getI18n();
        $this->assertSame('validation.min', $actual->getMessageString());
        $this->assertEquals(
            [
                'value' => 'page',
                'minimum' => 0
            ],
            $actual->getReplacements()
        );
    }

    public function testPageLimitNegative()
    {
        $testItem = new InvalidPageLimitException();
        $actual = $testItem->getI18n();
        $this->assertSame('validation.min', $actual->getMessageString());
        $this->assertEquals(
            [
                'value' => 'limit',
                'minimum' => 1
            ],
            $actual->getReplacements()
        );
    }

    public function testInvalidId()
    {
        $testItem = new InvalidIdException('iD software');
        $actual = $testItem->getI18n();
        $this->assertSame('validation.id', $actual->getMessageString());
        $this->assertEquals(
            [
                'id' => 'iD software',
            ],
            $actual->getReplacements()
        );
    }

    public function testMethodNotAllowed()
    {
        $testItem = new MethodNotAllowedException('GET');
        $actual = $testItem->getI18n();
        $this->assertSame('general.method_not_allowed', $actual->getMessageString());
        $this->assertEquals(
            [
                'method' => 'GET',
            ],
            $actual->getReplacements()
        );
    }

    public function testValueObjectInvalid()
    {
        $testItem = new InvalidValueForValueObjectException(
            'test',
            ObjectWithCollection::class
        );
        $actual = $testItem->getI18n();
        $this->assertSame('validation.format', $actual->getMessageString());
        $this->assertEquals(
            [
                'name' => 'object_with_collection',
                'value' => 'test',
            ],
            $actual->getReplacements()
        );
    }
}
