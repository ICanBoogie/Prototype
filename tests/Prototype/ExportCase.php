<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\Prototype;

use ICanBoogie\Prototyped;

class ExportCase extends Prototyped
{
	public string $public = 'public';
	public string $public_with_lazy_getter = 'public_with_lazy_getter';

	protected function lazy_get_public_with_lazy_getter(): string
	{
		return 'VALUE: public_with_lazy_getter';
	}

	protected string $protected = 'protected';
	protected string $protected_with_getter = 'protected_with_getter';
	protected string $protected_with_setter = 'protected_with_setter';
	protected string $protected_with_getter_and_setter = 'protected_with_getter_and_setter';
	protected string $protected_with_lazy_getter = 'protected_with_lazy_getter';

	protected function get_protected_with_getter(): string
	{
		return 'VALUE: protected_with_getter';
	}

	protected function set_protected_with_setter(): void
	{

	}

	protected function get_protected_with_getter_and_setter(): string
	{
		return 'VALUE: protected_with_getter';
	}

	protected function set_protected_with_getter_and_setter(): void
	{

	}

	protected function lazy_get_protected_with_lazy_getter(): string
	{
		return 'VALUE: protected_with_lazy_getter';
	}

	private string $private = 'private';
	private string $private_with_getter = 'private_with_getter';
	private string $private_with_setter = 'private_with_setter';
	private string $private_with_getter_and_setter = 'private_with_getter_and_setter';
	private string $private_with_lazy_getter = 'private_with_lazy_getter';

	protected function get_private_with_getter(): string
	{
		return 'VALUE: private_with_getter';
	}

	protected function set_private_with_setter(): void
	{

	}

	protected function get_private_with_getter_and_setter(): string
	{
		return 'VALUE: private_with_getter';
	}

	protected function set_private_with_getter_and_setter(): void
	{

	}

	protected function lazy_get_private_with_lazy_getter(): string
	{
		return 'VALUE: private_with_lazy_getter';
	}
}
