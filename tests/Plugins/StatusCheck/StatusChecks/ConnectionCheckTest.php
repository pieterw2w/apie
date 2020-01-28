<?php
namespace W2w\Test\Apie\Plugins\StatusCheck\StatusChecks;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Plugins\StatusCheck\ApiResources\Status;
use W2w\Lib\Apie\Plugins\StatusCheck\StatusChecks\ConnectionCheck;

class ConnectionCheckTest extends TestCase
{
    /**
     * @dataProvider getStatusProvider
     */
    public function testGetStatus(Status $expected, array $inputResponses, bool $parseResponse, bool $debug)
    {
        $mock = new MockHandler($inputResponses);
        $handler = new HandlerStack($mock);
        $client = new Client(['handler' => $handler]);
        $testItem = new ConnectionCheck($client, 'test connection check', 'status', $parseResponse, $debug);
        $this->assertEquals($expected, $testItem->getStatus());

    }

    public function getStatusProvider()
    {
        $counter = 1;
        while (file_exists(__DIR__ . '/mockData/testcase' . $counter . '.expected.php')) {
            $expected = require __DIR__ . '/mockData/testcase' . $counter . '.expected.php';
            $inputResponses = require __DIR__ . '/mockData/testcase' . $counter . '.input.php';
            yield [$expected[false][false], $inputResponses, false, false];
            yield [$expected[false][true], $inputResponses, false, true];
            yield [$expected[true][true], $inputResponses, true, true];
            yield [$expected[true][false], $inputResponses, true, false];
            $counter++;
        }
    }
}
