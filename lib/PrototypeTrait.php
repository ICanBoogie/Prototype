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

use ICanBoogie\Prototype\HasMethod;
use ICanBoogie\Prototype\MethodNotDefined;
use ICanBoogie\Prototype\MethodOutOfScope;

trait PrototypeTrait
{
	use HasMethod;

	private $prototype;

	/**
	 * Returns the prototype associated with the class.
	 *
	 * @return Prototype
	 */
	protected function get_prototype()
	{
		if (!$this->prototype)
		{
			$this->prototype = Prototype::from($this);
		}

		return $this->prototype;
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
	 * @return array
	 */
	public function __sleep()
	{
		$keys = array_keys(get_object_vars($this));

		if ($keys)
		{
			$keys = array_combine($keys, $keys);

			unset($keys['prototype']);

			foreach ($keys as $key)
			{
				#
				# we don't use {@link has_method()} because using prototype during session write
				# seams to corrupt PHP (tested with PHP 5.3.3).
				#

				if (method_exists($this, 'lazy_get_' . $key))
				{
					unset($keys[$key]);
				}
			}
		}

		foreach (Object::resolve_facade_properties($this) as $name => $property)
		{
			$keys[$name] = "\x00" . $property->class . "\x00" . $name;
		}

		return $keys;
	}

	/**
	 * Unsets null properties for which a getter is defined so that it is called when the property
	 * is accessed.
	 */
	public function __wakeup()
	{
		$vars = get_object_vars($this);

		foreach ($vars as $key => $value)
		{
			if ($value !== null)
			{
				continue;
			}

			if ($this->has_method('lazy_get_' . $key))
			{
				unset($this->$key);
			}
		}
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
			return call_user_func_array($this->$method, $arguments);
		}

		array_unshift($arguments, $this);

		try
		{
			$prototype = $this->prototype ?: $this->get_prototype();

			return call_user_func_array($prototype[$method], $arguments);
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
	 * Returns the value of an inaccessible property.
	 *
	 * Multiple callbacks are tried in order to retrieve the value of the property:
	 *
	 * 1. `get_<property>`: Get and return the value of the property.
	 * 2. `lazy_get_<property>`: Get, set and return the value of the property. Because new
	 * properties are created as public the callback is only called once which is ideal for lazy
	 * loading.
	 * 3. The prototype is queried for callbacks for the `get_<property>` and
	 * `lazy_get_<property>` methods.
	 * 4. Finally, the `ICanBoogie\Object::property` event is fired to try and retrieve the value
	 * of the property.
	 *
	 * @param string $property
	 *
	 * @throws PropertyNotReadable when the property has a protected or private scope and
	 * no suitable callback could be found to retrieve its value.
	 *
	 * @throws PropertyNotDefined when the property is undefined and there is no suitable
	 * callback to retrieve its values.
	 *
	 * @return mixed The value of the inaccessible property.
	 */
	public function __get($property)
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
			return call_user_func($prototype[$method], $this, $property);
		}

		$method  = 'lazy_get_' . $property;

		if (isset($prototype[$method]))
		{
			return $this->$property = call_user_func($prototype[$method], $this, $property);
		}

		$success = false;
		$value = $this->last_chance_get($property, $success);

		if ($success)
		{
			return $value;
		}

		#
		# We tried, but the property really is unaccessible.
		#

		$reflexion_class = new \ReflectionClass($this);

		try
		{
			$reflexion_property = $reflexion_class->getProperty($property);

			if (!$reflexion_property->isPublic())
			{
				throw new PropertyNotReadable([ $property, $this ]);
			}
		}
		catch (\ReflectionException $e) { }

		if ($this->has_method('set_' . $property))
		{
			throw new PropertyNotReadable([ $property, $this ]);
		}

		$properties = array_keys(get_object_vars($this));

		if ($properties)
		{
			throw new PropertyNotDefined(sprintf('Unknown or inaccessible property "%s" for object of class "%s" (available properties: %s).', $property, get_class($this), implode(', ', $properties)));
		}

		throw new PropertyNotDefined([ $property, $this ]);
	}

	/**
	 * Sets the value of an inaccessible property.
	 *
	 * The method is called because the property does not exists, it's visibility is
	 * "protected" or "private", or because although its visibility is "public" is was unset
	 * and is now inaccessible.
	 *
	 * The method only sets the property if it isn't defined by the class or its visibility is
	 * "public", but one can provide setters to override this behavior:
	 *
	 * The `set_<property>` setter can be used to set properties that are protected or private,
	 * which can be used to make properties write-only for example.
	 *
	 * The `volatile_set_<property>` setter can be used the handle virtual properties e.g. a
	 * `minute` property that would alter a `second` property for example.
	 *
	 * The setters can be defined by the class or its prototype.
	 *
	 * Note: Permission is granted if a `lazy_get_<property>` getter is defined by the class or
	 * its prototype.
	 *
	 * @param string $property
	 * @param mixed $value
	 *
	 * @throws PropertyNotWritable if the property is not writable.
	 */
	public function __set($property, $value)
	{
		$method = 'set_' . $property;

		if ($this->has_method($method))
		{
			return $this->$method($value);
		}

		$method = 'lazy_set_' . $property;

		if ($this->has_method($method))
		{
			return $this->$property = $this->$method($value);
		}

		$success = false;
		$this->last_chance_set($property, $value, $success);

		if ($success)
		{
			return;
		}

		#
		# We tried, but the property really is unaccessible.
		#

		if (property_exists($this, $property) && !$this->has_method('lazy_get_' . $property))
		{
			$reflection = new \ReflectionObject($this);
			$property_reflection = $reflection->getProperty($property);

			if (!$property_reflection->isPublic())
			{
				throw new PropertyNotWritable([ $property, $this ]);
			}

			$this->$property = $value;

			return;
		}

		if ($this->has_method('get_' . $property))
		{
			throw new PropertyNotWritable([ $property, $this ]);
		}

		$this->$property = $value;
	}

	/**
	 * Checks if the object has the specified property.
	 *
	 * The difference with the `property_exists()` function is that this method also checks for
	 * getters defined by the class or the prototype.
	 *
	 * @param string $property The property to check.
	 *
	 * @return bool true if the object has the property, false otherwise.
	 */
	public function has_property($property)
	{
		if (property_exists($this, $property))
		{
			return true;
		}

		if ($this->has_method('get_' . $property) || $this->has_method('lazy_get_' . $property))
		{
			return true;
		}

		$success = false;
		$this->last_chance_get($property, $success);

		return $success;
	}

	/**
	 * The method is invoked as a last chance to get a property,
	 * just before an exception is thrown.
	 *
	 * The method uses the helper {@link Prototype\last_chance_get()}.
	 *
	 * @param string $property Property to get.
	 * @param bool $success If the _last chance get_ was successful.
	 *
	 * @return mixed
	 */
	protected function last_chance_get($property, &$success)
	{
		return Prototype\last_chance_get($this, $property, $success);
	}

	/**
	 * The method is invoked as a last chance to set a property,
	 * just before an exception is thrown.
	 *
	 * The method uses the helper {@link Prototype\last_chance_set()}.
	 *
	 * @param string $property Property to set.
	 * @param mixed $value Value of the property.
	 * @param bool $success If the _last chance set_ was successful.
	 */
	protected function last_chance_set($property, $value, &$success)
	{
		Prototype\last_chance_set($this, $property, $value, $success);
	}
}