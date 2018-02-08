<?php

namespace AppTests;

use PHPUnit\Framework\TestCase;
use System\Support\ArgumentParser;
use System\Support\ArgumentCollection;

class ArgumentParserTest extends TestCase
{
    public function testCommandIsParsed()
    {
        $args = [
            'test',
            '--option1="value1"',
            'argument1',
        ];

        $parser = new ArgumentParser;
        $results = $parser->parse($args);

        $this->assertInstanceOf(ArgumentCollection::class, $results);

        $command = $results->getCommand();
        $this->assertEquals($command, 'test');
    }

    public function testOptionsAreParsed()
    {
        $args = [
            'test',
            '--option1="value1"',
            '--option2=value2',
        ];

        $parser = new ArgumentParser;
        $results = $parser->parse($args);

        $options = $results->getOptions();
        $this->assertTrue(is_array($options));
        $this->assertCount(2, $options);
        $this->assertEquals($options['option1'], 'value1');
        $this->assertEquals($options['option2'], 'value2');
    }

    public function testShortOptionsAreParsed()
    {
        $args = [
            'test',
            '--option1="value1"',
            '-o=value2',
            'argument1',
        ];

        $parser = new ArgumentParser;
        $results = $parser->parse($args);

        $options = $results->getOptions();
        $this->assertTrue(is_array($options));
        $this->assertCount(2, $options);
        $this->assertEquals($options['o'], 'value2');
    }

    public function testArgumentsAreParsed()
    {
        $args = [
            'test',
            '--option1="value1"',
            'argument1',
            'arg2',
        ];

        $parser = new ArgumentParser;
        $results = $parser->parse($args);

        $arguments = $results->getArguments();
        $this->assertTrue(is_array($arguments));
        $this->assertEquals($arguments[0], 'argument1');
        $this->assertEquals($arguments[1], 'arg2');
    }

    public function testSingleQuotedDoesNotFail()
    {
        $args = [
            'test',
            '--option1="value1',
            'argument1',
        ];

        $parser = new ArgumentParser;
        $results = $parser->parse($args);

        $options = $results->getOptions();
        $this->assertEquals($options['option1'], 'value1');
    }
}
