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

require_once 'ObjectClasses.php';

class ObjectTest extends \PHPUnit_Framework_TestCase
{
	public function testSetGet()
	{
		$o = new Object;
		$o->a = __FUNCTION__;
		$this->assertEquals(__FUNCTION__, $o->a);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotDefined
	 */
	public function testInvalidGet()
	{
		$o = new Object;
		$a = $o->undefined;
	}

	public function testReadOnlyProperty()
	{
		$o = new ObjectTest\ReadOnlyProperty;
		$this->assertEquals('value', $o->value);
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testInvalidUseOfReadOnlyProperty()
	{
		$o = new ObjectTest\ReadOnlyProperty;
		$o->value = 'value';
	}

	public function testWriteOnlyProperty()
	{
		$o = new ObjectTest\WriteOnlyProperty;
		$o->value = 'value';
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testInvalidUseOfWriteOnlyProperty()
	{
		$o = new ObjectTest\WriteOnlyProperty;
		$a = $o->value;
	}

	public function testDefaultValueForUnsetProperty()
	{
		$o = new ObjectTest\DefaultValueForUnsetProperty;
		$o->title = 'The quick brown fox';
		$this->assertEquals('the-quick-brown-fox', $o->slug);
		$this->assertArrayNotHasKey('slug', (array) $o);
		$this->assertArrayNotHasKey('slug', $o->to_array());
		$this->assertNotContains('slug', $o->__sleep());

		$o = ObjectTest\DefaultValueForUnsetProperty::from(array('title' => 'The quick brown fox'));
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

		$o = ObjectTest\DefaultValueForUnsetProperty::from(array('title' => 'The quick brown fox', 'slug' => 'brown-fox'));
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
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testObjectWithAVolatileGetterButNoCorrespondingProperty()
	{
		$a = new ObjectTest\ObjectWithAVolatileGetterButNoCorrespondingProperty;
		$this->assertEquals('value', $a->value);
		$a->value = 'value';
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testObjectWithAVolatileGetterAndACorrespondingPrivateProperty()
	{
		$a = new ObjectTest\ObjectWithAVolatileGetterAndACorrespondingPrivateProperty;
		$this->assertEquals('value', $a->value);
		$a->value = 'value';
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testObjectWithAVolatileGetterAndACorrespondingProtectedProperty()
	{
		$a = new ObjectTest\ObjectWithAVolatileGetterAndACorrespondingProtectedProperty;
		$this->assertEquals('value', $a->value);
		$a->value = 'value';
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testObjectWithAVolatileSetterButNoCorrespondingProperty()
	{
		$a = new ObjectTest\ObjectWithAVolatileSetterButNoCorrespondingProperty;
		$a->value = 'value';
		$b = $a->value;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testObjectWithAVolatileSetterAndACorrespondingPrivateProperty()
	{
		$a = new ObjectTest\ObjectWithAVolatileSetterAndACorrespondingPrivateProperty;
		$a->value = 'value';
		$b = $a->value;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testObjectWithAVolatileSetterAndACorrespondingProtectedProperty()
	{
		$a = new ObjectTest\ObjectWithAVolatileSetterAndACorrespondingProtectedProperty;
		$a->value = 'value';
		$b = $a->value;
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testExtendedObjectWithAVolatileSetterAndACorrespondingProtectedPropertyGet()
	{
		$a = new ObjectTest\ExtendedObjectWithAVolatileSetterAndACorrespondingProtectedProperty;
		$this->assertEquals('construct', $a->value);
	}

	public function testExtendedObjectWithAVolatileSetterAndACorrespondingProtectedPropertySet()
	{
		$a = new ObjectTest\ExtendedObjectWithAVolatileSetterAndACorrespondingProtectedProperty;
		$a->value = 'value';
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
	 * @expectedException ICanBoogie\PropertyNotWritable
	 */
	public function testWritingReadOnlyProperty()
	{
		$fixture = new A();
		$fixture->readonly = 'readandwrite';
	}

	/**
	 * @expectedException ICanBoogie\PropertyNotReadable
	 */
	public function testReadingWriteOnlyProperty()
	{
		$fixture = new A();

		$fixture->writeonly = 'writeonly';

		$this->assertEquals('writeonly', $fixture->writeonly);
	}

	public function testWritingWriteOnlyProperty()
	{
		$fixture = new A();

		$fixture->writeonly = 'writeonly';

		$this->assertEquals('writeonly', $fixture->read_writeonly);
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

	public function test_to_array()
	{
		$a = new ToArrayFixture(1, 2, 3);
		$this->assertEquals(array('a' => 1, 'b' => 2, 'c' => 3), $a->to_array());
	}

	public function test_to_array_with_property_facade()
	{
		$a = new ToArrayWithPropertyFacadeFixture(1, 2, 3);
		$this->assertEquals(array('a' => 1, 'c' => 3), $a->to_array());
	}

	public function test_to_array_recursive()
	{
		$a = new ToArrayFixture(1, new ToArrayFixture(11, 12, 13), array(1, 2, 3));
		$this->assertEquals(array('a' => 1, 'b' => array('a' => 11, 'b' => 12, 'c' => 13), 'c' => array(1, 2, 3)), $a->to_array_recursive());
	}

	public function test_to_json()
	{
		$a = new ToArrayFixture(1, new ToArrayFixture(11, 12, 13), array(1, 2, 3));
		$this->assertEquals('{"a":1,"b":{"a":11,"b":12,"c":13},"c":[1,2,3]}', $a->to_json());
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

/*
 * One should be able to get the `value` property, but setting it should throw a
 * `PropertyNotWritable` exception.
 */
class ObjectWithAVolatileGetterButNoCorrespondingProperty extends Object
{
	protected function volatile_get_value()
	{
		return 'value';
	}
}

/*
 * One should be able to get the `value` property, but setting it should throw a
 * `PropertyNotWritable` exception.
 */
class ObjectWithAVolatileGetterAndACorrespondingPrivateProperty extends Object
{
	private $value;

	protected function volatile_get_value()
	{
		return 'value';
	}
}

/*
 * One should be able to get the `value` property, but setting it should throw a
 * `PropertyNotWritable` exception. The property is accessible within the class.
 */
class ObjectWithAVolatileGetterAndACorrespondingProtectedProperty extends Object
{
	protected $value;

	protected function volatile_get_value()
	{
		return 'value';
	}
}

class ExtendedObjectWithAVolatileGetterAndACorrespondingProtectedProperty extends ObjectWithAVolatileGetterAndACorrespondingProtectedProperty
{
	public function __construct()
	{
		$this->value = 'construct';
	}
}

/*
 * One should be able to set the `value` property, but getting it should throw a
 * `PropertyNotReadable` exception.
 */
class ObjectWithAVolatileSetterButNoCorrespondingProperty extends Object
{
	protected function volatile_set_value($value)
	{

	}
}

/*
 * One should be able to set the `value` property, but setting it should throw a
 * `PropertyNotReadable` exception.
 */
class ObjectWithAVolatileSetterAndACorrespondingPrivateProperty extends Object
{
	private $value;

	protected function volatile_set_value($value)
	{
		$this->value = $value;
	}
}

/*
 * One should be able to set the `value` property, but setting it should throw a
 * `PropertyNotReadable` exception. The property is accessible within the class.
 */
class ObjectWithAVolatileSetterAndACorrespondingProtectedProperty extends Object
{
	protected $value;

	protected function volatile_set_value($value)
	{
		$this->value = $value;
	}
}

class ExtendedObjectWithAVolatileSetterAndACorrespondingProtectedProperty extends ObjectWithAVolatileSetterAndACorrespondingProtectedProperty
{
	public function __construct()
	{
		$this->value = 'construct';
	}
}























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

	protected function get_a()
	{
		return 'a';
	}

	protected function volatile_get_b()
	{
		return 'b';
	}

	protected $c;

	protected function set_c($value)
	{
		return $value;
	}

	protected function get_c()
	{
		return $this->c;
	}

	protected $d;

	protected function volatile_set_d($value)
	{
		$this->d = $value;
	}

	protected function volatile_get_d()
	{
		return $this->d;
	}

	private $e;

	protected function set_e($value)
	{
		return $value;
	}

	protected function get_e()
	{
		return $this->e;
	}

	protected $f;

	protected function volatile_set_f($value)
	{
		$this->f = $value;
	}

	protected function volatile_get_f()
	{
		return $this->f;
	}

	private $readonly = 'readonly';

	protected function volatile_get_readonly()
	{
		return $this->readonly;
	}

	private $writeonly;

	protected function volatile_set_writeonly($value)
	{
		$this->writeonly = $value;
	}

	protected function volatile_get_read_writeonly()
	{
		return $this->writeonly;
	}

	protected function get_pseudo_uniq()
	{
		return uniqid();
	}

	protected function set_with_parent($value)
	{
		return $value + 1;
	}
}

class B extends A
{
	protected function set_with_parent($value)
	{
		return parent::set_with_parent($value) * 10;
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

	protected function volatile_get_c()
	{
		return $this->c;
	}

	protected function volatile_set_c($value)
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