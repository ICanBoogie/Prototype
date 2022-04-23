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

class SampleMethodNotDefined
{
    use PrototypeTrait;

    public function public_method()
    {
        return __FUNCTION__;
    }

    protected function protected_method()
    {
        return __FUNCTION__;
    }

    private function private_method()
    {
        return __FUNCTION__;
    }
}
