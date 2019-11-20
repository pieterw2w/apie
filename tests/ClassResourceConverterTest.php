<?php
namespace W2w\Test\Apie;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use W2w\Lib\Apie\ApiResources\ApplicationInfo;
use W2w\Lib\Apie\ApiResources\Status;
use W2w\Lib\Apie\Resources\ApiResources;
use W2w\Lib\Apie\ClassResourceConverter;
use W2w\Lib\Apie\Exceptions\ResourceNameNotFoundException;
use W2w\Test\Apie\Mocks\Data\SimplePopo;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObject;

class ClassResourceConverterTest extends TestCase
{
    private $testItem;

    protected function setUp(): void
    {
        $this->testItem = new ClassResourceConverter(
            new CamelCaseToSnakeCaseNameConverter(),
            new ApiResources([ApplicationInfo::class, Status::class, SimplePopo::class, RecursiveObject::class]),
            true
        );
    }

    public function testNormalize()
    {
        $this->assertEquals('application_info', $this->testItem->normalize(ApplicationInfo::class));
        $this->assertEquals('simple_popo', $this->testItem->normalize(SimplePopo::class));
        $this->assertEquals('class_resource_converter_test', $this->testItem->normalize(__CLASS__));
    }

    public function testDenormalize()
    {
        $this->assertEquals(ApplicationInfo::class, $this->testItem->denormalize('application_info'));
        $this->assertEquals(SimplePopo::class, $this->testItem->denormalize('simple_popo'));
    }

    public function testDenormalize_throw_exception_if_not_api_resource()
    {
        $this->expectException(ResourceNameNotFoundException::class);
        $this->testItem->denormalize('class_resource_converter_test');
    }
}
