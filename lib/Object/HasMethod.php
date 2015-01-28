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

trait HasMethod
{
	/**
	 * Checks if a method exists.
	 *
	 * @param string $method
	 *
	 * @return bool `true` if the method exists, `false` otherwise.
	 */
	public function has_method($method)
	{
		return method_exists($this, $method);
	}
}
