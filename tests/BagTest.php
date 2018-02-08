<?php

namespace AppTests;

use Mockery;
use System\Support\Bag;
use PHPUnit\Framework\TestCase;
use System\Support\ArgumentCollection;

class BagTest extends TestCase
{
    public function testConstructorSetsValues()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'];
        $object = new \stdClass;

        $mock = Mockery::mock();
        $mock->shouldReceive('toArray')
                ->andReturn($array);

        $bag1 = new Bag($array);
        $bag2 = new Bag($bag1);
        $bag3 = new Bag('item');
        $bag4 = new Bag($object);

        $this->assertEquals($array, $bag1->all());
        $this->assertEquals($array, $bag2->all());
        $this->assertEquals(['item'], $bag3->all());
        $this->assertEquals([], $bag4->all());
    }

    public function testContainsReturnsCorrectResult()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'];

        $bag = new Bag($array);

        $this->assertTrue($bag->contains('333'));
        $this->assertFalse($bag->contains('444'));
    }

    public function testDataCanBeAppendedToBag()
    {
        $array = [
            1234,
            2341,
            3412,
            4123,
        ];

        $expected = [
            1234,
            2341,
            3412,
            4123,
            1111,
        ];

        $bag = new Bag($array);

        $bag->append(1111);

        $this->assertEquals($expected, $bag->toArray());
    }

    public function testHasReturnsCorrectResult()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'];

        $bag = new Bag($array);

        $this->assertTrue($bag->has('three'));
        $this->assertFalse($bag->has('four'));
    }

    public function testGetAndSetMethods()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'];

        $bag = new Bag($array);

        $this->assertEquals('2', $bag->get('two'));
        $this->assertFalse($bag->get('six'));

        $bag->set('six', '666');

        $this->assertEquals('666', $bag->get('six'));
    }

    public function testMagicGetAndSetMethods()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'];

        $bag = new Bag($array);

        $this->assertEquals('2', $bag->two);
        $this->assertFalse($bag->six);

        $bag->six = '666';

        $this->assertEquals('666', $bag->six);
    }

    public function testUniqueStripsDuplicates()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four', 'forty' => 'four'];

        $bag = new Bag($array);
        $uniqueBag = $bag->unique();

        $this->assertInstanceOf(Bag::class, $uniqueBag);
        $this->assertEquals(['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'], $uniqueBag->all());
    }

    public function testUniqueWithKeyStripsDuplicates()
    {
        $array = [
            'one' => ['id' => 1, 'value' => 'lorem'],
            'two' => ['id' => 2, 'value' => 'ipsum'],
            'three' => ['id' => 3, 'value' => 'lorem'],
            'four' => ['id' => 4, 'value' => 'amet'],
        ];

        $expected = [
            'one' => ['id' => 1, 'value' => 'lorem'],
            'two' => ['id' => 2, 'value' => 'ipsum'],
            'four' => ['id' => 4, 'value' => 'amet'],
        ];

        $bag = new Bag($array);
        $uniqueBag = $bag->unique('value');
        $uniqueBag2 = $bag->unique('id');

        $this->assertInstanceOf(Bag::class, $uniqueBag);
        $this->assertEquals($expected, $uniqueBag->all());
        $this->assertEquals($array, $uniqueBag2->all());
    }

    public function testMergesPerformsWithBagAndArray()
    {
        $array = ['one' => 1, 'two' => '2', 'three' => '333', '4' => 'four'];
        $array2 = ['merged2' => 'true'];

        $bag = new Bag($array);
        $bag2 = new Bag(['merged' => 'yes']);

        $mergedBag = $bag->merge($bag2);
        $mergedBag2 = $bag->merge($array2);

        $this->assertInstanceOf(Bag::class, $mergedBag);
        $this->assertInstanceOf(Bag::class, $mergedBag);
        $this->assertEquals(['one' => 1, 'two' => '2', 'three' => '333', '0' => 'four', 'merged' => 'yes'], $mergedBag->all());
        $this->assertEquals(['one' => 1, 'two' => '2', 'three' => '333', '0' => 'four', 'merged2' => 'true'], $mergedBag2->all());
    }

    public function testSumOnSingleDimensionalNumericalBasedArray()
    {
        $array = [
            1,
            2,
            3,
            4,
        ];

        $bag = new Bag($array);

        $this->assertEquals(10, $bag->sum());
    }

    public function testSumOnAssociativeArrayWithKey()
    {
        $array = [
            ['id' => 1, 'value' => 1234],
            ['id' => 2, 'value' => 2341],
            ['id' => 3, 'value' => 3412],
            ['id' => 4, 'value' => 4123],
        ];

        $bag = new Bag($array);

        $this->assertEquals(11110, $bag->sum('value'));
    }

    public function testSumOnArrayOfObjectsWithKey()
    {
        $array = [
            new Bag(['id' => 1, 'value' => 1234]),
            new Bag(['id' => 2, 'value' => 2341]),
            new Bag(['id' => 3, 'value' => 3412]),
            new Bag(['id' => 4, 'value' => 4123]),
        ];

        $bag = new Bag($array);

        $this->assertEquals(11110, $bag->sum('value'));
    }
}
