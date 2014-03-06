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
 * return array
 * (
 *     'prototypes' => array
 *     (
 *         'Icybee\Modules\Pages\Page::my_additional_method' => 'MyHookClass::my_additional_method',
 *         'Icybee\Modules\Pages\Page::lazy_get_my_property' => 'MyHookClass::lazy_get_my_property'
 *     )
 * );
 * </pre>
 */
class Prototype implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * Prototypes built per class.
	 *
	 * @var array[string]Prototype
	 */
	static protected $prototypes = array();

	/**
	 * Pool of prototype methods per class.
	 *
	 * @var array[string]callable
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

			if ($prototype->methods)
			{
				echo "overriding prototype methods:"; var_dump($prototype->methods);
			}

			$prototype->methods = $config[$class];
		}
	}

	/**
	 * Synthesizes the prototype methods from the "hooks" config.
	 *
	 * @param array $fragments
	 *
	 * @return array[string]callable
	 *
	 * @throws \InvalidArgumentException if a method definition is missing the '::' separator.
	 */
	static public function synthesize_config(array $fragments)
	{
		$methods = array();
		$debug = Debug::$mode == Debug::MODE_DEV;

		foreach ($fragments as $pathname => $fragment)
		{
			if (empty($fragment['prototypes']))
			{
				continue;
			}

			foreach ($fragment['prototypes'] as $method => $callback)
			{
				if ($debug && strpos($method, '::') === false)
				{
					throw new \InvalidArgumentException(sprintf
					(
						'Invalid method name "%s", must be <code>class_name::method_name</code> in "%s"', $method, $pathname
					));
				}

				list($class, $method) = explode('::', $method);

				$methods[$class][$method] = $callback;
			}
		}

		return $methods;
	}

	/**
	 * Returns the private properties defined by the reference, this includes the private
	 * properties defined by the whole class inheritance.
	 *
	 * @param string|object $reference Class name or instance.
	 *
	 * @return array
	 */
	static public function resolve_private_properties($reference)
	{
		if (is_object($reference))
		{
			$reference = get_class($reference);
		}

		if (isset(self::$resolve_private_properties_cache[$reference]))
		{
			return self::$resolve_private_properties_cache[$reference];
		}

		$private_properties = array();
		$class_reflection = new \ReflectionClass($reference);

		while ($class_reflection)
		{
			$private_properties = array_merge($private_properties, $class_reflection->getProperties(\ReflectionProperty::IS_PRIVATE));

			$class_reflection = $class_reflection->getParentClass();
		}

		return self::$resolve_private_properties_cache[$reference] = $private_properties;
	}

	static private $resolve_private_properties_cache = array();

	/**
	 * Returns the façade properties implemented by the specified reference.
	 *
	 * A façade property is a combination of a private property with the corresponding volatile
	 * getter and setter.
	 *
	 * @param string|object $reference Class name of instance.
	 *
	 * @return array[string]\ReflectionProperty
	 */
	static public function resolve_facade_properties($reference)
	{
		if (is_object($reference))
		{
			$reference = get_class($reference);
		}

		if (isset(self::$resolve_facade_properties_cache[$reference]))
		{
			return self::$resolve_facade_properties_cache[$reference];
		}

		$facade_properties = array();

		foreach (self::resolve_private_properties($reference) as $property)
		{
			$name = $property->name;

			if (!method_exists($reference, "get_{$name}") || !method_exists($reference, "set_{$name}"))
			{
				continue;
			}

			$facade_properties[$name] = $property;
		}

		return self::$resolve_facade_properties_cache[$reference] = $facade_properties;
	}

	static private $resolve_facade_properties_cache = array();

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
	 * @var array[string]callable
	 */
	protected $methods = array();

	/**
	 * Methods defined by the prototypes chain.
	 *
	 * @var array[string]callable
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
	 * @return array[string]callable
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
		$methods = $this->get_consolidated_methods();

		return isset($methods[$method]);
	}

	/**
	 * Returns the callback associated with the specified method.
	 *
	 * @param string $method The name of the method.
	 *
	 * @throws Prototype\MethodNotDefined if the method is not defined.
	 *
	 * @return callable
	 */
	public function offsetGet($method)
	{
		$methods = $this->get_consolidated_methods();

		if (!isset($methods[$method]))
		{
			throw new Prototype\MethodNotDefined(array($method, $this->class));
		}

		return $methods[$method];
	}

	/**
	 * Returns an iterator for the prototype methods.
	 *
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		$methods = $this->get_consolidated_methods();

		return new \ArrayIterator($methods);
	}
}

namespace ICanBoogie\Prototype;

/**
 * This exception is thrown when one tries to access an undefined prototype method.
 */
class MethodNotDefined extends \BadMethodCallException
{
	public function __construct($message, $code=500, \Exception $previous=null)
	{
		if (is_array($message))
		{
			$message = sprintf('Method "%s" is not defined by the prototype of class "%s".', $message[0], $message[1]);
		}

		parent::__construct($message, $code, $previous);
	}
}