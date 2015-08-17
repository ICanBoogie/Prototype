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

/**
 * Subclasses of the {@link Object} class are associated with a prototype, which can be used to
 * add methods as well as getters and setters to classes.
 *
 * When using the ICanBoogie framework, methods can be defined using the "hooks" config and the
 * "prototypes" namespace:
 *
 * <pre>
 * <?php
 *
 * return [
 *
 *     'prototypes' => [
 *
 *         'Icybee\Modules\Pages\Page::my_additional_method' => 'MyHookClass::my_additional_method',
 *         'Icybee\Modules\Pages\Page::lazy_get_my_property' => 'MyHookClass::lazy_get_my_property'
 *
 *     ]
 * ];
 * </pre>
 */
class Prototype implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * Prototypes built per class.
	 *
	 * @var Prototype[]
	 */
	static protected $prototypes = [];

	/**
	 * Pool of prototype methods per class.
	 *
	 * @var array
	 */
	static protected $pool;

	/**
	 * Returns the prototype associated with the specified class or object.
	 *
	 * @param string|object $class Class name or instance.
	 *
	 * @return Prototype
	 */
	static public function from($class)
	{
		if (is_object($class))
		{
			$class = get_class($class);
		}

		if (empty(self::$prototypes[$class]))
		{
			self::$prototypes[$class] = new static($class);
		}

		return self::$prototypes[$class];
	}

	/**
	 * Defines many prototype methods in a single call.
	 *
	 * @param array $config
	 */
	static public function configure(array $config)
	{
		self::$pool = $config;

		foreach (self::$prototypes as $class => $prototype)
		{
			$prototype->consolidated_methods = null;

			if (empty($config[$class]))
			{
				continue;
			}

			$prototype->methods = $config[$class] + $prototype->methods;
		}
	}

	/**
	 * Class associated with the prototype.
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * Parent prototype.
	 *
	 * @var Prototype
	 */
	protected $parent;

	/**
	 * Methods defined by the prototype.
	 *
	 * @var callable[]
	 */
	protected $methods = [];

	/**
	 * Methods defined by the prototypes chain.
	 *
	 * @var callable[]|null
	 */
	protected $consolidated_methods;

	/**
	 * Creates a prototype for the specified class.
	 *
	 * @param string $class
	 */
	protected function __construct($class)
	{
		$this->class = $class;

		$parent_class = get_parent_class($class);

		if ($parent_class)
		{
			$this->parent = static::from($parent_class);
		}

		if (isset(self::$pool[$class]))
		{
			$this->methods = self::$pool[$class];
		}
	}

	/**
	 * Consolidate the methods of the prototype.
	 *
	 * The method creates a single array from the prototype methods and those of its parents.
	 *
	 * @return callable[]
	 */
	protected function get_consolidated_methods()
	{
		if ($this->consolidated_methods !== null)
		{
			return $this->consolidated_methods;
		}

		$methods = $this->methods;

		if ($this->parent)
		{
			$methods += $this->parent->get_consolidated_methods();
		}

		return $this->consolidated_methods = $methods;
	}

	/**
	 * Revokes the consolidated methods of the prototype.
	 *
	 * The method must be invoked when prototype methods are modified.
	 */
	protected function revoke_consolidated_methods()
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
		$methods = $this->consolidated_methods !== null
			? $this->consolidated_methods
			: $this->get_consolidated_methods();

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
		$methods = $this->consolidated_methods !== null
			? $this->consolidated_methods
			: $this->get_consolidated_methods();

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
		$methods = $this->consolidated_methods !== null
			? $this->consolidated_methods
			: $this->get_consolidated_methods();

		return new \ArrayIterator($methods);
	}
}
