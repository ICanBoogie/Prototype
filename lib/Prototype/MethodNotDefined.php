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

use ICanBoogie\Accessor\AccessorTrait;
use function get_class;
use function ICanBoogie\format;
use function is_object;

/**
 * Exception thrown in attempt to access a method that is not defined.
 *
 * @property-read string $method The method that is not defined.
 * @property-read string $class The class of the instance on which the method was invoked.
 * @property-read object|null $instance Instance on which the method was invoked, or `null` if
 * only the class is available.
 */
class MethodNotDefined extends \BadMethodCallException
{
	use AccessorTrait;

	/**
	 * @var string
	 * @uses get_method
	 */
	private $method;

	private function get_method(): string
	{
		return $this->method;
	}

	/**
	 * @var string
	 * @uses get_class
	 */
	private $class;

	private function get_class(): string
	{
		return $this->class;
	}

	/**
	 * @return object|null
	 * @uses get_instance
	 */
	private $instance;

	private function get_instance(): ?object
	{
		return $this->instance;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $method The method that is not defined.
	 * @param string|object $class_or_instance The name of the class or one of its instances.
	 * @param string|null $message If `null` a message is formatted with $method and $class.
	 * @param int $code
	 * @param \Throwable $previous
	 */
	public function __construct(string $method, $class_or_instance, string $message = null, int $code = 500, \Throwable $previous = null)
	{
		$class = $class_or_instance;

		if (\is_object($class_or_instance))
		{
			$this->instance = $class_or_instance;
			$class = \get_class($class_or_instance);
		}

		$this->method = $method;
		$this->class = $class;

		parent::__construct($message ?: $this->format_message($method, $class), $code, $previous);
	}

	private function format_message(string $method, string $class): string
	{
		return format('The method %method is not defined by the prototype of class %class.', [

			'method' => $method,
			'class' => $class

		]);
	}
}
