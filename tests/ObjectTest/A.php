<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ObjectTest;

use ICanBoogie\Prototyped;

class A extends Prototyped
{
	public $a;
	public $b;
	public $unset;
	protected $unset_protected;

	public function __construct()
	{
		unset($this->a);
		unset($this->b);
		unset($this->unset);
		unset($this->unset_protected);
	}

	protected function lazy_get_a()
	{
		return 'a';
	}

	protected function get_b()
	{
		return 'b';
	}

	protected $c;

	protected function lazy_set_c($value)
	{
		return $value;
	}

	protected function lazy_get_c()
	{
		return $this->c;
	}

	protected $d;

	protected function set_d($value)
	{
		$this->d = $value;
	}

	protected function get_d()
	{
		return $this->d;
	}

	private $e;

	protected function lazy_set_e($value)
	{
		return $value;
	}

	protected function lazy_get_e()
	{
		return $this->e;
	}

	protected $f;

	protected function set_f($value)
	{
		$this->f = $value;
	}

	protected function get_f()
	{
		return $this->f;
	}

	private $readonly = 'readonly';

	protected function get_readonly()
	{
		return $this->readonly;
	}

	private $writeonly;

	protected function set_writeonly($value)
	{
		$this->writeonly = $value;
	}

	protected function get_read_writeonly()
	{
		return $this->writeonly;
	}

	protected function lazy_get_pseudo_uniq()
	{
		return uniqid();
	}

	protected function lazy_set_with_parent($value)
	{
		return $value + 1;
	}
}
