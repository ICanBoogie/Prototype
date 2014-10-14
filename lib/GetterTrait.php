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
 * Simple implementation of getters.
 *
 * <pre>
 * <?php
 *
 * class A
 * {
 *     use \ICanBoogie\GetterTrait;
 *
 *     protected function lazy_get_pseudo_uniqid()
 *     {
 *         return uniqid();
 *     }
 *
 *     protected $protected = 123;
 *
 *     protected function get_protected()
 *     {
 *         return $this->protected;
 *     }
 *
 *     private $private = 456;
 *
 *     protected function get_private()
 *     {
 *         return $this->private;
 *     }
 * }
 *
 * $a = new A;
 *
 * $pseudo_uniqid = $a->pseudo_uniqid;
 * $pseudo_uniqid == $a->pseudo_uniqid; // true
 * unset($a->pseudo_uniqid);
 * $pseudo_uniqid == $a->pseudo_uniqid; // false
 *
 * $a->protected; // 123
 * $a->private;   // 456
 * </pre>
 */
trait GetterTrait
{
	public function __get($property)
	{
		return $this->__object_get($property);
	}

	private function __object_get($property)
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
		# There is no method defined to get the requested property, the appropriate property
		# exception is raised.
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

		if (method_exists($this, 'set_' . $property))
		{
			throw new PropertyNotReadable([ $property, $this ]);
		}

		throw new PropertyNotDefined([ $property, $this ]);
	}
}