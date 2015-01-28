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

use ICanBoogie\GetterTrait;

/**
 * Exception thrown in attempt to access a method that is not defined.
 *
 * @property-read string $method The method that is not defined.
 * @property-read mixed $class The class of the instance on which the method was invoked.
 * @property-read object|null $instance Instance on which the method was invoked, or `null` if
 * only the class is available.
 */
class MethodNotDefined extends \BadMethodCallException
{
	use GetterTrait;

	private $method;

	protected function get_method()
	{
		return $this->method;
	}

	private $class;

	protected function get_class()
	{
		return $this->class;
	}

	private $instance;

	protected function get_instance()
	{
		return $this->instance;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $method The method that is not defined.
	 * @param string|object $class The name of the class or one of its instances.
	 * @param string|null $message If `null` a message is formatted with $method and $class.
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($method, $class, $message = null, $code = 500, \Exception $previous = null)
	{
		if (is_object($class))
		{
			$this->instance = $class;
			$class = get_class($class);
		}

		$this->method = $method;
		$this->class = $class;

		$message = $message ?: \ICanBoogie\format('The method %method is not defined by the prototype of class %class.', [

			'method' => $method,
			'class' => $class

		]);

		parent::__construct($message, $code, $previous);
	}
}
