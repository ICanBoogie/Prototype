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

use Closure;
use ICanBoogie\Accessor\AccessorReflection;
use ICanBoogie\Accessor\SerializableTrait;
use ICanBoogie\Prototype\UnableToInstantiate;
use JsonException;
use ReflectionClass;
use ReflectionException;
use Throwable;

use function array_fill_keys;
use function array_intersect_key;
use function array_keys;
use function get_called_class;
use function get_object_vars;
use function is_callable;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Together with the {@link Prototype} class the {@link Prototyped} class provides means to
 * define getters and setters, as well as define getters, setters, and method at runtime.
 *
 * The class also provides a method to create instances in the same fashion PDO creates instances
 * with the `FETCH_CLASS` mode, that is the properties of the instance are set *before* its
 * constructor is invoked.
 */
class Prototyped implements ToArrayRecursive
{
	use ToArrayRecursiveTrait;
	use PrototypeTrait;
	use SerializableTrait;

	public const ASSIGN_SAFE = false;
	public const ASSIGN_UNSAFE = true;

	/**
	 * Creates a new instance of the class using the supplied properties.
	 *
	 * The method tries to create the instance in the same fashion as
	 * [PDO](http://www.php.net/manual/en/book.pdo.php) with the `FETCH_CLASS` mode, that is the
	 * properties of the instance are set *before* its constructor is invoked.
	 *
	 * @param array<string, mixed> $properties Properties to be set before the constructor is invoked.
	 * @param mixed[] $construct_args Arguments passed to the constructor.
	 * @param class-string|null $class_name The name of the instance class. If empty, the name of the
	 * called class is used.
	 *
	 * @return object The new instance.
	 *
	 * @throws UnableToInstantiate
	 */
	static public function from(array $properties = [], array $construct_args = [], string $class_name = null): object
	{
		if (!$class_name) {
			$class_name = get_called_class();
		}

		try {
			$class_reflection = self::get_class_reflection($class_name);

			if (!$properties)
			{
				return $class_reflection->newInstanceArgs($construct_args);
			}

			$instance = $class_reflection->newInstanceWithoutConstructor();

			if ($instance instanceof self)
			{
				$instance->assign($properties, self::ASSIGN_UNSAFE);
			}
			else foreach ($properties as $property => $value)
			{
				$instance->$property = $value;
			}

			if ($class_reflection->hasMethod('__construct') && is_callable([ $instance, '__construct' ]))
			{
				$instance->__construct(...$construct_args);
			}

			return $instance;
		}
		catch (Throwable $e)
		{
			throw new UnableToInstantiate("Unable to instantiate `$class_name`.", 0, $e);
		}
	}

	/**
	 * Returns assignable properties.
	 *
	 * @return string[]
	 */
	static public function assignable(): array
	{
		return [];
	}

	/**
	 * @var array<class-string, ReflectionClass<object>>
	 */
	static private array $class_reflection_cache = [];

	/**
	 * Returns cached class reflection.
	 *
	 * @param class-string $class_name
	 *
	 * @return ReflectionClass<object>
	 *
	 * @throws ReflectionException
	 */
	static private function get_class_reflection(string $class_name): ReflectionClass
	{
		return self::$class_reflection_cache[$class_name] ??= new ReflectionClass($class_name);
	}

	/**
	 * Returns the public properties of an instance.
	 *
	 * @return array<string, mixed>
	 */
	static private function get_object_vars(object $object): array
	{
		static $get_object_vars;

		if (!$get_object_vars)
		{
			$get_object_vars = Closure::bind(function(object $object) {

				return get_object_vars($object);

			}, null, get_class(new class {})); // Because `stdClass` is a no-no in PHP7
		}

		return $get_object_vars($object);
	}

	/**
	 * The method returns an array of key/key pairs.
	 *
	 * Properties for which a lazy getter is defined are discarded. For instance, if the property
	 * `next` is defined and the class of the instance defines the getter `lazy_get_next()`, the
	 * property is discarded.
	 *
	 * Note that façade properties are also included.
	 *
	 * Warning: The code used to export private properties seams to produce frameless exception on
	 * session close. If you encounter this problem you might want to override the method. Don't
	 * forget to remove the prototype property!
	 *
	 * @return array<string, mixed>
	 *
	 * @throws ReflectionException
	 */
	public function __sleep()
	{
		$keys = $this->accessor_sleep();

		unset($keys['prototype']);

		return $keys;
	}

	/**
	 * Assigns properties to an object.
	 *
	 * @param array<string, mixed> $properties The properties to assign.
	 * @param bool $unsafe The properties are not filtered if `true`.
	 *
	 * @return $this
	 */
	public function assign(array $properties, bool $unsafe = self::ASSIGN_SAFE): object
	{
		if (!$unsafe)
		{
			$properties = array_intersect_key($properties, array_fill_keys(static::assignable(), null));
		}

		foreach ($properties as $property => $value)
		{
			$this->$property = $value;
		}

		return $this;
	}

	/**
	 * Converts the object into an array.
	 *
	 * Only public properties and façade properties are included.
	 *
	 * @return array<string, mixed>
	 *
	 * @throws ReflectionException
	 */
	public function to_array(): array
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
	 * @throws JsonException
	 */
	public function to_json(): string
	{
		return json_encode($this->to_array_recursive(), JSON_THROW_ON_ERROR);
	}
}
