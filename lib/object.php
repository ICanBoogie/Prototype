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
 * Together with the {@link Prototype} class the {@link Object} class provides means to
 * modify methods as well as define getters and setters for magic properties.
 *
 * The class also provides a method to create instances in the same fashion PDO creates instances
 * with the `FETCH_CLASS` mode, that is the properties of the instance are set *before* its
 * constructor is invoked.
 *
 * Getters and setters
 * -------------------
 *
 * When an inaccessible property is read or written the {@link Object} tries to find a method
 * suitable to read or write the property. Theses methods are called getters and setters. They
 * can be defined by the class or its prototype. For instance, a `connection` property could
 * have the following getter and setter:
 *
 * <pre>
 * <?php
 *
 * protected function get_connection()
 * {
 *     return new Connection($this->username, $this->password);
 * }
 *
 * protected function set_connection(Connection $connection)
 * {
 *     return $connection;
 * }
 * </pre>
 *
 * In this example the `connection` property is created after `get_connection()` is called,
 * which is ideal to implement lazy loading.
 *
 * Another type of getter/setter is available that does not create the requested property. They are
 * called _volatile_, because their result is not automatically stored in the corresponding
 * property, this choice is up to the setter:
 *
 * <pre>
 * <?php
 *
 * namespace ICanBoogie;
 *
 * class Time extends Object
 * {
 *     public $seconds;
 *
 *     protected function volatile_get_minutes()
 *     {
 *         return $this->seconds / 60;
 *     }
 *
 *     protected function volatile_set_minutes($minutes)
 *     {
 *         $this->seconds = $minutes * 60;
 *     }
 * }
 * </pre>
 *
 * In this example the result of the `minutes` getter is only returned, the property is not
 * created.
 *
 * @property-read Prototype $prototype The prototype associated with the class.
 */
class Object
{
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
	static public function from($properties=null, array $construct_args=array(), $class_name=null)
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

			$done = array();

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

		if (is_callable(array($instance, '__construct')))
		{
			call_user_func_array(array($instance, '__construct'), $construct_args);
		}

		return $instance;
	}

	/**
	 * Removes properties for which a getter is defined.
	 *
	 * The method returns an array of key/key pairs.
	 *
	 * @return array
	 */
	public function __sleep()
	{
		$keys = array_keys(get_object_vars($this));

		if (!$keys)
		{
			return array();
		}

		$keys = array_combine($keys, $keys);

		foreach ($keys as $key)
		{
			if ($this->has_method('get_' . $key) || $this->has_method('volatile_get_' . $key))
			{
				unset($keys[$key]);
			}
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

			if ($this->has_method('get_' . $key)/* || $this->has_method('volatile_get_' . $key)*/)
			{
				unset($this->$key);
			}
		}
	}

	/**
	 * Defers calls for which methods are not defined to the {@link prototype}.
	 *
	 * If a property exists with the name $method and holds an object the object is called with
	 * the arguments.
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return mixed The result of the callback.
	 */
	public function __call($method, $arguments)
	{
		if (isset($this->$method) && is_object($this->$method))
		{
			return call_user_func_array($this->$method, $arguments);
		}

		array_unshift($arguments, $this);

		return call_user_func_array($this->prototype[$method], $arguments);
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
	protected function last_chance_get($property, &$success)
	{
		return Prototype\last_chance_get($this, $property, $success);
	}

	/**
	 * The method is invoked as a last chance to set a property,
	 * just before an exception is thrown.
	 *
	 * @param string $property Property to set.
	 * @param mixed $value Value of the property.
	 * @param bool $success If the _last chance set_ was successful.
	 */
	protected function last_chance_set($property, $value, &$success)
	{
		Prototype\last_chance_set($this, $property, $value, $success);
	}

	/**
	 * Returns the prototype associated with the class.
	 *
	 * @return Prototype
	 */
	protected function get_prototype()
	{
		return Prototype::get($this);
	}

	/**
	 * Returns the value of an inaccessible property.
	 *
	 * Multiple callbacks are tried in order to retrieve the value of the property:
	 *
	 * 1. `volatile_get_<property>`: Get and return the value of the property.
	 * 2. `get_<property>`: Get, set and return the value of the property. Because new properties
	 * are created as public the callback is only called once which is ideal for lazy loading.
	 * 3. The prototype is queried for callbacks for the `volatile_get_<property>` and
	 * `get_<property>` methods.
	 * 4.Finally, the `ICanBoogie\Object::property` event is fired to try and retrieve the value of
	 * the property.
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
		$method = 'volatile_get_' . $property;

		if (method_exists($this, $method))
		{
			return $this->$method();
		}

		$method = 'get_' . $property;

		if (method_exists($this, $method))
		{
			return $this->$property = $this->$method();
		}

		#
		# we didn't find a suitable method in the class, maybe the prototype has one.
		#

		$prototype = $this->prototype;

		$method = 'volatile_get_' . $property;

		if (isset($prototype[$method]))
		{
			return call_user_func($prototype[$method], $this, $property);
		}

		$method  = 'get_' . $property;

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
				throw new PropertyNotReadable(array($property, $this));
			}
		}
		catch (\ReflectionException $e) { }

		$properties = array_keys(get_object_vars($this));

		if ($properties)
		{
			throw new PropertyNotDefined(sprintf('Unknown or inaccessible property "%s" for object of class "%s" (available properties: %s).', $property, get_class($this), implode(', ', $properties)));
		}

		throw new PropertyNotDefined(array($property, $this));
	}

	/**
	 * Sets the value of an inaccessible property.
	 *
	 * The method is called because the property does not exists, it's visibility is
	 * "protected" or "private", or because although its visibility is "public" is was unset
	 * and is now inaccessible.
	 *
	 * The method only sets the property if it isn't defined by the class or its visibility is
	 * "public", but one can provide setters to override this behaviour:
	 *
	 * The `set_<property>` setter can be used to set properties that are protected or private,
	 * which can be used to make properties write-only for example.
	 *
	 * The `volatile_set_<property>` setter can be used the handle virtual properties e.g. a
	 * `minute` property that would alter a `second` property for example.
	 *
	 * The setters can be defined by the class or its prototype.
	 *
	 * Note: Permission is granted if a `get_<property>` getter is defined by the class or
	 * its prototype.
	 *
	 * @param string $property
	 * @param mixed $value
	 *
	 * @throws PropertyNotWritable if the property is not writable.
	 */
	public function __set($property, $value)
	{
		if ($property == 'prototype')
		{
			$this->prototype = $value;

			return;
		}

		$method = 'volatile_set_' . $property;

		if ($this->has_method($method))
		{
			return $this->$method($value);
		}

		$method = 'set_' . $property;

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

		if (property_exists($this, $property) && !$this->has_method('get_' . $property))
		{
			$reflection = new \ReflectionObject($this);
			$property_reflection = $reflection->getProperty($property);

			if (!$property_reflection->isPublic())
			{
				throw new PropertyNotWritable(array($property, $this));
			}
		}

		$this->$property = $value;
	}

	/**
	 * Checks if the object has the specified property.
	 *
	 * The difference with the `property_exists()` function is that the method also checks for
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

		if ($this->has_method('get_' . $property) || $this->has_method('volatile_get_' . $property))
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
	 * The method checks for method defined by the class and the prototype.
	 *
	 * @param string $method Name of the method.
	 *
	 * @return bool
	 */
	public function has_method($method)
	{
		return method_exists($this, $method) || isset($this->prototype[$method]);
	}

	public function to_array()
	{
		$properties = $this->__sleep();
		$values = get_object_vars($this);

		return array_intersect_key($values, $properties);
	}

	public function to_json()
	{
		return json_encode($this->to_array());
	}
}