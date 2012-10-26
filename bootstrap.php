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
 * Version string of the ICanBoogie\Prototype package.
 *
 * @var string
 */
const VERSION = '1.0.0 (2012-10-26)';

/**
 * The ROOT directory of the ICanBoogie\Prototype package.
 *
 * @var string
 */
defined('ICanBoogie\Prototype\ROOT') or define('ICanBoogie\Prototype\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/*
 * Bootstrap
 */
require_once ROOT . 'lib/helpers.php';