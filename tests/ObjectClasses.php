<?php

namespace ICanBoogie\ObjectTest;

use ICanBoogie\Object;

/**
 * The `value` property CAN be read but MUST NOT be set.
 */
class ReadOnlyProperty extends Object
{
	protected function volatile_get_value()
	{
		return 'value';
	}
}

/**
 * The `value` property CAN be wrote but MUST NOT be read.
 */
class WriteOnlyProperty extends Object
{
	protected function volatile_set_value($value)
	{
		// …
	}
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

	protected function volatile_get_slug()
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

	protected function volatile_get_slug()
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

	protected function volatile_get_value()
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

	protected function get_value()
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

	protected function volatile_set_minutes($minutes)
	{
		$this->seconds = $minutes * 60;
	}

	protected function volatile_get_minutes()
	{
		return $this->seconds / 60;
	}
}