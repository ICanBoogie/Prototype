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

use ICanBoogie\Prototype\MethodOutOfScopeTest\A;

class MethodOutOfScopeTest extends \PHPUnit_Framework_TestCase
{
	public function test_invoke_public_method()
	{
		$a = new A;
		$this->assertEquals('public_method', $a->public_method());
	}

	public function test_invoke_protected_method()
	{
		$a = new A;

		try
		{
			$a->protected_method();

			$this->fail('Excepted MethodOutOfScope exception.');
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Prototype\MethodOutOfScope', $e);
			$this->assertEquals('protected_method', $e->method);
			$this->assertSame($a, $e->instance);
		}
	}

	public function test_invoke_private_method()
	{
		$a = new A;

		try
		{
			$a->private_method();

			$this->fail('Excepted MethodOutOfScope exception.');
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Prototype\MethodOutOfScope', $e);
			$this->assertEquals('private_method', $e->method);
			$this->assertSame($a, $e->instance);
		}
	}
}

namespace ICanBoogie\Prototype\MethodOutOfScopeTest;

class A
{
	use \ICanBoogie\PrototypeTrait;

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
