<?php

namespace AppTests;

use System\Axo;
use Mockery;
use PHPUnit_Framework_TestCase;

class AxoApplicationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('AXO_PATH')) {
            define('AXO_PATH', rtrim(dirname(__FILE__), '/').'/../');
        }
    }

    public function testApplicationLoads()
    {
        $app = $this->getMockBuilder('System\Axo')
                    ->setMethods(['version'])
                    ->getMock();

        $this->assertTrue(is_float($app->version()));
    }

    public function testApplicationFailsOnMissingCommand()
    {
        $this->expectOutputString('commandnotexist does not exist'.PHP_EOL);

        $app = $this->getMockBuilder('System\Axo')
                    ->setMethods()
                    ->getMock();

        $app->run(['run', 'commandnotexist', 'test']);
    }

    public function testApplicationLoadCommandCustomDirectory()
    {
        $this->expectOutputString('SUCCESS');

        $app = $this->getMockBuilder('System\Axo')
                    ->setMethods()
                    ->getMock();

        $app->addCommandDirectory(__DIR__.'/Commands', 'AppTests\Commands');

        $app->run(['run', 'test', '--option1', '-o', 'argument']);
    }

    public function testApplicationComplainsMissingArgument()
    {
        $this->expectOutputString('php run test ARG1 [ARG2]'.PHP_EOL);

        $app = $this->getMockBuilder('System\Axo')
                    ->setMethods()
                    ->getMock();

        $app->addCommandDirectory(__DIR__.'/Commands', 'AppTests\Commands');

        $app->run(['run', 'test', '--option1', '-o']);
    }

    public function testApplicationComplainsMissingOption()
    {
        $this->expectOutputString('--option2 / -o option missing'.PHP_EOL);

        $app = $this->getMockBuilder('System\Axo')
                    ->setMethods()
                    ->getMock();

        $app->addCommandDirectory(__DIR__.'/Commands', 'AppTests\Commands');

        $app->run(['run', 'test', '--option1', 'argument']);
    }
}
