<?php

namespace AppTests;

date_default_timezone_set('Europe/London');

use System\Mail\VariableParser;
use PHPUnit_Framework_TestCase;

class VariableParserTest extends PHPUnit_Framework_TestCase
{
	public function testParserReplacesSimpleVariable()
	{
		$variables = [
			'unit' => 'PHPUnit',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This test should read {{unit}}');

		$this->assertEquals($string, 'This test should read PHPUnit');
	}

	public function testParserReplacesMultiple()
	{
		$variables = [
			'unit' => 'PHPUnit',
			'test' => 'string',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{test}} should read {{unit}}');

		$this->assertEquals($string, 'This string should read PHPUnit');
	}

	public function testParserReplacesMissingWithNull()
	{
		$variables = [
			'unit' => 'PHPUnit',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{test}} should read {{unit}}');

		$this->assertEquals($string, 'This  should read PHPUnit');
	}

	public function testParserHandlesSpacesInVariables()
	{
		$variables = [
			'unit' => 'PHPUnit',
			'test' => 'string',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{test    }} should read {{ unit}}');

		$this->assertEquals($string, 'This string should read PHPUnit');
	}

	public function testParserIgnoresMalformedVariables()
	{
		$variables = [
			'unit' => 'PHPUnit',
			'test' => 'string',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{test} } should read {{ un}it }} {{unit}}');

		$this->assertEquals($string, 'This {{test} } should read {{ un}it }} PHPUnit');
	}

	public function testParserHandlesDateMacro()
	{
		$variables = [];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This test should display the date {{date.now}}');

		$this->assertEquals($string, 'This test should display the date '.date('d/m/Y H:i'));
	}

	public function testParserHandlesDateMacroFormat()
	{
		$variables = [];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This test should display the date {{date.format|d-m-Y-H-i}}');

		$this->assertEquals($string, 'This test should display the date '.date('d-m-Y-H-i'));
	}

	public function testParserHandlesEscapedVariables()
	{
		$variables = [
			'test' => 'string',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{test}} should display curly @{{brackets}}');

		$this->assertEquals($string, 'This string should display curly {{brackets}}');
	}
	
	public function testParserHandlesDotNotationVariable()
	{
		$variables = [
			'address' => [
				'name' => 'PHPUnit',
				'email' => 'php@unit.com',
			],
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This test should be addressed to {{address.name}} at {{address.email}}');

		$this->assertEquals($string, 'This test should be addressed to PHPUnit at php@unit.com');
	}

	public function testParserCanHandleObjects()
	{
		$address = new \StdClass;
		$address->name = 'PHPUnit';
		$address->email = 'php@unit.com';

		$variables = [
			'address' => $address,
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This test should be addressed to {{address.name}} at {{address.email}}');

		$this->assertEquals($string, 'This test should be addressed to PHPUnit at php@unit.com');
	}

	public function testParserCanHandleObjectHost()
	{
		$variables = new \StdClass;

		$variables->address = new \StdClass;
		$variables->address->name = 'PHPUnit';
		$variables->address->email = 'php@unit.com';

		$parser = new VariableParser($variables);
		$string = $parser->parse('This test should be addressed to {{address.name}} at {{address.email}}');

		$this->assertEquals($string, 'This test should be addressed to PHPUnit at php@unit.com');
	}

	public function testParserEscapesHtml()
	{
		$variables = [
			'html' => '<p>html</p>',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{html}} should be encoded');

		$this->assertEquals($string, 'This &lt;p&gt;html&lt;/p&gt; should be encoded');
	}

	public function testParserPrintsUnescapedHtml()
	{
		$variables = [
			'html' => '<p>html</p>',
		];

		$parser = new VariableParser($variables);
		$string = $parser->parse('This {{{html}}} should be encoded');

		$this->assertEquals($string, 'This <p>html</p> should be encoded');
	}

	public function testParserFailsOnStringArgument()
	{
		$this->expectException(\InvalidArgumentException::class);

		$variables = 'variable';

		$parser = new VariableParser($variables);
	}
}
