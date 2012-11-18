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

use ICanBoogie\Object;

/**
 * Returns the value of an object's property.
 *
 * This function is called as a last chance to get the value of a property before the
 * {@link PropertyNotDefined} exception is thrown.
 *
 * @param Object $target Target object from which the property should be retrieved.
 * @param string $property Name of the property.
 * @param bool $success `true` if the value was successfully retrieved, `false` otherwise.
 *
 * @return mixed
 */
function last_chance_get(Object $target, $property, &$success)
{
	# this code is needed to preserve arguments passed by reference

	return call_user_func_array(__NAMESPACE__ . '\Helpers::last_chance_get', array($target, $property, &$success));
}

/**
 * Sets the value of an object's property.
 *
 * This function is called as a last chance to set the value of a property before the
 * property is created with the `public` visibility.
 *
 * @param Object $target Target object in which the property should be stored.
 * @param string $property Name of the property.
 * @param mixed $value Value of the property.
 * @param bool $success `true` if the value was successfully stored, `false` otherwise.
 *
 * @return mixed
 */
function last_chance_set(Object $target, $property, $value, &$success)
{
	# this code is needed to preserve arguments passed by reference

	return call_user_func_array(__NAMESPACE__ . '\Helpers::last_chance_set', array($target, $property, $value, &$success));
}

/**
 * Patchable helpers.
 */
class Helpers
{
	static private $jumptable = array
	(
		'last_chance_get' => array(__CLASS__, 'last_chance_get'),
		'last_chance_set' => array(__CLASS__, 'last_chance_set')
	);

	/**
	 * Calls the callback of a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param array $arguments Arguments.
	 *
	 * @return mixed
	 */
	static public function __callstatic($name, array $arguments)
	{
		return call_user_func_array(self::$jumptable[$name], $arguments);
	}

	/**
	 * Patches a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param collable $callback Callback.
	 *
	 * @throws \RuntimeException is attempt to patch an undefined function.
	 */
	static public function patch($name, $callback)
	{
		if (empty(self::$jumptable[$name]))
		{
			throw new \RuntimeException("Undefined patchable: $name.");
		}

		self::$jumptable[$name] = $callback;
	}

	/*
	 * Default implementations
	 */

	static private function last_chance_get(Object $target, $property, &$success=false)
	{

	}

	static private function last_chance_set(Object $target, $property, $value, &$success=false)
	{

	}
}