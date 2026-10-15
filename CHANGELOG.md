# CHANGELOG

## v7.0.0

### New Requirements

- PHP 8.4+
- `icanboogie/config` ^7.0

### New features

- `ConfigBuilder` implements `ICanBoogie\Config\Builder`, its fragments are named `prototype`. It can be used directly
  as a configuration builder, `icanboogie/bind-prototype` no longer wraps it.

### Deprecated Features

None

### Backward Incompatible Changes

None

### Other Changes

- Replaced PHP Code Sniffer with PHP-CS-Fixer.
- Tooling updated: PHPUnit 13.4, PHPStan 2.3, CI tests the lowest and the latest dependencies.
- PHPStan runs at level `max` on `lib` and `tests`, with a baseline for `tests`. The `@phpstan-ignore` comments name the
  error they ignore and why.
- The tests use `#[DataProvider]` attributes and test stubs, as PHPUnit 13 requires.
- Tests run on PHP 8.4 and 8.5.



## v6.0.0

### New Requirements

- PHP 8.1+

### New features

- Added `Prototype::has_method()`.
- Added `Prototype::add_method()`.
- Added `Prototype::call()`.

### Backward Incompatible Changes

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

- Removed the `prototype` property from `PrototypeTrait`.

### Deprecated Features

None

### Other Changes

- `Prototyped::from()` returns `static`.
