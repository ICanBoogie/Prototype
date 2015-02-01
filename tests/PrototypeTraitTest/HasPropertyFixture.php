<?php

namespace ICanBoogie\Prototype\PrototypeTraitTest;

use ICanBoogie\PrototypeTrait;

class HasPropertyFixture
{
	use PrototypeTrait;

	public $public;
	protected $protected;
	private $private;
}
