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

use DateTime;
use ICanBoogie\Prototyped;

/**
 * @property DateTime $created_at
 */
class CreatedAtCase extends Prototyped
{
	private $created_at;

	protected function get_created_at()
	{
		$created_at = $this->created_at;

		if ($created_at instanceof DateTime)
		{
			return $created_at;
		}

		return new DateTime($created_at);
	}

	protected function set_created_at($created_at)
	{
		$this->created_at = $created_at;
	}
}
