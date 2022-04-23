<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\PrototypedCases;

use ICanBoogie\Prototyped;

class AssignableCase extends Prototyped
{
	const PROPERTY_ID = 'id';
	const PROPERTY_COMMENT = 'comment';
	const PROPERTY_COLOR = 'color';

	public $id;
	public $comment;
	public $color;

	static public function assignable(): array
	{
		return parent::assignable() + [

			self::PROPERTY_COMMENT,
			self::PROPERTY_COLOR,

		];
	}
}
