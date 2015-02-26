<?php

namespace ICanBoogie\Prototype;

class HooksTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_synthesize_config_invalid()
	{
		Hooks::synthesize_config([

			[

				'prototypes' => [

					'invalid_method' => function () {}

				]

			]

		]);
	}

	public function test_synthesize_config()
	{
		$one_one = function() {};
		$one_two = function() {};
		$one_one_override = function() {};
		$one_three = function() {};
		$two_one = function() {};

		$synthesized = Hooks::synthesize_config([

			[

				'prototypes' => [

					'one::one' => $one_one,
					'one::two' => $one_two

				]

			],

			[

				'prototypes' => [

					'one::one' => $one_one_override,
					'one::three' => $one_three

				]

			],

			[

				uniqid() => [

					uniqid()

				]

			],

			[

				'prototypes' => [

					'two::one' => $two_one

				]

			]

		]);

		$this->assertSame([

			'one' => [

				'one' => $one_one_override,
				'two' => $one_two,
				'three' => $one_three

			],

			'two' => [

				'one' => $two_one

			]

		], $synthesized);
	}
}