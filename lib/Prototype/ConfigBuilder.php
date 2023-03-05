<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Prototype;

final class ConfigBuilder
{
    /**
     * @var array<class-string, array<string, callable>>
     *     Where _key_ is a target class and _value_ is an array of method bindings,
     *     where _key_ is a method and _value_ a callable.
     */
    private array $bindings = [];

    /**
     * Build the configuration.
     */
    public function build(): Config
    {
        return new Config($this->bindings);
    }

    /**
     * @param class-string $target_class The class that will receive the method extension.
     * @param string $method The name of the method to add to the class.
     * @param callable $callable The handler for the method.
     *
     * @return $this
     */
    public function bind(string $target_class, string $method, callable $callable): self
    {
        $this->bindings[$target_class][$method] = $callable;

        return $this;
    }
}
