<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie\Prototype;

use ICanBoogie\Prototype\Config;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\PrototypedCases\Sample;
use Test\ICanBoogie\SampleHooks;
use Test\ICanBoogie\SetStateHelper;

final class ConfigTest extends TestCase
{
	public function test_export(): void
	{
		$config = new Config([
			Sample::class => [ 'get_message' => [ SampleHooks::class, 'sample_method' ] ],
		]);

		$actual = SetStateHelper::export_import($config);

		$this->assertEquals($config, $actual);
	}
}
