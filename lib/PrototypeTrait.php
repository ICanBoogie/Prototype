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

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\Prototype\MethodNotDefined;
use ICanBoogie\Prototype\MethodOutOfScope;
use function array_unshift;
use function is_callable;
use function method_exists;

/**
 * A trait for classes wishing to implement prototype methods.
 *
 * @property Prototype $prototype The prototype associated with the class.
 */
trait PrototypeTrait
{
	use AccessorTrait
	{
		AccessorTrait::has_property as private accessor_has_property;
	}

	/**
	 * @var Prototype|null
	 */
	private $prototype;

	protected function get_prototype(): Prototype
	{
		return $this->prototype ?: $this->prototype = Prototype::from($this);
	}

	/**
	 * If a property exists with the name specified by `$method` and holds an object which class
	 * implements `__invoke` then the object is called with the arguments. Otherwise, calls are
	 * forwarded to the {@link $prototype}.
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		if (isset($this->$method) && is_callable([ $this->$method, '__invoke' ]))
		{
			return $this->$method(...$arguments);
		}

		array_unshift($arguments, $this);

		try
		{
			$prototype = $this->prototype ?: $this->get_prototype();
			$callable = $prototype[$method];

			return $callable(...$arguments);
		}
		catch (MethodNotDefined $e)
		{
			if (method_exists($this, $method))
			{
				throw new MethodOutOfScope($method, $this);
			}

			throw $e;
		}
	}

	/**
	 * Checks if the object has the specified property.
	 *
	 * The difference with the `property_exists()` function is that this method also checks for
	 * getters defined by the class or the prototype.
	 *
	 * @param string $property The property to check.
	 *
	 * @return bool `true` if the object has the property, `false` otherwise.
	 */
	public function has_property(string $property): bool
	{
		if ($this->accessor_has_property($property))
		{
			return true;
		}

		$success = false;
		$this->last_chance_get($property, $success);

		return $success;
	}

	/**
	 * Checks whether this object supports the specified method.
	 *
	 * The method checks for methods defined by the class and the prototype.
	 *
	 * @param string $method Name of the method.
	 *
	 * @return bool `true` if the method is defined, `false` otherwise.
	 */
	public function has_method(string $method): bool
	{
		if (method_exists($this, $method))
		{
			return true;
		}

		$prototype = $this->prototype ?: $this->get_prototype();

		return isset($prototype[$method]);
	}

	/**
	 * @inheritdoc
	 */
	protected function accessor_get($property)
	{
		$method = 'get_' . $property;

		if (method_exists($this, $method))
		{
			return $this->$method();
		}

		$method = 'lazy_get_' . $property;

		if (method_exists($this, $method))
		{
			return $this->$property = $this->$method();
		}

		#
		# we didn't find a suitable method in the class, maybe the prototype has one.
		#

		$prototype = $this->prototype ?: $this->get_prototype();

		$method = 'get_' . $property;

		if (isset($prototype[$method]))
		{
			return $prototype[$method]($this, $property);
		}

		$method  = 'lazy_get_' . $property;

		if (isset($prototype[$method]))
		{
			return $this->$property = $prototype[$method]($this, $property);
		}

		$success = false;
		$value = $this->last_chance_get($property, $success);

		if ($success)
		{
			return $value;
		}

		$this->assert_property_is_readable($property);
	} //@codeCoverageIgnore

	/**
	 * @inheritdoc
	 */
	protected function accessor_set($property, $value)
	{
		$method = 'set_' . $property;

		if ($this->has_method($method))
		{
			$this->$method($value);

			return;
		}

		$method = 'lazy_set_' . $property;

		if ($this->has_method($method))
		{
			$this->$property = $this->$method($value);

			return;
		}

		$success = false;
		$this->last_chance_set($property, $value, $success);

		if ($success)
		{
			return;
		}

		$this->assert_property_is_writable($property);

		$this->$property = $value;
	}

	/**
	 * The method is invoked as a last chance to get a property,
	 * just before an exception is thrown.
	 *
	 * @param string $property Property to get.
	 * @param bool $success If the _last chance get_ was successful.
	 *
	 * @return mixed
	 */
	protected function last_chance_get(string $property, bool &$success)
	{
		$success = false;

		return null;
	}

	/**
	 * The method is invoked as a last chance to set a property,
	 * just before an exception is thrown.
	 *
	 * @param string $property Property to set.
	 * @param mixed $value Value of the property.
	 * @param bool $success If the _last chance set_ was successful.
	 */
	protected function last_chance_set(string $property, $value, bool &$success): void
	{
		$success = false;
	}
}
