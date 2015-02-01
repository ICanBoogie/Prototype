# Prototype

[![Release](https://img.shields.io/github/release/ICanBoogie/Prototype.svg)](https://github.com/ICanBoogie/Prototype/releases)
[![Build Status](https://img.shields.io/travis/ICanBoogie/Prototype/master.svg)](http://travis-ci.org/ICanBoogie/Prototype)
[![HHVM](https://img.shields.io/hhvm/icanboogie/prototype.svg)](http://hhvm.h4cc.de/package/icanboogie/prototype)
[![Code Quality](https://img.shields.io/scrutinizer/g/ICanBoogie/Prototype/master.svg)](https://scrutinizer-ci.com/g/ICanBoogie/Prototype)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Prototype/master.svg)](https://coveralls.io/r/ICanBoogie/Prototype)
[![Packagist](https://img.shields.io/packagist/dt/icanboogie/prototype.svg)](https://packagist.org/packages/icanboogie/prototype)

The **Prototype** package allows methods of classes using the [PrototypeTrait][] to be defined at
runtime, and since the [Accessor package][] is used, this also includes getters and setters.





## Defining methods at runtime

Methods can be defined at runtime using the _prototype_ of a class. They are immediately
available to every instance of the class and are inherited by the sub-classes of that class.

```php
<?php

use ICanBoogie\PrototypeTrait;

class Cat { use PrototypeTrait; }
class OtherCat extends Cat {}
class FierceCat extends Cat {}

$cat = new Cat();
$other_cat = new OtherCat();
$fierce_cat = new FierceCat();
$second_fierce_cat = new FierceCat();

// define the 'meow' prototype method for Cat class

$cat->prototype['meow'] = function(Cat $cat) {

	return 'Meow';

};

// override the 'meow' prototype method for FierceCat class

$fierce_cat->prototype['meow'] = function(Cat $cat) {

	return 'MEOOOW !';

};

echo $cat->meow();               // Meow
echo $other_cat->meow();         // Meow
echo $fierce_cat->meow();        // MEOOOW !
echo $second_fierce_cat->meow(); // MEOOOW !
```





### Defining getters and setters at runtime

Because getters and setters are methods too, they are defined just like regular methods.

```php
<?php

use ICanBoogie\PrototypeTrait;

class TimeObject
{
	use PrototypeTrait;

	public $seconds;
}

$time = new Time;

$time->prototype['set_minutes'] = function(Time $time, $minutes) {

	$time->seconds = $minutes * 60;

};

$time->prototype['get_minutes'] = function(Time $time, $minutes) {

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
use ICanBoogie\ActiveRecord;

class Article
{
	use PrototypeTrait;
	
	public $image_id;
}

$prototype = Prototype::from('Article');

$prototype['get_image'] = function(Article $target)
{
	static $model;

	if (!$model)
	{
		$model = ActiveRecord\get_model('images');
	}

	return $target->image_id ? $model[$target->image_id] : null;
};

$article = new Article();
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

Prototype::from('Node')['url'] = function($node, $type) {

	return "/path/to/$type.html";

};

$node = new Node;
$news = new News;

echo $node->url('madonna'); // /path/to/madonna.html
echo $news->url('madonna'); // /path/to/another/madonna.html
```





## Defining prototypes methods

Prototype methods can be defined using a global configuration; through the `prototype` property
of an `Object` instance; or using the `Prototype` instance associated with classes using
the [PrototypeTrait][] trait.





### Defining prototypes methods using a global configuration

All prototypes can be configured using a single global configuration. For each class you can
define the methods that the prototype implements.

The following example demonstrate how the `meow()` method is defined for instances of the `Cat`
and `FierceCat` classes. Although they are defined using closure in the example, methods can be
defined using any callable such as `"Website\Hooks::cat_meow"`.

```php
<?php

ICanBoogie\Prototype::configure
([

	'Cat' => [

		'meow' => function(Cat $cat) {

			return 'Meow';

		}
	],

	'FierceCat' => [

		'meow' => function(Cat $cat) {

			return 'MEOOOW !';

		}
	]

]);
```





### Defining prototypes methods through the `prototype` property

As we have seen in previous examples, prototype methods can be defined using
the `prototype` property:

```php
<?php

use ICanBoogie\PrototypeTrait;

class Cat
{
	use PrototypeTrait;
}

$cat = new Cat;

$cat->prototype['meow'] = function(Cat $cat)
{
	return 'Meow';
};

echo $cat->meow();
```





### Defining prototypes methods using a prototype instance

Prototype methods can be defined using the `Prototype` instance of a class:

```php
<?php

use ICanBoogie\Prototype;

$prototype = Prototype::from('Cat');

$prototype['meow'] = function(Cat $cat)
{
	return 'Meow';
};
```





### Defining prototypes methods using config fragments

When using the [ICanBoogie](http://icanboogie.org/) framework, prototypes methods can be defined
using the `hooks` config and the `prototypes` namespace:

```php
<?php

// config/hooks.php

return [

	'prototypes' => [

		'Icybee\Modules\Pages\Page::my_additional_method' => 'Website\Hooks::my_additional_method',
		'Icybee\Modules\Pages\Page::lazy_get_my_property' => 'Website\Hooks::lazy_get_my_property'

	]
];
```





## Getting an array representation of an object

An array representation of an `Object` instance can be obtained using the `to_array()` method. Only
public and façade properties are exported.

```php
<?php

use ICanBoogie\Object

class A extends Object
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

The `Object::from()` method creates an instance from an array of properties:

```php
<?php

class A extends Object
{
	private $a;
	protected $b;
	public $c;
}

$a = A::from([ 'a' => 1, 'b' => 2, 'c' => 3 ]);
```

Instances are created in the same fashion [PDO](http://www.php.net/manual/en/book.pdo.php)
creates instances when fetching objects using the `FETCH_CLASS` mode, that is the properties
of the instance are set *before* its constructor is invoked.

`Object` sub-classes might want to override the `Object::from` method to allow creating
instances from different kind of sources, just like the `Operation::from` method creates an
`Operation` instance from a `Request`:

```php
<?php

namespace ICanBoogie;

class Operation
{
	static public function from($properties=null, array $construct_args=[], $class_name=null)
	{
		if ($properties instanceof Request)
		{
			return static::from_request($properties);
		}

		return parent::from($properties, $construct_args, $class_name);
	}
}
```



 

## Using the Prototype trait

The prototype features are available as a [trait](http://php.net/traits). Any class can implement
them simply by using the [PrototypeTrait][] trait.

```php
<?php

use ICanBoogie\PrototypeTrait;

class MyException extends Exception
{
	use PrototypeTrait;

	private $a;
	private $b;

	public function __construct($a, $b, $message, $code=500, Exception $previous=null)
	{
		$this->a = $a;
		$this->b = $b;

		parent::__construct($message, $code, $previous);
	}

	protected function get_a()
	{
		return $this->a;
	}

	protected function get_b()
	{
		return $this->b;
	}

	protected function get_code()
	{
		return $this->getCode();
	}
}

$e = new MyException(12, 34, "Damned!", 404);

echo $e->a;    // 12
echo $e->b;    // 34
echo $e->code; // 404

$e->a = 34; // throws PropertyNotWritable
```





## Exceptions

The following exceptions are defined:

- [MethodNotDefined][]: Exception thrown in attempt to access a method that is not defined.
- [MethodOutOfScope][]: Exception thrown in attempt to invoke a method that is out of scope.





## ICanBoogie autoconfig

The package supports the autoconfig feature of the framework [ICanBoogie][] and provides a
config constructor for the "prototypes" config:

```php
$app = ICanBoogie\boot();
$app->configs['prototypes']; // The "prototypes" config
```





## Patching

The `last_chance_get()` and `last_chance_set()` helpers are called in attempt to get or set the
value of a property after no adequate getter or setter could be found to do so. If they fail, an
exception is usually thrown.

The functions defined by the Prototype package don't do anything, but they can be patched to
provide different mechanisms. For instance, the [ICanBoogie](http://icanboogie.org/)
framework patches the `last_chance_get()` helper to try and get the requested property
using an event:

```php
<?php

namespace ICanBoogie;

Prototype\Helpers::patch('last_chance_get', function(Object $target, $property, &$success)
{
	$event = new Object\GetPropertyEvent($target, [ 'property' => $property ]);

	#
	# The operation is considered a success if the `value` property exists in the event
	# object. Thus, even a `null` value is considered a success.
	#

	if (!property_exists($event, 'value'))
	{
		return;
	}

	$success = true;

	return $event->value;
});
```





----------





## Requirements

The package requires PHP 5.4 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/):

```
$ composer require icanboogie/prototype
```

The following packages are required, you might want to check them out:

* [icanboogie/common](https://packagist.org/packages/icanboogie/common)






### Cloning the repository

The package is [available on GitHub](https://github.com/ICanBoogie/Prototype), its repository can
be cloned with the following command line:

	$ git clone https://github.com/ICanBoogie/Prototype.git





## Documentation

The package is documented as part of the [ICanBoogie][] framework
[documentation](http://icanboogie.org/docs/). You can generate the documentation for the package
and its dependencies with the `make doc` command. The documentation is generated in the `docs`
directory. [ApiGen](http://apigen.org/) is required. The directory can later by cleaned with
the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite.
The directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/ICanBoogie/Prototype/master.svg)](http://travis-ci.org/ICanBoogie/Prototype)
[![Code Coverage](https://img.shields.io/coveralls/ICanBoogie/Prototype/master.svg)](https://coveralls.io/r/ICanBoogie/Prototype)





## License

The package is licensed under the New BSD License. See the [LICENSE](LICENSE) file for details.





[Accessor package]: https://github.com/ICanBoogie/Accessor
[ICanBoogie]: http://icanboogie.org
[MethodNotDefined]: http://icanboogie.org/docs/class-ICanBoogie.Prototype.MethodNotDefined.html
[MethodOutOfScope]: http://icanboogie.org/docs/class-ICanBoogie.Prototype.MethodOutOfScope.html
[Object]: http://icanboogie.org/docs/class-ICanBoogie.Object.html
[PropertyNotWritable]: http://icanboogie.org/docs/class-ICanBoogie.PropertyNotWritable.html
[PropertyNotReadable]: http://icanboogie.org/docs/class-ICanBoogie.PropertyNotReadable.html
[PrototypeTrait]: http://icanboogie.org/docs/class-ICanBoogie.PrototypeTrait.html
[ToArray]: http://icanboogie.org/docs/class-ICanBoogie.ToArray.html
[ToArrayRecursive]: http://icanboogie.org/docs/class-ICanBoogie.ToArrayRecursive.html
