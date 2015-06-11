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

use ICanBoogie\Accessor\AccessorReflection;

/**
 * Together with the {@link Prototype} class the {@link Object} class provides means to
 * define getters and setters, as well as define getters, setters, and method at runtime.
 *
 * The class also provides a method to create instances in the same fashion PDO creates instances
 * with the `FETCH_CLASS` mode, that is the properties of the instance are set *before* its
 * constructor is invoked.
 *
 * @property-read Prototype $prototype The prototype associated with the class.
 */
class Object implements ToArrayRecursive
{
	use ToArrayRecursiveTrait;
	use PrototypeTrait;

	/**
	 * Creates a new instance of the class using the supplied properties.
	 *
	 * The method tries to create the instance in the same fashion as [PDO](http://www.php.net/manual/en/book.pdo.php)
	 * with the `FETCH_CLASS` mode, that is the properties of the instance are set *before* its
	 * constructor is invoked.
	 *
	 * @param array $properties Properties to be set before the constructor is invoked.
	 * @param array $construct_args Arguments passed to the constructor.
	 * @param string|null $class_name The name of the instance class. If empty, the name of the
	 * called class is used.
	 *
	 * @return mixed The new instance.
	 */
	static public function from($properties = null, array $construct_args = [], $class_name = null)
	{
		if (!$class_name)
		{
			$class_name = get_called_class();
		}

		$class_reflection = self::get_class_reflection($class_name);

		if (!$properties)
		{
			return $class_reflection->newInstanceArgs($construct_args);
		}

		$instance = $class_reflection->newInstanceWithoutConstructor();

		foreach ($properties as $property => $value)
		{
			$instance->$property = $value;
		}

		if ($class_reflection->hasMethod('__construct') && is_callable([ $instance, '__construct' ]))
		{
			call_user_func_array([ $instance, '__construct' ], $construct_args);
		}

		return $instance;
	}

	static private $class_reflection_cache = [];

	/**
	 * Returns cached class reflection.
	 *
	 * @param string $class_name
	 *
	 * @return \ReflectionClass
	 */
	static private function get_class_reflection($class_name)
	{
		if (isset(self::$class_reflection_cache[$class_name]))
		{
			return self::$class_reflection_cache[$class_name];
		}

		return self::$class_reflection_cache[$class_name] = new \ReflectionClass($class_name);
	}

	/**
	 * Returns the public properties of an instance.
	 *
	 * @param mixed $object
	 *
	 * @return array
	 */
	static private function get_object_vars($object)
	{
		static $get_object_vars;

		if (!$get_object_vars)
		{
			$get_object_vars = \Closure::bind(function($object) {

				return get_object_vars($object);

			}, null, 'stdClass');
		}

		return $get_object_vars($object);
	}

	/**
	 * Converts the object into an array.
	 *
	 * Only public properties and façade properties are included.
	 *
	 * @return array
	 */
	public function to_array()
	{
		$array = self::get_object_vars($this);

		foreach (array_keys(AccessorReflection::resolve_facade_properties($this)) as $name)
		{
			$array[$name] = $this->$name;
		}

		return $array;
	}

	/**
	 * Converts the object into a JSON string.
	 *
	 * @return string
	 */
	public function to_json()
	{
		return json_encode($this->to_array_recursive());
	}
}
