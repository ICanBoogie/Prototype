<?php

namespace Test\ICanBoogie\Prototype;

use ICanBoogie\Prototype\Config;
use ICanBoogie\Prototype\ConfigBuilder;
use PHPUnit\Framework\TestCase;

final class ConfigBuilderTest extends TestCase
{
    public function test_fragment_filename(): void
    {
        $this->assertSame('prototype', ConfigBuilder::get_fragment_filename());
    }

    public function test_build(): void
    {
        $callable = fn() => 'meow';
        $config = (new ConfigBuilder())
            ->bind(Cat::class, 'meow', $callable)
            ->build();

        $this->assertInstanceOf(Config::class, $config);
        $this->assertSame([ Cat::class => [ 'meow' => $callable ] ], $config->bindings);
    }
}
