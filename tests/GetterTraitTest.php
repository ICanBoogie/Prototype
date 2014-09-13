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

use ICanBoogie\GetterTraitTest\A;
use ICanBoogie\GetterTraitTest\OverridingGet;

class GetterTraitTest extends \PHPUnit_Framework_TestCase
{
	public function test_public()
	{
		$a = new A;
		$a->public = 1;
		$this->assertEquals(1, $a->public);
		$a->new_public = 2;
		$this->assertEquals(2, $a->new_public);
	}

	public function test_get_pseudo_uniqid()
	{
		$a = new A;
		$uniqid = $a->pseudo_uniqid;
		$this->assertNotEmpty($uniqid);
		$this->assertSame($uniqid, $a->pseudo_uniqid);
		unset($a->pseudo_uniqid);
		$this->assertNotSame($uniqid, $a->pseudo_uniqid);
	}

	public function test_get_protected()
	{
		$a = new A;
		$this->assertEquals(123, $a->protected);
	}

	public function test_get_private()
	{
		$a = new A;
		$this->assertEquals(456, $a->private);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function test_unset_property_is_not_defined()
	{
		$a = new A;
		unset($a->public);
		$a->public;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function test_not_defined()
	{
		$a = new A;
		$a->undefined;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function test_not_readable()
	{
		$a = new A;
		$a->real_private;
	}

	public function test_overriding_get()
	{
		$a = new OverridingGet;
		$this->assertEquals("The Queen of Pop", $a->madonna);
		$this->assertEquals(123, $a->protected);
		$this->assertEquals(456, $a->private);
	}
}

namespace ICanBoogie\GetterTraitTest;

use ICanBoogie\GetterTrait;

/**
 * @property string $pseudo_uniqid
 */
class A
{
	use GetterTrait;

	public $public;

	protected function lazy_get_pseudo_uniqid()
	{
		return uniqid();
	}

	protected $protected = 123;

	protected function get_protected()
	{
		return $this->protected;
	}

	private $private = 456;

	protected function get_private()
	{
		return $this->private;
	}

	private $real_private = 789;
}

class OverridingGet
{
	use GetterTrait;

	public function __get($property)
	{
		if ($property == 'madonna')
		{
			return "The Queen of Pop";
		}

		return $this->__object_get($property);
	}

	protected $protected = 123;

	protected function get_protected()
	{
		return $this->protected;
	}

	private $private = 456;

	protected function get_private()
	{
		return $this->private;
	}
}