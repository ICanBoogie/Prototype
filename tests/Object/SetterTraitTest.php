<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Object;

use ICanBoogie\Object\SetterTraitObject\A;
use ICanBoogie\Object\SetterTraitObject\B;

class SetterTraitObject extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_set
	 */
	public function test_set($class, $property, $value, $state)
	{
		$class = __CLASS__ . '\\' . $class;
		$a = new $class;
		$a->$property = $value;
		$this->assertSame($state, $a->get_all());
	}

	public function provide_test_set()
	{
		return [

			[ 'A', 'public',          2, [ 'public' => 2, 'protected' => null, 'private' => null ] ],
			[ 'A', 'unset_public',    2, [ 'public' => null, 'unset_public' => 3, 'protected' => null, 'private' => null ] ],
			[ 'A', 'protected',       2, [ 'public' => null, 'protected' => 3, 'private' => null ] ],
			[ 'A', 'unset_protected', 2, [ 'public' => null, 'protected' => null, 'unset_protected' => 3, 'private' => null ] ],
			[ 'A', 'private',         2, [ 'public' => null, 'protected' => null, 'private' => 3 ] ],
			[ 'A', 'unset_private',   2, [ 'public' => null, 'protected' => null, 'private' => null, 'unset_private' => 3 ] ],
			[ 'A', 'lazy',            2, [ 'public' => null, 'protected' => null, 'private' => null, 'lazy' => 3 ] ],
			[ 'A', 'additional',      2, [ 'public' => null, 'protected' => null, 'private' => null, 'additional' => 2 ] ],

			[ 'B', 'public',          2, [ 'public' => 2, 'protected' => null, 'private' => null ] ],
			[ 'B', 'unset_public',    2, [ 'public' => null, 'unset_public' => 3, 'protected' => null, 'private' => null ] ],
			[ 'B', 'protected',       2, [ 'public' => null, 'protected' => 3, 'private' => null ] ],
			[ 'B', 'unset_protected', 2, [ 'public' => null, 'protected' => null, 'unset_protected' => 3, 'private' => null ] ],
			[ 'B', 'private',         2, [ 'public' => null, 'protected' => null, 'private' => 3 ] ],
			[ 'B', 'unset_private',   2, [ 'public' => null, 'protected' => null, 'private' => null, 'unset_private' => 3 ] ],
			[ 'B', 'lazy',            2, [ 'public' => null, 'protected' => null, 'private' => null, 'lazy' => 3 ] ],
			[ 'B', 'additional',      2, [ 'public' => null, 'protected' => null, 'private' => null, 'additional' => 2 ] ]
		];
	}

	/**
	 * @dataProvider provide_test_not_writable
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_not_writable($class, $property)
	{
		$class = __CLASS__ . '\\' . $class;
		$a = new $class;
		$a->$property = null;
	}

	public function provide_test_not_writable()
	{
		return [

			[ 'A', 'not_writable_protected' ],
			[ 'A', 'not_writable_private' ],
			[ 'B', 'not_writable_protected' ],
			[ 'B', 'not_writable_private' ]

		];
	}
}

namespace ICanBoogie\Object\SetterTraitObject;

class A
{
	use \ICanBoogie\Object\SetterTrait;

	public $public;

	/*
	 * The method must not be called
	 */
	protected function set_public($value)
	{
		$this->public = $value + 1;
	}

	public $unset_public;

	protected function set_unset_public($value)
	{
		$this->unset_public = $value + 1;
	}

	protected $protected;

	protected function set_protected($value)
	{
		$this->protected = $value + 1;
	}

	protected $unset_protected;

	protected function set_unset_protected($value)
	{
		$this->unset_protected = $value + 1;
	}

	private $private;

	protected function set_private($value)
	{
		$this->private = $value + 1;
	}

	protected $unset_private;

	private function set_unset_private($value)
	{
		$this->unset_private = $value + 1;
	}

	protected function lazy_set_lazy($value)
	{
		return $value + 1;
	}

	protected $not_writable_protected;
	private $not_writable_private;

	public function __construct()
	{
		unset($this->unset_public);
		unset($this->unset_protected);
		unset($this->unset_private);
	}

	public function get_all()
	{
		return array_diff_key(get_object_vars($this), [

			'not_writable_protected' => null,
			'not_writable_private' => null

		]);
	}
}

class B extends A
{

}