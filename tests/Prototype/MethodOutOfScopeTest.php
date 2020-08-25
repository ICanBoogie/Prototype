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

use Exception;
use ICanBoogie\Prototype\MethodOutOfScopeTest\A;
use PHPUnit\Framework\TestCase;

class MethodOutOfScopeTest extends TestCase
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
		catch (Exception $e)
		{
			$this->assertInstanceOf(MethodOutOfScope::class, $e);
			/* @var $e MethodOutOfScope */
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
		catch (Exception $e)
		{
			$this->assertInstanceOf(MethodOutOfScope::class, $e);
			/* @var $e MethodOutOfScope */
			$this->assertEquals('private_method', $e->method);
			$this->assertSame($a, $e->instance);
		}
	}
}
