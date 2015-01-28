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

class Hooks
{
	/**
	 * Synthesizes the "prototypes" config from the "hooks" config.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException if a method definition is missing the '::' separator.
	 */
	static public function synthesize_config(array $fragments)
	{
		$methods = [];

		foreach ($fragments as $pathname => $fragment)
		{
			if (empty($fragment['prototypes']))
			{
				continue;
			}

			foreach ($fragment['prototypes'] as $method => $callback)
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

				list($class, $method) = explode('::', $method);

				$methods[$class][$method] = $callback;
			}
		}

		return $methods;
	}
}
