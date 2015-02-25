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
 * Patchable helpers.
 */
class Helpers
{
	static private $jumptable = [

		'last_chance_get' => [ __CLASS__, 'last_chance_get' ],
		'last_chance_set' => [ __CLASS__, 'last_chance_set' ]

	];

	/**
	 * Calls the callback of a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param array $arguments Arguments.
	 *
	 * @return mixed
	 */
	static public function __callStatic($name, array $arguments)
	{
		return call_user_func_array(self::$jumptable[$name], $arguments);
	}

	/**
	 * Patches a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param callable $callback Callback.
	 *
	 * @throws \RuntimeException is attempt to patch an undefined function.
	 */
	// @codeCoverageIgnoreStart
	static public function patch($name, $callback)
	{
		if (empty(self::$jumptable[$name]))
		{
			throw new \RuntimeException("Undefined patchable: $name.");
		}

		self::$jumptable[$name] = $callback;
	}
	// @codeCoverageIgnoreEnd

	/*
	 * Default implementations
	 */

	static private function last_chance_get($target, $property, &$success = false)
	{

	}

	static private function last_chance_set($target, $property, $value, &$success = false)
	{

	}
}
