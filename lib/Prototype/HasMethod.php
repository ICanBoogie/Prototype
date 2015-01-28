<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Prototype;

use ICanBoogie\Prototype;

/**
 * Implements `has_method` for prototypes.
 *
 * @package ICanBoogie\Prototype
 *
 * @property-read Prototype $prototype
 */
trait HasMethod
{
	/**
	 * @return Prototype
	 */
	abstract protected function get_prototype();

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
		if (method_exists($this, $method))
		{
			return true;
		}

		$prototype = $this->prototype ?: $this->get_prototype();

		return isset($prototype[$method]);
	}
}
