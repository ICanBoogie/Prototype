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

/**
 * Exception thrown in attempt to access a method that is not defined.
 *
 * @property-read string $method The method that is not defined.
 * @property-read mixed $class The class of the instance on which the method was invoked.
 */
class MethodNotDefined extends \BadMethodCallException
{
	use \ICanBoogie\GetterTrait;

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

	public function __construct($method, $class, $message=null, $code=500, \Exception $previous=null)
	{
		$this->method = $method;
		$this->class = $class;

		$message = $message ?: \ICanBoogie\format('The method %method is not defined by the prototype of class %class.', [

			'method' => $method,
			'class' => $class

		]);

		parent::__construct($message, $code, $previous);
	}
}
