<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie;

use ICanBoogie\Prototype;
use ICanBoogie\Prototype\Config;
use ICanBoogie\Prototype\MethodNotDefined;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Prototype\Cat;
use Test\ICanBoogie\Prototype\FierceCat;
use Test\ICanBoogie\Prototype\NormalCat;
use Test\ICanBoogie\PrototypedCases\SampleA;
use Test\ICanBoogie\PrototypedCases\SampleB;
use Test\ICanBoogie\PrototypeTraitCases\BindCase;
use Test\ICanBoogie\PrototypeTraitCases\UnsetCase;

final class PrototypeTest extends TestCase
{
	private $a;
	private $b;

	protected function setUp(): void
	{
		$this->a = $a = new SampleA;
		$this->b = $b = new SampleB;

		$a->prototype['set_minutes'] = function (SampleA $self, $minutes) {
			$self->seconds = $minutes * 60;
		};

		$a->prototype['get_minutes'] = function (SampleA $self) {
			return $self->seconds / 60;
		};
	}

	public function testBind()
	{
		$method1 = 'm' . uniqid();
		$method2 = 'm' . uniqid();
		$value1 = uniqid();
		$value2 = uniqid();
		$value3 = uniqid();

		$callback1 = function (BindCase $case) use ($value1) {
			return $value1;
		};

		$callback2 = function (BindCase $case) use ($value2) {
			return $value2;
		};

		$callback3 = function (BindCase $case) use ($value3) {
			return $value3;
		};

		Prototype::bind(new Config([ BindCase::class => [ $method1 => $callback1 ] ]));
		Prototype::bind(new Config([ BindCase::class => [ $method2 => $callback2 ] ]));
		Prototype::bind(new Config([]));

		$case = new BindCase();

		$this->assertSame($value1, $case->$method1());
		$this->assertSame($value2, $case->$method2());

		Prototype::bind(new Config([ BindCase::class => [ $method1 => $callback3 ] ]));

		$methods = iterator_to_array(Prototype::from(BindCase::class));

		$this->assertSame([

			$method1 => $callback3,
			$method2 => $callback2,

		], $methods);

		$this->assertSame($value3, $case->$method1());
		$this->assertSame($value2, $case->$method2());
	}

	public function testPrototype()
	{
		$this->assertInstanceOf(Prototype::class, $this->a->prototype);
	}

	public function testMethod()
	{
		$a = $this->a;

		$a->prototype['format'] = function (SampleA $self, $format) {
			return date($format, $self->seconds);
		};

		$a->seconds = time();
		$format = 'H:i:s';

		$this->assertEquals(date($format, $a->seconds), $a->format($format));
	}

	public function testSetterGetter()
	{
		$a = $this->a;

		$a->minutes = 2;

		$this->assertEquals(120, $a->seconds);
		$this->assertEquals(2, $a->minutes);
	}

	public function testPrototypeChain()
	{
		$b = $this->b;

		$b->prototype['set_hours'] = function (SampleB $self, $hours) {
			$self->seconds = $hours * 3600;
		};

		$b->prototype['get_hours'] = function (SampleB $self, $hours) {
			return $self->seconds / 3600;
		};

		$b->minutes = 4;

		$this->assertEquals(240, $b->seconds);
		$this->assertEquals(4, $b->minutes);

		$b->hours = 1;

		$this->assertEquals(3600, $b->seconds);
		$this->assertEquals(1, $b->hours);

		# hours should be a simple property for A

		$a = $this->a;

		$a->seconds = 0;
		$a->hours = 1;

		$this->assertEquals(0, $a->seconds);
		$this->assertEquals(1, $a->hours);
	}

	public function testPrototypeChainWithCats()
	{
		$cat = new Cat;
		$normal_cat = new NormalCat;
		$fierce_cat = new FierceCat;
		$other_fierce_cat = new FierceCat;

		$cat->prototype['meow'] = function ($target) {
			return 'Meow';
		};

		$fierce_cat->prototype['meow'] = function ($target) {
			return 'MEOOOW !';
		};

		$this->assertEquals('Meow', $cat->meow());
		$this->assertEquals('Meow', $normal_cat->meow());
		$this->assertEquals('MEOOOW !', $fierce_cat->meow());
		$this->assertEquals('MEOOOW !', $other_fierce_cat->meow());
	}

	public function testMethodNotDefined()
	{
		$this->expectException(MethodNotDefined::class);
		$this->a->undefined_method();
	}

	public function testUnset()
	{
		$value = uniqid();
		$method = 'm' . uniqid();

		$prototype = Prototype::from(UnsetCase::class);
		$prototype[$method] = function () use ($value) {
			return $value;
		};

		$case = new UnsetCase();

		$this->assertSame($value, $case->$method());

		unset($prototype[$method]);

		$this->expectException(MethodNotDefined::class);

		$case->$method();
	}
}
