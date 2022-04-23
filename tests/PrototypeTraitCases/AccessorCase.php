<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\PrototypeTraitCases;

use ICanBoogie\PrototypeTrait;

/**
 * @property-read mixed $a
 * @property-read mixed $b
 * @property-read int $code
 * @property-read \Exception $previous
 */
class AccessorCase extends \Exception
{
	use PrototypeTrait;

	private $a;

	protected function get_a()
	{
		return $this->a;
	}

	private $b;

	protected function get_b()
	{
		return $this->b;
	}

	protected function get_code()
	{
		return $this->getCode();
	}

	protected function get_previous()
	{
		return $this->getPrevious();
	}

	/**
	 * @param string $a
	 * @param int $b
	 * @param string $message
	 * @param int $code
	 * @param \Exception|null $previous
	 */
	public function __construct($a, $b, $message, $code = 500, \Exception $previous = null)
	{
		$this->a = $a;
		$this->b = $b;

		parent::__construct($message, $code, $previous);
	}
}
