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

use ICanBoogie\Prototype\MethodNotDefined;
use function array_diff_key;
use function array_intersect_key;
use function array_merge;
use function get_class;
use function get_parent_class;
use function is_object;
use function is_subclass_of;

/**
 * Manages the prototype methods that may be bound to classes using {@link PrototypeTrait}.
 */
class Prototype implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * Prototypes instances per class.
	 *
	 * @var Prototype[]
	 */
	static private $prototypes = [];

	/**
	 * Prototype methods per class.
	 *
	 * @var array|null
	 */
	static private $bindings;

	/**
	 * Returns the prototype associated with the specified class or object.
	 *
	 * @param string|object $class_or_object Class name or object.
	 *
	 * @return Prototype
	 */
	static public function from($class_or_object): Prototype
	{
		$class = is_object($class_or_object) ? get_class($class_or_object) : $class_or_object;
		$prototype = &self::$prototypes[$class];

		return $prototype ?: $prototype = new static($class);
	}

	/**
	 * Defines prototype methods.
	 *
	 * @param array $bindings
	 */
	static public function bind(array $bindings): void
	{
		if (!$bindings)
		{
			return;
		}

		self::update_bindings($bindings);
		self::update_instances($bindings);
	}

	/**
	 * Updates prototype methods with bindings.
	 *
	 * @param array $bindings
	 */
	static private function update_bindings(array $bindings): void
	{
		$current = &self::$bindings;

		if (!$current)
		{
			$current = $bindings;
		}

		$intersect = array_intersect_key($bindings, $current);
		$current += array_diff_key($bindings, $current);

		foreach ($intersect as $class => $methods)
		{
			$current[$class] = array_merge($current[$class], $methods);
		}
	}

	/**
	 * Updates instances with bindings.
	 *
	 * @param array $bindings
	 */
	static private function update_instances(array $bindings): void
	{
		foreach (self::$prototypes as $class => $prototype)
		{
			$prototype->consolidated_methods = null;

			if (empty($bindings[$class]))
			{
				continue;
			}

			$prototype->methods = $bindings[$class] + $prototype->methods;
		}
	}

	/**
	 * Class associated with the prototype.
	 *
	 * @var string
	 */
	private $class;

	/**
	 * Parent prototype.
	 *
	 * @var Prototype
	 */
	private $parent;

	/**
	 * Methods defined by the prototype.
	 *
	 * @var callable[]
	 */
	private $methods = [];

	/**
	 * Methods defined by the prototypes chain.
	 *
	 * @var callable[]|null
	 */
	private $consolidated_methods;

	/**
	 * Creates a prototype for the specified class.
	 *
	 * @param string $class
	 */
	private function __construct(string $class)
	{
		$this->class = $class;
		$parent_class = get_parent_class($class);

		if ($parent_class)
		{
			$this->parent = static::from($parent_class);
		}

		if (isset(self::$bindings[$class]))
		{
			$this->methods = self::$bindings[$class];
		}
	}

	/**
	 * Returns the consolidated methods of the prototype.
	 *
	 * @return callable[]
	 */
	private function get_consolidated_methods(): array
	{
		$consolidated_methods = &$this->consolidated_methods;

		if ($consolidated_methods !== null)
		{
			return $consolidated_methods;
		}

		return $consolidated_methods = $this->consolidate_methods();
	}

	/**
	 * Consolidate the methods of the prototype.
	 *
	 * The method creates a single array from the prototype methods and those of its parents.
	 *
	 * @return callable[]
	 */
	private function consolidate_methods(): array
	{
		$methods = $this->methods;

		if ($this->parent)
		{
			$methods += $this->parent->get_consolidated_methods();
		}

		return $methods;
	}

	/**
	 * Revokes the consolidated methods of the prototype.
	 *
	 * The method must be invoked when prototype methods are modified.
	 */
	private function revoke_consolidated_methods(): void
	{
		$class = $this->class;

		foreach (self::$prototypes as $prototype)
		{
			if (!is_subclass_of($prototype->class, $class))
			{
				continue;
			}

			$prototype->consolidated_methods = null;
		}

		$this->consolidated_methods = null;
	}

	/**
	 * Adds or replaces the specified method of the prototype.
	 *
	 * @param string $method The name of the method.
	 *
	 * @param callable $callback
	 */
	public function offsetSet($method, $callback)
	{
 		self::$prototypes[$this->class]->methods[$method] = $callback;

		$this->revoke_consolidated_methods();
	}

	/**
	 * Removed the specified method from the prototype.
	 *
	 * @param string $method The name of the method.
	 */
	public function offsetUnset($method)
	{
		unset(self::$prototypes[$this->class]->methods[$method]);

		$this->revoke_consolidated_methods();
	}

	/**
	 * Checks if the prototype defines the specified method.
	 *
	 * @param string $method The name of the method.
	 *
	 * @return bool
	 */
	public function offsetExists($method)
	{
		$methods = &$this->consolidated_methods;

		if ($methods === null) {
			$methods = $this->consolidate_methods();
		}

		return isset($methods[$method]);
	}

	/**
	 * Returns the callback associated with the specified method.
	 *
	 * @param string $method The name of the method.
	 *
	 * @throws MethodNotDefined if the method is not defined.
	 *
	 * @return callable
	 */
	public function offsetGet($method)
	{
		$methods = &$this->consolidated_methods;

		if ($methods === null) {
			$methods = $this->consolidate_methods();
		}

		if (!isset($methods[$method]))
		{
			throw new MethodNotDefined($method, $this->class);
		}

		return $methods[$method];
	}

	/**
	 * Returns an iterator for the prototype methods.
	 */
	public function getIterator()
	{
		$methods = &$this->consolidated_methods;

		if ($methods === null) {
			$methods = $this->consolidate_methods();
		}

		return new \ArrayIterator($methods);
	}
}
