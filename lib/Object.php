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
	 * The instance is created in the same fashion [PDO](http://www.php.net/manual/en/book.pdo.php)
	 * creates instances when fetching objects using the `FETCH_CLASS` mode, that is the properties
	 * of the instance are set *before* its constructor is invoked.
	 *
	 * Note: Because the method uses the [`unserialize`](http://www.php.net/manual/en/function.unserialize.php)
	 * function to create the instance, the `__wakeup()` magic method will be called if it is
	 * defined by the class, and it will be called *before* the constructor.
	 *
	 * Note: The {@link __wakeup()} method of the {@link Object} class removes `null` properties
	 * for which a getter is defined.
	 *
	 * @param array $properties Properties to be set before the constructor is invoked.
	 * @param array $construct_args Arguments passed to the constructor.
	 * @param string|null $class_name The name of the instance class. If empty the name of the
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

		$properties_count = 0;
		$serialized = '';

		if ($properties)
		{
			$class_reflection = new \ReflectionClass($class_name);
			$class_properties = $class_reflection->getProperties();
			$defaults = $class_reflection->getDefaultProperties();

			$done = [];

			foreach ($class_properties as $property)
			{
				if ($property->isStatic())
				{
					continue;
				}

				$properties_count++;

				$identifier = $property->name;
				$done[] = $identifier;
				$value = null;

				if (array_key_exists($identifier, $properties))
				{
					$value = $properties[$identifier];
				}
				else if (isset($defaults[$identifier]))
				{
					$value = $defaults[$identifier];
				}

				if ($property->isProtected())
				{
					$identifier = "\x00*\x00" . $identifier;
				}
				else if ($property->isPrivate())
				{
					$identifier = "\x00" . $property->class . "\x00" . $identifier;
				}

				$serialized .= serialize($identifier) . serialize($value);
			}

			$extra = array_diff(array_keys($properties), $done);

			foreach ($extra as $name)
			{
				$properties_count++;

				$serialized .= serialize($name) . serialize($properties[$name]);
			}
		}

		$serialized = 'O:' . strlen($class_name) . ':"' . $class_name . '":' . $properties_count . ':{' . $serialized . '}';

		$instance = unserialize($serialized);

		#
		# for some reason is_callable() sometimes returns true event if the `__construct` method is not defined.
		#

		if (method_exists($instance, '__construct') && is_callable([ $instance, '__construct' ]))
		{
			call_user_func_array([ $instance, '__construct' ], $construct_args);
		}

		return $instance;
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
		$array = \ICanBoogie\Prototype\get_public_object_vars($this);

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
