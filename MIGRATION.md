# Migration

## v5.x to v6.0

### New requirements

- PHP 8.1+

### Breaking changes

- `Prototype::bind()` requires a `Config` objects instead of an array.

```php
<?php

namespace ICanBoogie;

Prototype::bind([
	Cat::class => [
		'meow' => fn(Cat $cat) => 'Meow'
    ],

	FierceCat::class => [
		'meow' => fn(FierceCat $cat) => 'MEOOOW !'
	]
]);

```

```php
<?php

namespace ICanBoogie;

use ICanBoogie\Prototype\ConfigBuilder;use ICanBoogie\PrototypeTest\FierceCat;

$config = (new ConfigBuilder())
    ->bind(Cat::class, 'meom', fn(Cat $cat) => 'Meow')
    ->bind(FierceCat::class, 'meow', fn(FierceCat $cat) => 'MEOOOW !')
    ->build();

ICanBoogie\Prototype::bind($config);
```

- The parameter `$class_name` is no longer supported on `Prototyped::from()`.

### Other changes

- `Prototyped::from()` returns `static`.
