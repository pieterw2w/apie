<?php


namespace W2w\Test\Apie\Plugins\PrimaryKey;

use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\PrimaryKey\PrimaryKeyPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class PrimaryKeyPluginTest extends TestCase
{
    public function testSchemaIsProperlyCreated()
    {
        $apie = DefaultApie::createDefaultApie(
            true,
            [
                new StaticResourcesPlugin([RecursiveObjectWithId::class]),
                new PrimaryKeyPlugin(),
            ],
            null,
            false
        );
        $expected = new Schema(
            [
                'title' => 'recursive_object_with_id',
                'description' => 'recursive_object_with_id get for groups get, read',
                'type' => 'object',
                'properties' => [
                    'id' => new Schema(
                        [
                            'type' => 'string',
                            'format' => 'uuid',
                        ]
                    ),
                    'child' => new Schema(
                        [
                            'type' => 'string',
                            'format' => 'path',
                            'nullable' => true,
                        ]
                    )
                ]
            ]
        );
        $actual = $apie->getSchemaGenerator()->createSchema(
            RecursiveObjectWithId::class,
            'get',
            ['get', 'read']
        );
        $this->assertEquals($expected, $actual);
    }
}
