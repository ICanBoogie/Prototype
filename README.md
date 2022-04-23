# Prototype

[![Release](https://img.shields.io/packagist/v/ICanBoogie/Prototype.svg)](https://packagist.org/packages/icanboogie/prototype)
[![Build Status](https://img.shields.io/github/workflow/status/ICanBoogie/Prototype/test)](https://github.com/ICanBoogie/Prototype/actions?query=workflow%3Atest)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Prototype/master.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Prototype)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Prototype/master.svg)](https://coveralls.io/r/ICanBoogie/Prototype)
[![Packagist](https://img.shields.io/packagist/dt/icanboogie/prototype.svg)](https://packagist.org/packages/icanboogie/prototype)

The **icanboogie/prototype** package allows methods of classes using the [PrototypeTrait][] to be
defined at runtime, and since the [icanboogie/accessor][] package is used, this also includes
getters and setters.



#### Installation

```bash
composer require icanboogie/prototype
```



## Defining methods at runtime

Methods can be defined at runtime using the _prototype_ of a class. They are immediately
available to every instance of the class and are inherited by the sub-classes of that class.

```php
<?php

use ICanBoogie\Prototype;
use ICanBoogie\PrototypeTrait;

class Cat { use PrototypeTrait; }
class OtherCat extends Cat {}
class FierceCat extends Cat {}

$cat = new Cat;
$other_cat = new OtherCat;
$fierce_cat = new FierceCat;
$second_fierce_cat = new FierceCat;

// define the 'meow' prototype method for Cat class
Prototype::from(Cat::class)['meow'] = fn(Cat $cat) => 'Meow';

// override the 'meow' prototype method for FierceCat class
Prototype::from(FierceCat::class)['meow'] = fn(Cat $cat) => 'MEOOOW !';

echo $cat->meow();               // Meow
echo $other_cat->meow();         // Meow
echo $fierce_cat->meow();        // MEOOOW !
echo $second_fierce_cat->meow(); // MEOOOW !
```





### Defining getters and setters at runtime

Because getters and setters are methods too, they are defined just like regular methods.

```php
<?php

use ICanBoogie\Prototype;
use ICanBoogie\PrototypeTrait;

class TimeObject
{
	use PrototypeTrait;

	public $seconds;
}

$time = new Time;
$prototype = Prototype::from(Time::class);

$prototype['set_minutes'] = function(Time $time, $minutes) {

	$time->seconds = $minutes * 60;

};

$prototype['get_minutes'] = function(Time $time, $minutes) {

	return $time->seconds / 60;

};

$time->seconds = 120;
echo $time->minutes; // 2

$time->minutes = 4;
echo $time->seconds; // 240
```





## Dependency injection, inversion of control

Dependency injection and inversion of control can be implemented using prototype _lazy_ getters.

The following example demonstrates how a magic `image` property can be defined to lazy load a
record from an ActiveRecord model.

```php
<?php

use ICanBoogie\Prototype;
use ICanBoogie\PrototypeTrait;

class Article
{
	use PrototypeTrait;

	public $image_id;
}

// …

Prototype::from(Article::class)['get_image']
    = fn(Article $target) => $image_model[$target->image_id] ?? null;

$article = new Article;
$article->image_id = 12;
echo $article->image->nid; // 12
```





## Prototype methods and `parent::`

Prototype methods can be overridden and work with `parent::` calls just like regular methods.

In the following example the prototype method `url()` is added to the class `Node` and
overridden in the class `News`, still `parent::` can be used from `News`:

```php
<?php

use ICanBoogie\Prototype;
use ICanBoogie\PrototypeTrait;

class Node
{
	use PrototypeTrait;
}

class News extends Node
{
	public function url($type)
	{
		return parent::url("another/$type");
	}
}

Prototype::from(Node::class)['url'] = fn(Node $node, string $type) => "/path/to/$type.html";

$node = new Node;
$news = new News;

echo $node->url('madonna'); // /path/to/madonna.html
echo $news->url('madonna'); // /path/to/another/madonna.html
```





## Defining prototypes methods

Prototype methods can be defined using a global configuration; through the `prototype` property
of an `Prototyped` instance; or using the `Prototype` instance associated with classes using
the [PrototypeTrait][] trait.





### Defining prototypes methods using a global configuration

All prototypes can be configured using a single global configuration. For each class you can
define the methods that the prototype implements.

The following example demonstrate how the `meow()` method is defined for instances of the `Cat`
and `FierceCat` classes. Although they are defined using closure in the example, methods can be
defined using any callable such as `"App\Hooks::cat_meow"`.

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





### Defining prototypes methods through the `prototype` property

Prototype methods may be defined using the `prototype` property:

```php
<?php

use ICanBoogie\PrototypeTrait;

class Cat
{
	use PrototypeTrait;
}

$cat = new Cat;

$cat->prototype['meow'] = fn(Cat $cat) => 'Meow';

echo $cat->meow();
```





### Defining prototypes methods using a prototype instance

Prototype methods may be defined using the `Prototype` instance of a class:

```php
<?php

use ICanBoogie\Prototype;

Prototype::from(Cat::class)['meow'] = fn(Cat $cat) => 'Meow';
```





## Getting an array representation of an object

An array representation of an `Prototyped` instance can be obtained using the `to_array()` method. Only
public and façade properties are exported.

```php
<?php

use ICanBoogie\Prototyped;

class A extends Prototyped
{
	public $a;
	protected $b;
	private $c;

	public function __construct($a, $b, $c)
	{
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
	}

	protected function get_c()
	{
		return $this->c;
	}

	protected function set_c($value)
	{
		$this->c = $value;
	}
}

$a = new A('a', 'b', 'c');

var_dump($a->to_array());

// array(2) {
//  ["a"]=>
//  string(1) "a"
//  ["c"]=>
//  string(1) "c"
//}
```

As mentioned before, _façade_ properties are also exported. The `to_array()` method should be
overrode to alter this behavior.

Additionally the `to_array_recursive()` method can be used to recursively convert an instance
into an array, in which case all the instances of the tree implementing [ToArray][]
or [ToArrayRecursive][] are converted into arrays.





## Getting a JSON representation of an object

The `to_json()` method can be used to get a JSON representation of an object. The method is
really straight forward, it invokes `to_array_recursive()` and pass the result to
`json_encode()`.

```php
<?php

echo $a->to_json(); // {"a":"a","c":"c"}
```





## Creating an instance from an array of properties

The `Prototyped::from()` method creates an instance from an array of properties:

```php
<?php

class A extends Prototyped
{
	private $a;
	protected $b;
	public $c;
}

$a = A::from([ 'b' => 2, 'c' => 3 ]);
```

Instances are created in the same fashion [PDO](http://www.php.net/manual/en/book.pdo.php)
creates instances when fetching objects using the `FETCH_CLASS` mode, that is the properties
of the instance are set *before* its constructor is invoked.





## Exceptions

The following exceptions are defined:

- [MethodNotDefined][]: Exception thrown in attempt to access a method that is not defined.
- [MethodOutOfScope][]: Exception thrown in attempt to invoke a method that is out of scope.





----------





## Documentation

The package is documented as part of the [ICanBoogie][] framework [documentation][]. You can
generate the documentation for the package and its dependencies with the `make doc` command. The
documentation is generated in the `docs` directory. [ApiGen](http://apigen.org/) is required. The
directory can later by cleaned with the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [PHPUnit](https://phpunit.de/) and
[Composer](http://getcomposer.org/) need to be globally available to run the suite.
The command installs dependencies as required. The `make test-coverage` command runs test suite and
also creates an HTML coverage report in "build/coverage". The directory can later be cleaned with
the `make clean` command.

To ensure tests are running with the minimum requirements, it is advised to run them inside the
provided container. The container is started with the `make test-container` command. Once inside the
container, `make test` and `make test-coverage` can be used. Xdebug is available in the container
and ready for debugging, it uses the servername `icanboogie-tests` and the absolute path `/app`.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/ICanBoogie/Prototype/master.svg)](http://travis-ci.org/ICanBoogie/Prototype)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Prototype/master.svg)](https://coveralls.io/r/ICanBoogie/Prototype)



## License

**icanboogie/prototype** is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.



[PropertyNotWritable]:       https://icanboogie.org/api/common/1.2/class-ICanBoogie.PropertyNotWritable.html
[PropertyNotReadable]:       https://icanboogie.org/api/common/1.2/class-ICanBoogie.PropertyNotReadable.html
[ToArray]:                   https://icanboogie.org/api/common/1.2/class-ICanBoogie.ToArray.html
[ToArrayRecursive]:          https://icanboogie.org/api/common/1.2/class-ICanBoogie.ToArrayRecursive.html
[documentation]:             https://icanboogie.org/api/prototype/4.0/
[MethodNotDefined]:          https://icanboogie.org/api/prototype/4.0/class-ICanBoogie.Prototype.MethodNotDefined.html
[MethodOutOfScope]:          https://icanboogie.org/api/prototype/4.0/class-ICanBoogie.Prototype.MethodOutOfScope.html
[Prototyped]:                https://icanboogie.org/api/prototype/4.0/class-ICanBoogie.Prototyped.html
[PrototypeTrait]:            https://icanboogie.org/api/prototype/4.0/class-ICanBoogie.PrototypeTrait.html
[ICanBoogie]:                https://icanboogie.org
[icanboogie/accessor]:       https://github.com/ICanBoogie/Accessor
[icanboogie/bind-prototype]: https://github.com/ICanBoogie/bind-prototype
