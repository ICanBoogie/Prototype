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

class ToArrayWithFacadePropertyCase extends Prototyped
{
    public $a;
    protected $b;
    private $c;

    protected function get_c()
    {
        return $this->c;
    }

    protected function set_c($value)
    {
        $this->c = $value;
    }

    public function __construct($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}
