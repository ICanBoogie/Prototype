<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4("ICanBoogie\\PrototypedTest\\", __DIR__ . '/PrototypedTest');
$loader->addPsr4("ICanBoogie\\PrototypeTraitTest\\", __DIR__ . '/PrototypeTraitTest');
$loader->addPsr4("ICanBoogie\\Prototype\\", __DIR__ . '/Prototype');

date_default_timezone_set('Europe/Paris');
