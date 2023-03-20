<?php

namespace Test\ICanBoogie\PrototypedCases;

use AllowDynamicProperties;
use ICanBoogie\Prototyped;

#[AllowDynamicProperties]
class SampleA extends Prototyped
{
    public int $seconds = 0;
}
