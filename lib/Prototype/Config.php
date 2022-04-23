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

final class Config
{
	/**
	 * @param array{ 'bindings': array<class-string, array<string, callable>> } $an_array
	 *
	 * @return self
	 */
	public static function __set_state(array $an_array): self
	{
		return new self($an_array['bindings']);
	}

	/**
	 * @param array<class-string, array<string, callable>> $bindings
	 *     Where _key_ is a target class and _value_ is an array of method bindings,
	 *     where _key_ is a method and _value_ a callable.
	 */
	public function __construct(
		public readonly array $bindings = []
	) {
	}
}
