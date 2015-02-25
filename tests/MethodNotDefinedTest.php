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

use ICanBoogie\Prototype\MethodNotDefinedTest\A;

class MethodNotDefinedTest extends \PHPUnit_Framework_TestCase
{
	public function test_instance()
	{
		$method = 'method' . uniqid();
		$object = $this
			->getMockBuilder('ICanBoogie\Object')
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
			$this->assertInstanceOf('ICanBoogie\Prototype\MethodNotDefined', $e);
			$this->assertEquals('undefined_method', $e->method);
			$this->assertEquals(get_class($a), $e->class);
		}
	}
}


namespace ICanBoogie\Prototype\MethodNotDefinedTest;

use ICanBoogie\PrototypeTrait;

class A
{
	use PrototypeTrait;

	public function public_method()
	{
		return __FUNCTION__;
	}

	protected function protected_method()
	{
		return __FUNCTION__;
	}

	private function private_method()
	{
		return __FUNCTION__;
	}
}
