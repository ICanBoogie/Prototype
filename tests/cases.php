<?php

namespace ICanBoogie\ObjectTest;

use ICanBoogie\Object;

/**
 * The `value` property CAN be read but MUST NOT be set.
 */
class ReadOnlyProperty extends Object
{
	protected function get_property()
	{
		return 'value';
	}
}

class ReadOnlyPropertyExtended extends ReadOnlyProperty
{
}

/**
 * The `value` property CAN be read but MUST NOT be set.
 */
class ReadOnlyPropertyProtected extends Object
{
	protected $property = 'value';

	protected function get_property()
	{
		return $this->property;
	}
}

class ReadOnlyPropertyProtectedExtended extends ReadOnlyPropertyProtected
{
}

/**
 * The `value` property CAN be read but MUST NOT be set.
 */
class ReadOnlyPropertyPrivate extends Object
{
	private $property = 'value';

	protected function get_property()
	{
		return $this->property;
	}
}

class ReadOnlyPropertyPrivateExtended extends ReadOnlyPropertyPrivate
{
}

/**
 * The `value` property CAN be wrote but MUST NOT be read.
 */
class WriteOnlyProperty extends Object
{
	protected function set_property($value)
	{
		// …
	}
}

class WriteOnlyPropertyExtended extends WriteOnlyProperty
{
}

/**
 * The `value` property CAN be wrote but MUST NOT be read.
 */
class WriteOnlyPropertyProtected extends Object
{
	protected $property;

	protected function set_property($value)
	{
		$this->property = $value;
	}
}

class WriteOnlyPropertyProtectedExtended extends WriteOnlyPropertyProtected
{
}

/**
 * The `value` property CAN be wrote but MUST NOT be read.
 */
class WriteOnlyPropertyPrivate extends Object
{
	private $property;

	protected function set_property($value)
	{
		$this->property = $value;
	}
}

class WriteOnlyPropertyPrivateExtended extends WriteOnlyPropertyPrivate
{
}

/**
 * This use case is typically used to provide a value for an empty property, without setting
 * the property. The public property is unset to make it unaccessible, it should *not* be
 * created by the getter, but it can still be set by the user at any time.
 */
class DefaultValueForUnsetProperty extends Object
{
	public $title;
	public $slug;

	public function __construct()
	{
		if (!$this->slug)
		{
			unset($this->slug);
		}
	}

	protected function get_slug()
	{
		return \ICanBoogie\normalize($this->title);
	}
}

/**
 * This class is similar to the previous class, but the `slug` property is now protected. Thanks
 * to the volatile getter, the property can be read but setting it from the public scope
 * throws a `PropertyNotWritable` exception.
 */
class DefaultValueForUnsetProtectedProperty extends Object
{
	public $title;
	protected $slug;

	public function __construct()
	{
		if (!$this->slug)
		{
			unset($this->slug);
		}
	}

	protected function get_slug()
	{
		return \ICanBoogie\normalize($this->title);
	}
}

/**
 * ICanBoogie\PropertyNotWritable is thrown if one tries to get the `value` property because the
 * getter tries to set the value and there is no setter.
 */
class InvalidProtectedPropertyGetter extends Object
{
	protected $value;

	public function __construct()
	{
		unset($this->value);
	}

	protected function get_value()
	{
		return $this->value = uniqid();
	}
}

/**
 * ICanBoogie\PropertyNotWritable is thrown if one tries to get the `value` property because the
 * getter tries to set the value and there is no setter.
 *
 * Note: This makes `value` writable from public scope.
 */
class ValidProtectedPropertyGetter extends Object
{
	protected $value;

	public function __construct()
	{
		unset($this->value);
	}

	protected function lazy_get_value()
	{
		return uniqid();
	}
}

/**
 * A virtual property is a property whose value is stored elsewhere.
 */
class VirtualProperty extends Object
{
	public $seconds;

	protected function set_minutes($minutes)
	{
		$this->seconds = $minutes * 60;
	}

	protected function get_minutes()
	{
		return $this->seconds / 60;
	}
}

class CreatedAtCase extends Object
{
	private $created_at;

	protected function get_created_at()
	{
		$created_at = $this->created_at;

		if ($created_at instanceof \DateTime)
		{
			return $created_at;
		}

		return new \DateTime($created_at);
	}

	protected function set_created_at($created_at)
	{
		$this->created_at = $created_at;
	}
}

class CreatedAtCaseExtended extends CreatedAtCase
{

}

class ExportCase extends Object
{
	public $public = 'public';
	public $public_with_lazy_getter = 'public_with_lazy_getter';

	protected function lazy_get_public_with_lazy_getter()
	{
		return 'VALUE: public_with_lazy_getter';
	}

	protected $protected = 'protected';
	protected $protected_with_getter = 'protected_with_getter';
	protected $protected_with_setter = 'protected_with_setter';
	protected $protected_with_getter_and_setter = 'protected_with_getter_and_setter';
	protected $protected_with_lazy_getter = 'protected_with_lazy_getter';

	protected function get_protected_with_getter()
	{
		return 'VALUE: protected_with_getter';
	}

	protected function set_protected_with_setter()
	{

	}

	protected function get_protected_with_getter_and_setter()
	{
		return 'VALUE: protected_with_getter';
	}

	protected function set_protected_with_getter_and_setter()
	{

	}

	protected function lazy_get_protected_with_lazy_getter()
	{
		return 'VALUE: protected_with_lazy_getter';
	}

	private $private = 'private';
	private $private_with_getter = 'private_with_getter';
	private $private_with_setter = 'private_with_setter';
	private $private_with_getter_and_setter = 'private_with_getter_and_setter';
	private $private_with_lazy_getter = 'private_with_lazy_getter';

	protected function get_private_with_getter()
	{
		return 'VALUE: private_with_getter';
	}

	protected function set_private_with_setter()
	{

	}

	protected function get_private_with_getter_and_setter()
	{
		return 'VALUE: private_with_getter';
	}

	protected function set_private_with_getter_and_setter()
	{

	}

	protected function lazy_get_private_with_lazy_getter()
	{
		return 'VALUE: private_with_lazy_getter';
	}
}
