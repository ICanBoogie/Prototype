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
use ICanBoogie\Accessor\AccessorTrait;
use Throwable;

use function get_class;
use function ICanBoogie\format;

/**
 * Exception thrown in attempt to invoke a method that is out of scope.
 *
 * @property-read string $method The method that is out of scope.
 * @property-read object $instance The instance on which the method was invoked.
 */
class MethodOutOfScope extends BadMethodCallException implements Exception
{
    /**
     * @uses get_method
     * @uses get_instance
     */
    use AccessorTrait;

    /**
     * @var string
     */
    private $method;

    private function get_method(): string
    {
        return $this->method;
    }

    /**
     * @var object
     */
    private $instance;

    private function get_instance(): object
    {
        return $this->instance;
    }

    /**
     * @inheritdoc
     */
    public function __construct(string $method, object $instance, string $message = null, Throwable $previous = null)
    {
        $this->method = $method;
        $this->instance = $instance;

        parent::__construct($message ?: $this->format_message($method, $instance), 0, $previous);
    }

    private function format_message(string $method, object $instance): string
    {
        return format('The method %method is out of scope for class %class.', [

            'method' => $method,
            'class' => get_class($instance)

        ]);
    }
}
