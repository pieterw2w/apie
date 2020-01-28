<?php
namespace W2w\Test\Apie\Plugins\ApplicationInfo\Guesser;

use PHPUnit\Framework\TestCase;
use W2w\Lib\Apie\Apie;
use W2w\Lib\Apie\Plugins\ApplicationInfo\Guesser\AppGuesser;

class AppGuesserTest extends TestCase
{
    public function test_it_works()
    {
        $this->assertNotEmpty(AppGuesser::determineHash());
        $this->assertEquals('w2w/apie ' . Apie::VERSION, AppGuesser::determineApp());
        $this->assertEquals('dev', AppGuesser::determineEnvironment(true));
    }
}
