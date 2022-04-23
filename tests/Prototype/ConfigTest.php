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

use ICanBoogie\Sample;
use ICanBoogie\SampleHooks;
use ICanBoogie\SetStateHelper;
use PHPUnit\Framework\TestCase;

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
