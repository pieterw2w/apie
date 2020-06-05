<?php


namespace W2w\Test\Apie\Plugins\PrimaryKey;

use erasys\OpenApi\Spec\v3\Schema;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\Apie\Plugins\PrimaryKey\PrimaryKeyPlugin;
use W2w\Lib\Apie\Plugins\StaticConfig\StaticResourcesPlugin;
use W2w\Test\Apie\ForwardsCompatibleTestCase;
use W2w\Test\Apie\OpenApiSchema\Data\RecursiveObjectWithId;

class PrimaryKeyPluginTest extends ForwardsCompatibleTestCase
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
                'title' => 'RecursiveObjectWithId',
                'description' => 'RecursiveObjectWithId get for groups get, read',
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
                            'example' => '/recursive_object_with_id/12345',
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
