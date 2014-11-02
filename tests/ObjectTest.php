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

use ICanBoogie\ObjectTest\A;
use ICanBoogie\ObjectTest\B;
use ICanBoogie\ObjectTest\ToArrayFixture;
use ICanBoogie\ObjectTest\ToArrayWithPropertyFacadeFixture;
use ICanBoogie\ObjectTest\ExportCase;

require_once 'cases.php';

class ObjectTest extends \PHPUnit_Framework_TestCase
{
	public function test_implements_to_array()
	{
		$o = new Object;
		$this->assertInstanceOf('ICanBoogie\ToArray', $o);
	}

	public function test_implements_to_array_recursive()
	{
		$o = new Object;
		$this->assertInstanceOf('ICanBoogie\ToArrayRecursive', $o);
	}

	public function test_get_prototype()
	{
		$o = new Object;
		$this->assertInstanceOf('ICanBoogie\Prototype', $o->prototype);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_set_prototype()
	{
		$o = new Object;
		$o->prototype = null;
	}

	public function test_export_empty()
	{
		$o = new Object;

		$this->assertEmpty($o->__sleep());
		$this->assertEmpty($o->to_array());
	}

	/**
	 * @dataProvider provide_test_readonly
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function test_readonly($class)
	{
		$o = new $class;
		$this->assertEquals('value', $o->property);
		$o->property = true;
	}

	public function provide_test_readonly()
	{
		return [

			[ 'ICanBoogie\ObjectTest\ReadOnlyProperty' ],
			[ 'ICanBoogie\ObjectTest\ReadOnlyPropertyExtended' ],
			[ 'ICanBoogie\ObjectTest\ReadOnlyPropertyProtected' ],
			[ 'ICanBoogie\ObjectTest\ReadOnlyPropertyProtectedExtended' ],
			[ 'ICanBoogie\ObjectTest\ReadOnlyPropertyPrivate' ],
			[ 'ICanBoogie\ObjectTest\ReadOnlyPropertyPrivateExtended' ]

		];
	}

	/**
	 * @dataProvider provide_test_writeonly
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function test_writeonly($class)
	{
		$o = new $class;
		$o->property = true;
		$a = $o->property;
	}

	public function provide_test_writeonly()
	{
		return [

			[ 'ICanBoogie\ObjectTest\WriteOnlyProperty' ],
			[ 'ICanBoogie\ObjectTest\WriteOnlyPropertyExtended' ],
			[ 'ICanBoogie\ObjectTest\WriteOnlyPropertyProtected' ],
			[ 'ICanBoogie\ObjectTest\WriteOnlyPropertyProtectedExtended' ],
			[ 'ICanBoogie\ObjectTest\WriteOnlyPropertyPrivate' ],
			[ 'ICanBoogie\ObjectTest\WriteOnlyPropertyPrivateExtended' ]

		];
	}

	public function test_set_undefined()
	{
		$o = new Object;
		$v = mt_rand();
		$o->property = $v;
		$this->assertEquals($v, $o->property);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function test_get_undefined()
	{
		$o = new Object;
		$a = $o->undefined;
	}

	public function test_to_array()
	{
		$o = new Object;
		$this->assertEmpty($o->to_array());

		$o = new ExportCase;
		$array = $o->to_array();
		$this->assertArrayHasKey('public', $array);
		$this->assertArrayHasKey('public_with_lazy_getter', $array);
		$this->assertArrayNotHasKey('protected', $array);
		$this->assertArrayNotHasKey('protected_with_getter', $array);
		$this->assertArrayNotHasKey('protected_with_setter', $array);
		$this->assertArrayNotHasKey('protected_with_getter_and_setter', $array);
		$this->assertArrayNotHasKey('protected_with_lazy_getter', $array);
		$this->assertArrayNotHasKey('private', $array);
		$this->assertArrayNotHasKey('private_with_getter', $array);
		$this->assertArrayNotHasKey('private_with_setter', $array);
		$this->assertArrayHasKey('private_with_getter_and_setter', $array);
	}

	public function test_sleep()
	{
		$o = new Object;
		$this->assertEmpty($o->__sleep());

		$o = new ExportCase;
		$properties = $o->__sleep();
		$this->assertArrayHasKey('public', $properties);
		$this->assertArrayNotHasKey('public_with_lazy_getter', $properties);
		$this->assertArrayHasKey('protected', $properties);
		$this->assertArrayHasKey('protected_with_getter', $properties);
		$this->assertArrayHasKey('protected_with_setter', $properties);
		$this->assertArrayHasKey('protected_with_getter_and_setter', $properties);
		$this->assertArrayNotHasKey('protected_with_lazy_getter', $properties);
		$this->assertArrayNotHasKey('private', $properties);
		$this->assertArrayNotHasKey('private_with_getter', $properties);
		$this->assertArrayNotHasKey('private_with_setter', $properties);
		$this->assertArrayHasKey('private_with_getter_and_setter', $properties);
		$this->assertArrayNotHasKey('private_with_lazy_getter', $properties);
	}

	public function test_to_array2()
	{
		$a = new ToArrayFixture(1, 2, 3);
		$this->assertEquals([ 'a' => 1, 'b' => 2, 'c' => 3 ], $a->to_array());
	}

	public function test_to_array_with_property_facade()
	{
		$a = new ToArrayWithPropertyFacadeFixture(1, 2, 3);
		$this->assertEquals([ 'a' => 1, 'c' => 3 ], $a->to_array());
	}

	public function test_to_array_recursive()
	{
		$a = new ToArrayFixture(1, new ToArrayFixture(11, 12, 13), [ 1, 2, 3 ]);
		$this->assertEquals([ 'a' => 1, 'b' => [ 'a' => 11, 'b' => 12, 'c' => 13 ], 'c' => [ 1, 2, 3 ] ], $a->to_array_recursive());
	}

	public function test_to_json()
	{
		$a = new ToArrayFixture(1, new ToArrayFixture(11, 12, 13), [ 1, 2, 3 ]);
		$this->assertEquals('{"a":1,"b":{"a":11,"b":12,"c":13},"c":[1,2,3]}', $a->to_json());
	}

	public function testDefaultValueForUnsetProperty()
	{
		$o = new ObjectTest\DefaultValueForUnsetProperty;
		$o->title = 'The quick brown fox';
		$this->assertEquals('the-quick-brown-fox', $o->slug);
		$this->assertArrayNotHasKey('slug', (array) $o);
		$this->assertArrayNotHasKey('slug', $o->to_array());
		$this->assertNotContains('slug', $o->__sleep());

		$o = ObjectTest\DefaultValueForUnsetProperty::from([ 'title' => 'The quick brown fox' ]);
		$this->assertEquals('the-quick-brown-fox', $o->slug);
		$this->assertArrayNotHasKey('slug', (array) $o);
		$this->assertArrayNotHasKey('slug', $o->to_array());
		$this->assertNotContains('slug', $o->__sleep());

		$o = new ObjectTest\DefaultValueForUnsetProperty;
		$o->title = 'The quick brown fox';
		$o->slug = 'brown-fox';
		$this->assertEquals('brown-fox', $o->slug);
		$this->assertArrayHasKey('slug', (array) $o);
		$this->assertArrayHasKey('slug', $o->to_array());
		$this->assertContains('slug', $o->__sleep());

		$o = ObjectTest\DefaultValueForUnsetProperty::from([ 'title' => 'The quick brown fox', 'slug' => 'brown-fox' ]);
		$this->assertEquals('brown-fox', $o->slug);
		$this->assertArrayHasKey('slug', (array) $o);
		$this->assertArrayHasKey('slug', $o->to_array());
		$this->assertContains('slug', $o->__sleep());
	}

	public function testDefaultValueForUnsetProtectedProperty()
	{
		$o = new ObjectTest\DefaultValueForUnsetProtectedProperty;
		$o->title = 'Testing';
		$this->assertEquals('testing', $o->slug);
		# slug comes from the volatile getter, the property must *not* be set.
		$this->assertArrayNotHasKey('slug', (array) $o);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testInvalidUseOfDefaultValueForUnsetProtectedProperty()
	{
		$o = new ObjectTest\DefaultValueForUnsetProtectedProperty;
		$o->slug = 'madonna';
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testInvalidProtectedPropertyGetter()
	{
		$o = new ObjectTest\InvalidProtectedPropertyGetter;
		$a = $o->value;
	}

	public function testValidProtectedPropertyGetter()
	{
		$o = new ObjectTest\ValidProtectedPropertyGetter;
		$this->assertNotNull($o->value);
	}

	public function testVirtualProperty()
	{
		$o = new ObjectTest\VirtualProperty();

		$o->minutes = 1;
		$this->assertEquals(1, $o->minutes);
		$this->assertEquals(60, $o->seconds);

		$o->seconds = 120;
		$this->assertEquals(2, $o->minutes);

		$o->minutes *= 2;
		$this->assertEquals(240, $o->seconds);
		$this->assertEquals(4, $o->minutes);

		$this->assertArrayNotHasKey('minutes', (array) $o);
		$this->assertArrayNotHasKey('minutes', $o->__sleep());
		$this->assertArrayNotHasKey('minutes', $o->to_array());
	}













	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function testGetUnsetPublicProperty()
	{
		$fixture = new A();
		$fixture->unset;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testGetUnsetProtectedProperty()
	{
		$fixture = new A();
		$fixture->unset_protected;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function testGetUndefinedProperty()
	{
		$fixture = new A();
		$fixture->madonna;
	}

	public function testProtectedProperty()
	{
		$fixture = new A();
		$fixture->c = 'c';

		$this->assertEquals('c', $fixture->c);
	}

	public function testProtectedVolatileProperty()
	{
		$fixture = new A();
		$fixture->d = 'd';

		$this->assertEquals('d', $fixture->d);
	}

	/**
	 * Because `e` is private it is not accessible by the Object class that tries to set
	 * the result of the `set_e` setter, but PHP won't complain about it and will simply leave
	 * the property untouched. This situation is not ideal because an error would be nice, so we
	 * have to note that setters for private properties MUST be _volatile_, that their job is
	 * to set the property and that we encourage using protected properties.
	 */
	public function testPrivateProperty()
	{
		$fixture = new A();
		$fixture->e = 'e';

		$this->assertEquals(null, $fixture->e);
	}

	public function testPrivateVolatileProperty()
	{
		$fixture = new A();
		$fixture->f = 'f';

		$this->assertEquals('f', $fixture->f);
	}

	public function testReadingReadOnlyProperty()
	{
		$fixture = new A();

		$this->assertEquals('readonly', $fixture->readonly);
	}

	/**
	 * Properties with getters should be removed before serialization.
	 */
	public function testSleepAndGetters()
	{
		$fixture = new A();

		$this->assertEquals('a', $fixture->a);
		$this->assertEquals('b', $fixture->b);

		$fixture = $fixture->__sleep();

		$this->assertArrayNotHasKey('a', $fixture);
		$this->assertArrayNotHasKey('b', $fixture);
	}

	/**
	 * Null properties with getters should be unset when the object wakeup so that getters can
	 * be called when the properties are accessed.
	 */
	public function __testAwakeAndGetters() // TODO-20120903: we changed that ! check: volatile_get
	{
		#
		# we use get_object_vars() otherwise assertion method would call getters
		#

		$fixture = unserialize(serialize(new A()));
		$vars = get_object_vars($fixture);

		$this->assertArrayNotHasKey('a', $vars);
		$this->assertArrayNotHasKey('b', $vars);

		$this->assertEquals('a', $fixture->a);
		$this->assertEquals('b', $fixture->b);
	}

	/**
	 * The `pseudo_uniq` property use a lazyloading getter, that is the property is created as
	 * public after the getter has been called, and the getter won't be called again until the
	 * property is accessible.
	 */
	public function testPseudoUnique()
	{
		$fixture = new A();
		$uniq = $fixture->pseudo_uniq;

		$this->assertNotEmpty($uniq);
		$this->assertEquals($uniq, $fixture->pseudo_uniq);
		unset($fixture->pseudo_uniq);
		$this->assertNotEquals($uniq, $fixture->pseudo_uniq);
	}

	public function testSetWithParent()
	{
		$b = new B();
		$b->with_parent = 3;
		$this->assertEquals(40, $b->with_parent);
	}

	public function test_prototype_is_not_exported()
	{
		$o = new Object();
		$this->assertNotContains('prototype', $o->__sleep());
		$this->assertArrayNotHasKey('prototype', $o->to_array());
	}





	/**
	 * - A string or a DateTime can be set to `created_at`
	 * - A \DateTime instance is always obtained through `created_at`.
	 * - The `created_at` property MUST be preserved by serialization.
	 */
	public function test_created_at_case()
	{
		$o = new ObjectTest\CreatedAtCase();

		$o->created_at = "2013-06-06";
		$this->assertInstanceOf('DateTime', $o->created_at);
		$this->assertEquals('2013-06-06', $o->created_at->format('Y-m-d'));

		$o->created_at = new \DateTime("2013-06-06");
		$this->assertInstanceOf('DateTime', $o->created_at);
		$this->assertEquals('2013-06-06', $o->created_at->format('Y-m-d'));

		$this->assertArrayHasKey('created_at', $o->__sleep());
		$this->assertContains("\x00" . __CLASS__ . '\CreatedAtCase' . "\x00created_at", $o->__sleep());

		$serialized = serialize($o);
		$unserialized = unserialize($serialized);

		$this->assertInstanceOf('DateTime', $unserialized->created_at);
		$this->assertEquals('2013-06-06', $unserialized->created_at->format('Y-m-d'));
	}

	/**
	 * @depends test_created_at_case
	 */
	public function test_created_at_case_extended()
	{
		$o = new ObjectTest\CreatedAtCaseExtended();
		$o->created_at = "2013-06-06";

		$this->assertInstanceOf('DateTime', $o->created_at);
		$this->assertEquals('2013-06-06', $o->created_at->format('Y-m-d'));
		$this->assertArrayHasKey('created_at', $o->__sleep());
		$this->assertContains("\x00" . __CLASS__ . '\CreatedAtCase' . "\x00created_at", $o->__sleep());

		$serialized = serialize($o);
		$unserialized = unserialize($serialized);

		$this->assertInstanceOf('DateTime', $unserialized->created_at);
		$this->assertEquals('2013-06-06', $unserialized->created_at->format('Y-m-d'));
	}
}

namespace ICanBoogie\ObjectTest;

use ICanBoogie\Object;

class A extends Object
{
	public $a;
	public $b;
	public $unset;
	protected $unset_protected;

	public function __construct()
	{
		unset($this->a);
		unset($this->b);
		unset($this->unset);
		unset($this->unset_protected);
	}

	protected function lazy_get_a()
	{
		return 'a';
	}

	protected function get_b()
	{
		return 'b';
	}

	protected $c;

	protected function lazy_set_c($value)
	{
		return $value;
	}

	protected function lazy_get_c()
	{
		return $this->c;
	}

	protected $d;

	protected function set_d($value)
	{
		$this->d = $value;
	}

	protected function get_d()
	{
		return $this->d;
	}

	private $e;

	protected function lazy_set_e($value)
	{
		return $value;
	}

	protected function lazy_get_e()
	{
		return $this->e;
	}

	protected $f;

	protected function set_f($value)
	{
		$this->f = $value;
	}

	protected function get_f()
	{
		return $this->f;
	}

	private $readonly = 'readonly';

	protected function get_readonly()
	{
		return $this->readonly;
	}

	private $writeonly;

	protected function set_writeonly($value)
	{
		$this->writeonly = $value;
	}

	protected function get_read_writeonly()
	{
		return $this->writeonly;
	}

	protected function lazy_get_pseudo_uniq()
	{
		return uniqid();
	}

	protected function lazy_set_with_parent($value)
	{
		return $value + 1;
	}
}

class B extends A
{
	protected function lazy_set_with_parent($value)
	{
		return parent::lazy_set_with_parent($value) * 10;
	}
}

class ToArrayFixture extends Object
{
	public $a;
	public $b;
	public $c;

	public function __construct($a, $b, $c)
	{
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
	}
}

class ToArrayWithPropertyFacadeFixture extends Object
{
	public $a;
	protected $b;
	private $c;

	protected function get_c()
	{
		return $this->c;
	}

	protected function set_c($value)
	{
		$this->c = $value;
	}

	public function __construct($a, $b, $c)
	{
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
	}
}
