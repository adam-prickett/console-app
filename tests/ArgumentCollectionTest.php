<?php

namespace AppTests;

use System\Support\ArgumentCollection;
use PHPUnit_Framework_TestCase;

class ArgumentCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorSetsValues()
    {
        $collection = new ArgumentCollection([
            'command' => 'command1',
            'options' => [
                'option1' => 'value1',
                'option2' => 'value2',
            ],
            'args' => [
                'argument1',
                'argument2',
            ],
        ]);

        $this->assertCount(2, $collection->getOptions());
        $this->assertCount(2, $collection->getArguments());
        $this->assertEquals($collection->getCommand(), 'command1');
    }
}
