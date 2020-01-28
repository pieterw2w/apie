<?php

namespace W2w\Test\Apie\Plugins\Uuid;

use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\Uuid\UuidPlugin;

class UuidPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $this->apie = new Apie([new UuidPlugin()], true, null);
    }

    public function test_serializer_works_with_uuid()
    {
        $serializer = $this->apie->getResourceSerializer();
        $actual = $serializer->normalize(
            Uuid::fromString('986e12c4-3011-4ed8-aead-c62b76bb7f69'),
            'application/json'
        );
        $this->assertEquals('986e12c4-3011-4ed8-aead-c62b76bb7f69', $actual);
    }

    public function test_schema_is_correct()
    {
        $schemaGenerator = $this->apie->getSchemaGenerator();

        $actual = $schemaGenerator->createSchema(Uuid::class, 'get', ['get', 'read']);
        $this->assertEquals(new Schema(['type' => 'string', 'format' => 'uuid']), $actual);
    }
}
