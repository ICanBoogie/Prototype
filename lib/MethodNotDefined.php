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
 * This exception is thrown when one tries to access an undefined prototype method.
 */
class MethodNotDefined extends \BadMethodCallException
{
	public function __construct($message, $code=500, \Exception $previous=null)
	{
		if (is_array($message))
		{
			$message = sprintf('Method "%s" is not defined by the prototype of class "%s".', $message[0], $message[1]);
		}

		parent::__construct($message, $code, $previous);
	}
}