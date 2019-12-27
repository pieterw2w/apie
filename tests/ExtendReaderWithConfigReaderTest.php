<?php
namespace W2w\Test\Apie;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Serializer\Annotation\SerializedName;
use W2w\Lib\Apie\Annotations\ApiResource;
use W2w\Lib\Apie\ExtendReaderWithConfigReader;
use W2w\Lib\Apie\Persisters\NullPersister;
use W2w\Lib\Apie\Retrievers\MemoryDataLayer;
use W2w\Test\Apie\Mocks\Data\SimplePopo;

class ExtendReaderWithConfigReaderTest extends TestCase
{
    public function test_read_annotations_are_passed()
    {
        $testItem = new ExtendReaderWithConfigReader(new AnnotationReader(), []);
        $expected = ApiResource::createFromArray(['persistClass' => NullPersister::class]);
        $this->assertEquals(
            [$expected],
            $testItem->getClassAnnotations(new ReflectionClass(SimplePopo::class))
        );
        $this->assertEquals(
            $expected,
            $testItem->getClassAnnotation(new ReflectionClass(SimplePopo::class), ApiResource::class)
        );
        $this->assertEquals(
            [],
            $testItem->getPropertyAnnotations(new ReflectionProperty(SimplePopo::class, 'arbitraryField'))
        );
        $this->assertEquals(
            null,
            $testItem->getPropertyAnnotation(new ReflectionProperty(SimplePopo::class, 'arbitraryField'), SerializedName::class)
        );
        $this->assertEquals(
            [],
            $testItem->getMethodAnnotations(new ReflectionMethod(SimplePopo::class, 'getId'))
        );
        $this->assertEquals(
            null,
            $testItem->getMethodAnnotation(new ReflectionMethod(SimplePopo::class, 'getId'), SerializedName::class)
        );
    }

    /**
     * @dataProvider getClassAnnotationsProvider
     */
    public function testGetClassAnnotations(array $expected, string $message, array $config, string $className)
    {
        $testItem = new ExtendReaderWithConfigReader(new AnnotationReader(), $config);
        $this->assertEquals(
            $expected,
            $testItem->getClassAnnotations(new ReflectionClass($className)),
            $message
        );
    }

    public function getClassAnnotationsProvider()
    {
        $expected = ApiResource::createFromArray(['persistClass' => NullPersister::class]);
        $alternate = ApiResource::createFromArray(['persistClass' => MemoryDataLayer::class, 'retrieveClass' => MemoryDataLayer::class]);
        yield [[$expected], 'No config override, regular annotation', [], SimplePopo::class];
        yield [[], 'No config override, no annotation', [], __CLASS__];
        yield [[$alternate], 'Config override, regular annotation', [SimplePopo::class => $alternate], SimplePopo::class];
        yield [[$alternate], 'Config override, no annotation', [__CLASS__ => $alternate], __CLASS__];
    }

    /**
     * @dataProvider getClassAnnotationProvider
     */
    public function testGetClassAnnotation(?ApiResource $expected, string $message, array $config, string $className, $annotationName)
    {
        $testItem = new ExtendReaderWithConfigReader(new AnnotationReader(), $config);
        $this->assertEquals(
            $expected,
            $testItem->getClassAnnotation(new ReflectionClass($className), $annotationName),
            $message
        );
    }

    public function getClassAnnotationProvider()
    {
        $expected = ApiResource::createFromArray(['persistClass' => NullPersister::class]);
        $alternate = ApiResource::createFromArray(['persistClass' => MemoryDataLayer::class, 'retrieveClass' => MemoryDataLayer::class]);
        yield [$expected, 'No config override, regular annotation', [], SimplePopo::class, ApiResource::class];
        yield [null, 'No config override, no annotation', [], __CLASS__, ApiResource::class];
        yield [$alternate, 'Config override, regular annotation', [SimplePopo::class => $alternate], SimplePopo::class, ApiResource::class];
        yield [$alternate, 'Config override, no annotation', [__CLASS__ => $alternate], __CLASS__, ApiResource::class];

        yield [null, 'Other annotation, No config override, regular annotation', [], SimplePopo::class, SerializedName::class];
        yield [null, 'Other annotation, No config override, no annotation', [], __CLASS__, SerializedName::class];
        yield [null, 'Other annotation, Config override, regular annotation', [SimplePopo::class => $alternate], SimplePopo::class, SerializedName::class];
        yield [null, 'Other annotation, Config override, no annotation', [__CLASS__ => $alternate], __CLASS__, SerializedName::class];
    }
}
