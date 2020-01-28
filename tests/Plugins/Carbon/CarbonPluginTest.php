<?php


namespace W2w\Test\Apie\Plugins\Carbon;

use Carbon\Carbon;
use DateTimeZone;
use erasys\OpenApi\Spec\v3\Schema;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\Carbon\CarbonPlugin;

class CarbonPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $this->apie = new Apie([new CarbonPlugin()], true, null);
    }

    public function test_serializer_works_with_carbon()
    {
        $serializer = $this->apie->getResourceSerializer();
        $actual = $serializer->normalize(
            Carbon::createFromTimestamp(0, new DateTimeZone('Europe/Amsterdam')),
            'application/json'
        );
        $this->assertEquals('1970-01-01 01:00:00', $actual);
    }

    public function test_schema_is_correct()
    {
        $schemaGenerator = $this->apie->getSchemaGenerator();

        $actual = $schemaGenerator->createSchema(Carbon::class, 'get', ['get', 'read']);
        $this->assertEquals(new Schema(['type' => 'string', 'format' => 'date-time']), $actual);
    }
}
