<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Prototype;

/**
 * Returns the value of an object's property.
 *
 * This function is called as a last chance to get the value of a property before the
 * {@link PropertyNotDefined} exception is thrown.
 *
 * @param object $target Target object from which the property should be retrieved.
 * @param string $property Name of the property.
 * @param bool $success `true` if the value was successfully retrieved, `false` otherwise.
 *
 * @return mixed
 */
function last_chance_get($target, $property, &$success)
{
	# this code is needed to preserve arguments passed by reference

	return call_user_func_array(__NAMESPACE__ . '\Helpers::last_chance_get', [ $target, $property, &$success ]);
}

/**
 * Sets the value of an object's property.
 *
 * This function is called as a last chance to set the value of a property before the
 * property is created with the `public` visibility.
 *
 * @param object $target Target object in which the property should be stored.
 * @param string $property Name of the property.
 * @param mixed $value Value of the property.
 * @param bool $success `true` if the value was successfully stored, `false` otherwise.
 *
 * @return mixed
 */
function last_chance_set($target, $property, $value, &$success)
{
	# this code is needed to preserve arguments passed by reference

	return call_user_func_array(__NAMESPACE__ . '\Helpers::last_chance_set', [ $target, $property, $value, &$success ]);
}

/**
 * Get public object variables.
 *
 * @param mixed $object
 *
 * @return array
 */
function get_public_object_vars($object)
{
	return get_object_vars($object);
}
