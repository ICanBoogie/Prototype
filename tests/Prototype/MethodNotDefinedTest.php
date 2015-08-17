<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Prototype;

use ICanBoogie\Object;
use ICanBoogie\Prototype\MethodNotDefinedTest\A;

class MethodNotDefinedTest extends \PHPUnit_Framework_TestCase
{
	public function test_instance()
	{
		$method = 'method' . uniqid();
		$object = $this
			->getMockBuilder(Object::class)
			->disableOriginalConstructor()
			->getMock();

		$instance = new MethodNotDefined($method, $object);

		$this->assertSame($method, $instance->method);
		$this->assertSame(get_class($object), $instance->class);
		$this->assertSame($object, $instance->instance);
	}

	public function test_invoke_public_method()
	{
		$a = new A;
		$this->assertEquals('public_method', $a->public_method());
	}

	/**
	 * @expectedException \ICanBoogie\Prototype\MethodOutOfScope
	 */
	public function test_invoke_protected_method()
	{
		$a = new A;
		$a->protected_method();
	}

	/**
	 * @expectedException \ICanBoogie\Prototype\MethodOutOfScope
	 */
	public function test_invoke_private_method()
	{
		$a = new A;
		$a->private_method();
	}

	public function test_undefined_method()
	{
		$a = new A;

		try
		{
			$a->undefined_method();
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf(MethodNotDefined::class, $e);
			$this->assertEquals('undefined_method', $e->method);
			$this->assertEquals(get_class($a), $e->class);
		}
	}
}
