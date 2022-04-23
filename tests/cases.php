<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\PrototypedCases;

use ICanBoogie\Prototyped;

class ReadOnlyPropertyExtended extends \Test\ICanBoogie\PrototypedCases\ReadOnlyProperty
{
}

/**
 * The `value` property CAN be read but MUST NOT be set.
 */
class ReadOnlyPropertyProtected extends Prototyped
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
class ReadOnlyPropertyPrivate extends Prototyped
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
class WriteOnlyProperty extends Prototyped
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
class WriteOnlyPropertyProtected extends Prototyped
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
class WriteOnlyPropertyPrivate extends Prototyped
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
class DefaultValueForUnsetProperty extends Prototyped
{
    public $title;
    public $slug;

    public function __construct()
    {
        if (!$this->slug) {
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
 *
 * @property-read string $slug
 */
class DefaultValueForUnsetProtectedProperty extends Prototyped
{
    public $title;
    protected $slug;

    public function __construct()
    {
        if (empty($this->slug)) {
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
class InvalidProtectedPropertyGetter extends Prototyped
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
class ValidProtectedPropertyGetter extends Prototyped
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
class VirtualProperty extends Prototyped
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
