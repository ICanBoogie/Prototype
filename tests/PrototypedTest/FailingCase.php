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

use AllowDynamicProperties;
use ICanBoogie\Prototyped;
use Throwable;

#[AllowDynamicProperties]
final class FailingCase extends Prototyped
{
    /**
     * @throws Throwable
     */
    public function __construct(Throwable $e)
    {
        throw $e;
    }
}
