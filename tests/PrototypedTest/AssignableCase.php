<?php

namespace ICanBoogie\PrototypedTest;

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
