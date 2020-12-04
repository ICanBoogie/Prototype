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
use ICanBoogie\Prototyped;
use PHPUnit\Framework\TestCase;

class MethodNotDefinedTest extends TestCase
{
	public function test_instance()
	{
		$method = 'method' . uniqid();
		$object = $this
			->getMockBuilder(Prototyped::class)
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

	public function test_invoke_protected_method()
	{
		$a = new A;
		$this->expectException(MethodOutOfScope::class);
		$a->protected_method();
	}

	public function test_invoke_private_method()
	{
		$a = new A;
		$this->expectException(MethodOutOfScope::class);
		$a->private_method();
	}

	public function test_undefined_method()
	{
		$a = new A;
		$m = 'method' . uniqid();

		try
		{
			$a->$m();
		}
		catch (MethodNotDefined $e)
		{
			$this->assertEquals($m, $e->method);
			$this->assertEquals(get_class($a), $e->class);

			return;
		}

		$this->fail("Expected MethodNotDefined");
	}
}
