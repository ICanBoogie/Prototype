<?php

namespace ICanBoogie\PrototypedTest;

use ICanBoogie\Prototyped;

final class AssignableCase extends Prototyped
{
    public const PROPERTY_ID = 'id';
    public const PROPERTY_COMMENT = 'comment';
    public const PROPERTY_COLOR = 'color';

    public $id;
    public $comment;
    public $color;

    public static function assignable(): array
    {
        return parent::assignable() + [

                self::PROPERTY_COMMENT,
                self::PROPERTY_COLOR,

            ];
    }
}
