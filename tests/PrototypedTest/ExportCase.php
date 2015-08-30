<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\PrototypedTest;

use ICanBoogie\Prototyped;

class ExportCase extends Prototyped
{
	public $public = 'public';
	public $public_with_lazy_getter = 'public_with_lazy_getter';

	protected function lazy_get_public_with_lazy_getter()
	{
		return 'VALUE: public_with_lazy_getter';
	}

	protected $protected = 'protected';
	protected $protected_with_getter = 'protected_with_getter';
	protected $protected_with_setter = 'protected_with_setter';
	protected $protected_with_getter_and_setter = 'protected_with_getter_and_setter';
	protected $protected_with_lazy_getter = 'protected_with_lazy_getter';

	protected function get_protected_with_getter()
	{
		return 'VALUE: protected_with_getter';
	}

	protected function set_protected_with_setter()
	{

	}

	protected function get_protected_with_getter_and_setter()
	{
		return 'VALUE: protected_with_getter';
	}

	protected function set_protected_with_getter_and_setter()
	{

	}

	protected function lazy_get_protected_with_lazy_getter()
	{
		return 'VALUE: protected_with_lazy_getter';
	}

	private $private = 'private';
	private $private_with_getter = 'private_with_getter';
	private $private_with_setter = 'private_with_setter';
	private $private_with_getter_and_setter = 'private_with_getter_and_setter';
	private $private_with_lazy_getter = 'private_with_lazy_getter';

	protected function get_private_with_getter()
	{
		return 'VALUE: private_with_getter';
	}

	protected function set_private_with_setter()
	{

	}

	protected function get_private_with_getter_and_setter()
	{
		return 'VALUE: private_with_getter';
	}

	protected function set_private_with_getter_and_setter()
	{

	}

	protected function lazy_get_private_with_lazy_getter()
	{
		return 'VALUE: private_with_lazy_getter';
	}
}
