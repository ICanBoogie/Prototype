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

use function get_class;
use function ICanBoogie\format;

/**
 * Exception thrown in an attempt to invoke a method that is out of scope.
 */
class MethodOutOfScope extends BadMethodCallException implements Exception
{
    /**
     * @param string $method
     *     The method that is out of scope.
     * @param object $instance
     *     The instance on which the method was invoked.
     */
    public function __construct(
        public readonly string $method,
        public readonly object $instance,
        ?string $message = null,
        ?Throwable $previous = null
    ) {
        $message ??= $this->format_message($method, $instance);

        parent::__construct($message, previous: $previous);
    }

    private function format_message(string $method, object $instance): string
    {
        return format('The method %method is out of scope for class %class.', [

            'method' => $method,
            'class' => $instance::class,

        ]);
    }
}
