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
}

namespace ICanBoogie\GetterTraitTest;

/**
 * @property string $pseudo_uniqid
 */
class A
{
	use \ICanBoogie\GetterTrait;

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
}