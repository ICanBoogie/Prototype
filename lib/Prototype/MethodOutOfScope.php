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

/**
 * Exception thrown in attempt to invoke a method that is out of scope.
 *
 * @property-read string $method The method that is out of scope.
 * @property-read object $instance The instance on which the method was invoked.
 */
class MethodOutOfScope extends \BadMethodCallException
{
	use AccessorTrait;

	/**
	 * @return string
	 * @uses get_method
	 */
	private $method;

	/**
	 * @return string
	 */
	private function get_method()
	{
		return $this->method;
	}

	/**
	 * @return object
	 * @uses get_instance
	 */
	private $instance;

	/**
	 * @return object|null
	 */
	protected function get_instance()
	{
		return $this->instance;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $method
	 * @param object $instance
	 * @param string|null $message
	 * @param int $code
	 * @param \Exception|null $previous
	 */
	public function __construct($method, $instance, $message = null, $code = 500, \Exception $previous = null)
	{
		$this->method = $method;
		$this->instance = $instance;

		parent::__construct($message ?: $this->format_message($method, $instance), $code, $previous);
	}

	/**
	 * Formats exception message.
	 *
	 * @param string $method
	 * @param object $instance
	 *
	 * @return string
	 */
	private function format_message($method, $instance)
	{
		return format('The method %method is out of scope for class %class.', [

			'method' => $method,
			'class' => get_class($instance)

		]);
	}
}
