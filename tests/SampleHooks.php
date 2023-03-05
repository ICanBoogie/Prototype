<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie;

use Test\ICanBoogie\PrototypedCases\Sample;

final class SampleHooks
{
    public static function sample_method(Sample $sample): string
    {
        return "sample result";
    }
}
