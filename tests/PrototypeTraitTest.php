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

use ICanBoogie\PrototypeTraitTest\A;

class PrototypeTraitTest extends \PHPUnit_Framework_TestCase
{
	public function test_get()
	{
		$code = 404;
		$previous = new \Exception("previous");
		$a = new A('A', 'B', 'message', $code, $previous);

		$this->assertEquals('A', $a->a);
		$this->assertEquals('B', $a->b);
		$this->assertEquals($code, $a->code);
		$this->assertSame($previous, $a->previous);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_a()
	{
		$a = new A('A', 'B', 'message');
		$a->a = null;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_b()
	{
		$a = new A('A', 'B', 'message');
		$a->b = null;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_code()
	{
		$a = new A('A', 'B', 'message');
		$a->code = null;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_previous()
	{
		$a = new A('A', 'B', 'message');
		$a->previous = null;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function test_get_undefined()
	{
		$a = new A('A', 'B', 'message');
		$undefined = $a->undefined;
	}
}

namespace ICanBoogie\PrototypeTraitTest;

use ICanBoogie\PrototypeTrait;

class A extends \Exception
{
	use PrototypeTrait;

	private $a;
	private $b;

	public function __construct($a, $b, $message, $code=500, \Exception $previous=null)
	{
		$this->a = $a;
		$this->b = $b;

		parent::__construct($message, $code, $previous);
	}

	protected function get_a()
	{
		return $this->a;
	}

	protected function get_b()
	{
		return $this->b;
	}

	protected function get_code()
	{
		return $this->getCode();
	}

	protected function get_previous()
	{
		return $this->getPrevious();
	}
}