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

use BadMethodCallException;
use Throwable;

use function assert;
use function ICanBoogie\format;
use function is_object;
use function is_string;

/**
 * Exception thrown in attempt to access a method that is not defined.
 */
class MethodNotDefined extends BadMethodCallException implements Exception
{
    /**
     * @var class-string
     */
    public readonly string $class;

    /**
     * Instance on which the method was invoked, or `null` if it's not available.
     */
    public readonly ?object $instance;

    /**
     * @param string $method
     *     The method that is not defined.
     * @param class-string|object $class_or_instance
     *     The name of the class or one of its instances.
     * @param string|null $message
     *     If `null` a message is formatted with $method and $class.
     */
    public function __construct(
        public readonly string $method,
        string|object $class_or_instance,
        string $message = null,
        Throwable $previous = null
    ) {
        $class = $class_or_instance;

        if (is_object($class_or_instance)) {
            $this->instance = $class_or_instance;
            $class = $class_or_instance::class;
        }

        assert(is_string($class));

        $this->class = $class;

        parent::__construct($message ?? $this->format_message($method, $class), previous: $previous);
    }

    /**
     * @param class-string $class
     */
    private function format_message(string $method, string $class): string
    {
        return format('The method %method is not defined by the prototype of class %class.', [

            'method' => $method,
            'class' => $class

        ]);
    }
}
