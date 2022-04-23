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

use ICanBoogie\PropertyNotDefined;
use ICanBoogie\PropertyNotWritable;
use ICanBoogie\Prototype;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\PrototypeTraitCases\AccessorCase;
use Test\ICanBoogie\PrototypeTraitCases\HasPropertyFixture;
use Test\ICanBoogie\PrototypeTraitCases\ParentCaseA;
use Test\ICanBoogie\PrototypeTraitCases\ParentCaseB;

final class PrototypeTraitTest extends TestCase
{
	public function test_accessor(): void
	{
		$code = 404;
		$previous = new \Exception;
		$a = new AccessorCase('A', 'B', 'message', $code, $previous);

		$this->assertEquals('A', $a->a);
		$this->assertEquals('B', $a->b);
		$this->assertEquals($code, $a->code);
		$this->assertSame($previous, $a->previous);
	}

	public function test_set_a(): void
	{
		$a = new AccessorCase('A', 'B', 'message');
		$this->expectException(PropertyNotWritable::class);
		$a->a = null;
	}

	public function test_set_b(): void
	{
		$a = new AccessorCase('A', 'B', 'message');
		$this->expectException(PropertyNotWritable::class);
		$a->b = null;
	}

	public function test_set_code(): void
	{
		$a = new AccessorCase('A', 'B', 'message');
		$this->expectException(PropertyNotWritable::class);
		$a->code = null;
	}

	public function test_set_previous(): void
	{
		$a = new AccessorCase('A', 'B', 'message');
		$this->expectException(PropertyNotWritable::class);
		$a->previous = null;
	}

	public function test_get_undefined(): void
	{
		$a = new AccessorCase('A', 'B', 'message');
		$p = 'undefined' . uniqid();
		$this->expectException(PropertyNotDefined::class);
		$a->$p;
	}

	public function test_parent_invoke(): void
	{
		$prototype = Prototype::from(ParentCaseA::class);
		$prototype['url'] = function($instance, $type) {

			return "/path/to/$type.html";

		};

		$a = new ParentCaseA;
		$this->assertEquals("/path/to/madonna.html", $a->url('madonna'));

		$b = new ParentCaseB;
		$this->assertEquals("/path/to/another/madonna.html", $b->url('madonna'));
	}

	public function test_should_have_property(): void
	{
		$a = new HasPropertyFixture;

		$a->prototype['get_readonly'] = function() { };
		$a->prototype['lazy_get_lazy_readonly'] = function() { };
		$a->prototype['set_writeonly'] = function() { };
		$a->prototype['lazy_set_lazy_writeonly'] = function() { };

		$this->assertTrue($a->has_property('public'));
		$this->assertTrue($a->has_property('protected'));
		$this->assertTrue($a->has_property('private'));
		$this->assertTrue($a->has_property('readonly'));
		$this->assertTrue($a->has_property('lazy_readonly'));
		$this->assertTrue($a->has_property('writeonly'));
		$this->assertTrue($a->has_property('lazy_writeonly'));
		$this->assertFalse($a->has_property('undefined'));
	}
}
