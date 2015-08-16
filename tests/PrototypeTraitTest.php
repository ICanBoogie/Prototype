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

use ICanBoogie\Prototype\PrototypeTraitTest\HasPropertyFixture;
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
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_a()
	{
		$a = new A('A', 'B', 'message');
		$a->a = null;
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_b()
	{
		$a = new A('A', 'B', 'message');
		$a->b = null;
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_code()
	{
		$a = new A('A', 'B', 'message');
		$a->code = null;
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_previous()
	{
		$a = new A('A', 'B', 'message');
		$a->previous = null;
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotDefined
	 */
	public function test_get_undefined()
	{
		$a = new A('A', 'B', 'message');
		$p = 'undefined' . uniqid();
		$a->$p;
	}

	public function test_parent_invoke()
	{
		$prototype = Prototype::from('ICanBoogie\PrototypeTraitTest\ParentCaseA');
		$prototype['url'] = function($instance, $type)
		{
			return "/path/to/$type.html";
		};

		$a = new \ICanBoogie\PrototypeTraitTest\ParentCaseA;
		$this->assertEquals("/path/to/madonna.html", $a->url('madonna'));

		$b = new \ICanBoogie\PrototypeTraitTest\ParentCaseB;
		$this->assertEquals("/path/to/another/madonna.html", $b->url('madonna'));
	}

	public function test_should_have_property()
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

class ParentCaseA
{
	use PrototypeTrait;
}

class ParentCaseB extends ParentCaseA
{
	public function url($type)
	{
		return parent::url("another/$type");
	}
}
