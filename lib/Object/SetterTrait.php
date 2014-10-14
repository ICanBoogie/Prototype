<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Object;

use ICanBoogie\PropertyNotWritable;
use ICanBoogie\PropertyNotDefined;

/**
 * Simple implementation of setters.
 */
trait SetterTrait
{
	public function __set($property, $value)
	{
		$this->__object_set($property, $value);
	}

	private function __object_set($property, $value)
	{
		$method = 'set_' . $property;

		if (method_exists($this, $method))
		{
			# prevent the setter from being called twice if the property doesn't exists.
			$this->$property = null;

			return $this->$method($value);
		}

		$method = 'lazy_set_' . $property;

		if (method_exists($this, $method))
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

		if (property_exists($this, $property) && !method_exists($this, 'lazy_get_' . $property))
		{
			$reflection = new \ReflectionObject($this);

			try
			{
				$property_reflection = $reflection->getProperty($property);
			}
			catch (\ReflectionException $e)
			{
				#
				# The property is likely a private property of a parent class.
				#

				throw new PropertyNotWritable([ $property, $this ]);
			}

			if (!$property_reflection->isPublic())
			{
				throw new PropertyNotWritable([ $property, $this ]);
			}

			$this->$property = $value;

			return;
		}

		if (method_exists($this, 'get_' . $property))
		{
			throw new PropertyNotWritable([ $property, $this ]);
		}

		$this->$property = $value;
	}

	/**
	 * The method is invoked as a last chance to set a property, just before an exception
	 * is thrown.
	 *
	 * Note: The current implementation does nothing.
	 *
	 * @param string $property Property to set.
	 * @param mixed $value Value of the property.
	 * @param bool $success If the _last chance set_ was successful.
	 */
	protected function last_chance_set($property, $value, &$success)
	{

	}
}