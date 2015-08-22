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

/**
 * Hooks
 */
class Hooks
{
	/**
	 * Synthesizes the "prototype" config from the "prototype" config fragments.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	static public function synthesize_config(array $fragments)
	{
		$methods = [];

		foreach ($fragments as $pathname => $fragment)
		{
			foreach ($fragment as $method => $callback)
			{
				self::assert_valid_prototype_method_name($method, $pathname);

				list($class, $method) = explode('::', $method);

				$methods[$class][$method] = $callback;
			}
		}

		return $methods;
	}

	/**
	 * Asserts that a prototype method name is valid.
	 *
	 * @param string $method
	 * @param string $pathname
	 *
	 * @throws \InvalidArgumentException if a method definition is missing the '::' separator.
	 */
	static private function assert_valid_prototype_method_name($method, $pathname)
	{
		if (strpos($method, '::') === false)
		{
			throw new \InvalidArgumentException(\ICanBoogie\format
			(
				'Invalid method name %method, must be <code>class_name::method_name</code> in %pathname"', [

					'method' => $method,
					'pathname' => $pathname

				]
			));
		}
	}
}
