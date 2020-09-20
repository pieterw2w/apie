<?php

namespace W2w\Test\Apie\Features\Core;

use Exception;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\DefaultApie;
use W2w\Lib\ApieObjectAccessNormalizer\Exceptions\ValidationException;

class CoreTest extends TestCase
{
    public function testExceptions_are_properly_serialized_in_debug_mode()
    {
        $apie = DefaultApie::createDefaultApie(true);
        $actual = $apie->getResourceSerializer()->normalize(
            new Exception('This is a test', 42, new Exception('previous')),
            'application/json'
        );
        $this->assertArrayHasKey('trace', $actual);
        // trace string could differ between php version/OS, so we do not check the exact value
        $this->assertIsString($actual['trace']);
        unset($actual['trace']);
        $this->assertEquals(
            [
                'message' => 'This is a test',
                'code' => 42
            ],
            $actual
        );
    }

    public function testExceptions_are_properly_serialized_in_production_mode()
    {
        $apie = DefaultApie::createDefaultApie(false);
        $actual = $apie->getResourceSerializer()->normalize(
            new Exception('This is a test', 42, new Exception('previous')),
            'application/json'
        );
        $this->assertEquals(
            [
                'message' => 'This is a test',
                'code' => 42
            ],
            $actual
        );
    }

    public function testValidationExceptions_are_properly_serialized_in_production_mode()
    {
        $apie = DefaultApie::createDefaultApie(false);
        $actual = $apie->getResourceSerializer()->normalize(
            new ValidationException(['key' => ['required']]),
            'application/json'
        );
        $this->assertEquals(
            [
                'message' => 'A validation error occurred',
                'code' => 0,
                'status_code' => 422,
                'errors' => [
                    'key' => ['required']
                ],
            ],
            $actual
        );
    }

    public function testExceptionHasProperSchema()
    {
        $apie = DefaultApie::createDefaultApie(false);
        $actual = $apie->getSchemaGenerator()->createSchema(
            Exception::class,
            'get',
            ['get', 'read']
        )->toArray();
        $this->assertEquals(
            [
                'title' => 'Exception',
                'description' => 'Exception get for groups get, read',
                'type' => 'object',
                'properties' => [
                    'message' => [
                        'type' => 'string',
                        'nullable' => false,
                    ],
                    'code' => [
                        'type' => 'integer',
                        'nullable' => false,
                    ],
                ]
            ],
            $actual
        );

    }
}
