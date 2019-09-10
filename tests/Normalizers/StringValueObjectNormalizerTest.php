<?php


namespace W2w\Test\Apie\Normalizers;

use PhpValueObjects\AbstractStringValueObject;
use PhpValueObjects\Tests\Geography\LocaleValueObject;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Normalizers\StringValueObjectNormalizer;

class StringValueObjectNormalizerTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($expectedOutput, $input)
    {
        $testItem = new StringValueObjectNormalizer();
        if (is_null($expectedOutput)) {
            $this->assertFalse($testItem->supportsNormalization($input));
            return;
        }
        $this->assertTrue($testItem->supportsNormalization($input));
        $this->assertEquals($expectedOutput, $testItem->normalize($input));
    }

    public function normalizeProvider()
    {
        yield [null, 'text'];
        yield [null, AbstractValueObject::class];
        yield ['nl_NL', new LocaleValueObject('nl_NL')];
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($expectedOutput, $input, string $inputClass)
    {
        $testItem = new StringValueObjectNormalizer();
        if (is_null($expectedOutput)) {
            $this->assertFalse($testItem->supportsDenormalization($input, $inputClass));
            return;
        }
        $this->assertTrue($testItem->supportsDenormalization($input, $inputClass));
        $this->assertEquals($expectedOutput, $testItem->denormalize($input, $inputClass));
    }

    public function denormalizeProvider()
    {
        yield [null, 'text', 'string'];
        yield [null, AbstractValueObject::class, 'string'];
        yield ['nl_NL', 'nl_NL', LocaleValueObject::class];
    }
}
