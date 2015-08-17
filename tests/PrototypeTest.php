<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

use ICanBoogie\PrototypeTest\A;
use ICanBoogie\PrototypeTest\B;
use ICanBoogie\PrototypeTest\Cat;
use ICanBoogie\PrototypeTest\FierceCat;
use ICanBoogie\PrototypeTest\NormalCat;

class PrototypeTest extends \PHPUnit_Framework_TestCase
{
	private $a;
	private $b;

	public function setUp()
	{
		$this->a = $a = new A;
		$this->b = $b = new B;

		$a->prototype['set_minutes'] = function(A $self, $minutes) {

			$self->seconds = $minutes * 60;
		};

		$a->prototype['get_minutes'] = function(A $self, $minutes) {

			return $self->seconds / 60;
		};
	}

	public function testPrototype()
	{
		$this->assertInstanceOf(Prototype::class, $this->a->prototype);
	}

	public function testMethod()
	{
		$a = $this->a;

		$a->prototype['format'] = function(A $self, $format) {

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

		$b->prototype['set_hours'] = function(B $self, $hours) {

			$self->seconds = $hours * 3600;
		};

		$b->prototype['get_hours'] = function(B $self, $hours) {

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

		$cat->prototype['meow'] = function($target) {

			return 'Meow';

		};

		$fierce_cat->prototype['meow'] = function($target) {

			return 'MEOOOW !';

		};

		$this->assertEquals('Meow', $cat->meow());
		$this->assertEquals('Meow', $normal_cat->meow());
		$this->assertEquals('MEOOOW !', $fierce_cat->meow());
		$this->assertEquals('MEOOOW !', $other_fierce_cat->meow());
	}

	/**
	 * @expectedException \ICanBoogie\Prototype\MethodNotDefined
	 */
	public function testMethodNotDefined()
	{
		$this->a->undefined_method();
	}
}

namespace ICanBoogie\PrototypeTest;

use ICanBoogie\Object;

class A extends Object {}
class B extends A {}

class Cat extends Object {}
class NormalCat extends Cat {}
class FierceCat extends Cat {}
