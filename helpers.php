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
 * Get public object variables.
 *
 * @param mixed $object
 *
 * @return array
 */
function get_public_object_vars($object)
{
	return get_object_vars($object);
}
