# Prototype

[![Build Status][]](http://travis-ci.org/ICanBoogie/Prototype) [![Code Quality][]](https://scrutinizer-ci.com/g/ICanBoogie/Prototype/?branch=master)

With the `Object` and `Prototype` classes, provided by the __Prototype__ package, you can easily
implement getters and setters as well as define methods, getters and setters at runtime. These
getters and setters are always mapped to a magic property and can be used to create façade
to properties, inject dependencies, reverse application control, lazy load resources,
or create read-only and write-only properties.

Note: Although the following examples extend the [Object][] class to demonstrate the capability
of the prototype features, these features are available as a trait and thus can be used
by any class, without requiring direct inheritance from [Object][].





## Defining getters and setters

A getter is a method that gets the value of a specific property. A setter is a method that sets
the value of a specific property. You can define getters and setters on classes using
the [PrototypeTrait][] trait, such as the [Object][] class, with either the inheritance
of the class or the prototype associated with it.

Using a combination of getters, setters, properties, and properties visibility you can create
read-only properties, write-only properties, virtual properties, façade properties, or implement
lazy loading.

__Something to remember__: The getter or the setter is only called when the corresponding property
is not accessible. This is most notably important to remember when using lazy loading, which
creates the associated property once called.





### Read-only properties

Read-only properties are created by defining a getter. A [PropertyNotWritable][] exception is
thrown in attempt to set a read-only property.

The following example demonstrates how a `property` read-only property can be implemented:

```php
<?php

class ReadOnlyProperty extends \ICanBoogie\Object
{
	protected function get_property()
	{
		return 'value';
	}
}

$a = new ReadOnlyProperty;
echo $a->property; // value
$a->property = null; // throws ICanBoogie\PropertyNotWritable
```

An existing property can be made read-only by setting its visibility to `protected` or `private`:

```php
<?php

class ReadOnlyProperty extends \ICanBoogie\Object
{
	private $property = 'value';

	protected function get_property()
	{
		return $this->property;
	}
}

$a = new ReadOnlyProperty;
echo $a->property; // value
$a->property = null; // throws ICanBoogie\PropertyNotWritable
```





### Write-only properties

Write-only properties are created by defining a setter. A [PropertyNotReadable][] exception is
thrown in attempt to get a write-only property.

The following example demonstrates how a `property` write-only property can be implemented:

```php
<?php

class WriteOnlyProperty extends \ICanBoogie\Object
{
	protected function set_property($value)
	{
		// …
	}
}

$a = new WriteOnlyProperty;
$a->property = 'value';
echo $a->property; // throws ICanBoogie\PropertyNotReadable
```

An existing property can be made write-only by setting its visibility to `protected` or `private`:

```php
<?php

class WriteOnlyProperty extends \ICanBoogie\Object
{
	private $property = 'value';

	protected function set_property($value)
	{
		$this->property = $value;
	}
}

$a = new WriteOnlyProperty;
$a->property = 'value';
echo $a->property; // throws ICanBoogie\PropertyNotReadable
```





### Virtual properties

A virtual property is created by defining both its getter and setter. Such a property
can provide an interface to another property or data structure.

The following example demonstrates how a `minutes` virtual property can be implemented as an
interface to a `seconds` property.

```php
<?php

use ICanBoogie\Object;

class Time extends Object
{
	public $seconds;

	protected function set_minutes($minutes)
	{
		$this->seconds = $minutes * 60;
	}

	protected function get_minutes()
	{
		return $this->seconds / 60;
	}
}

$time = new Time;
$time->seconds = 120;
echo $time->minutes; // 2

$time->minutes = 4;
echo $time->seconds; // 240
```





### Façade properties

Sometimes you want to be able to manage the type of a property, what you can store, what you
can retrieve, the most transparently possible. This can be achieved with _façade properties_.

Façade properties are setup by defining a private property along with its getter and setter.
The following example demonstrates how a `created_at` property is created, that can be set
to a `DateTime` instance, a string, an integer or null, while always returning
a `DateTime` instance.

```php
<?php

use ICanBoogie\DateTime;

class Article extends \ICanBoogie\Object
{
	private $created_at;

	protected function set_created_at($datetime)
	{
		$this->created_at = $datetime;
	}

	protected function get_created_at()
	{
		$datetime = $this->created_at;

		if ($datetime instanceof DateTime)
		{
			return $datetime;
		}

		return $this->created_at = ($datetime === null) ? DateTime::none() : new DateTime($datetime, 'utc');
	}
}
```






#### Façade properties are exported

The value of façade properties is exported when the instance is serialized or transformed into an
array.

The following example demonstrates how a `created_at` property is exported both by `__sleep()`
(which is invoked during `serialize()`) and `to_array()`.

```php
<?php

$a = new Article;
$a->created_at = 'now';

echo get_class($a->created_at);                      // ICanBoogie\DateTime
echo array_key_exists('created_at', $a->__sleep());  // true
echo array_key_exists('created_at', $a->to_array()); // true

serialize($a);
// O:7:"Article":1:{s:19:"\x00Article\x00created_at";O:19:"ICanBoogie\DateTime":3:{s:4:"date";s:19:"2013-11-18 22:20:00";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}
```

Notice how the `created_at` property is exported during `serialize()` while preserving its
visibility.





### Lazy loading

Properties that only create their value once they have been accessed can also be defined, they
are often used for lazy loading.

In the following example, the `lazy_get_pseudo_uniqid()` getter returns a unique value, but because the 
`pseudo_uniqid` property is created with the `public` visibility after the getter was called,
any subsequent access to the property returns the same value:

```php
<?php

use ICanBoogie\Object;

class PseudoUniqID extends Object
{
	protected function lazy_get_pseudo_uniqid()
	{
		return uniqid();
	}
}

$a = new PseudoUniqID;

echo $a->pseudo_uniqid; // 5089497a540f8
echo $a->pseudo_uniqid; // 5089497a540f8
```

Of course, unsetting the created property resets the process.

```php
<?php

unset($a->pseudo_uniqid);

echo $a->pseudo_uniqid; // 508949b5aaa00
echo $a->pseudo_uniqid; // 508949b5aaa00
```





## More examples

### Providing a default value until a property is set

Because getters are invoked when their corresponding property is inaccessible, and because
an unset property is inaccessible, it is possible to define getters to provide default values
until a value is actually set.

The following example demonstrates how a default value can be provided when the value of a
property is missing. When the value of the `slug` property is empty the property is unset,
making it inaccessible. Thus, until the property is actually set, the getter will be invoked
and will return a default value created from the `title` property.

```php
<?php

use ICanBoogie\Object;

class Article extends Object
{
	public $title;
	public $slug;

	public function __construct($title, $slug=null)
	{
		$this->title = $tile;

		if ($slug)
		{
			$this->slug = $slug;
		}
		else
		{
			unset($this->slug);
		}
	}

	protected function get_slug()
	{
		return \ICanBoogie\normalize($this->slug);
	}
}
```





## Defining methods at runtime

Methods can be defined at runtime using the _prototype_ of a class. They are immediately
available to every instance of the class and are inherited by the sub-classes of that class.

```php
<?php

use ICanboogie\Object;

class Cat extends Object {}
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

use ICanBoogie\Object;

class Time extends Object
{
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

use ICanBoogie\Object;
use ICanBoogie\ActiveRecord;

class Article extends Object
{
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

use ICanBoogie\Object;

class Cat {}

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

class A extends \ICanBoogie\Object
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





## ICanBoogie auto-config

The package supports the auto-config feature of the framework [ICanBoogie][] and provides a
config constructor for the "prototypes" config:

```php
$core = \ICanBoogie\boot();

$core->configs['prototypes']; // The "prototypes" config
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

[![Build Status][]](https://travis-ci.org/ICanBoogie/Prototype)





## License

The package is licensed under the New BSD License. See the [LICENSE](LICENSE) file for details.





[Build Status]: https://travis-ci.org/ICanBoogie/Prototype.svg?branch=master
[Code Quality]: https://scrutinizer-ci.com/g/ICanBoogie/Prototype/badges/quality-score.png?b=master
[ICanBoogie]: http://icanboogie.org
[MethodNotDefined]: http://icanboogie.org/docs/class-ICanBoogie.Prototype.MethodNotDefined.html
[MethodOutOfScope]: http://icanboogie.org/docs/class-ICanBoogie.Prototype.MethodOutOfScope.html
[Object]: http://icanboogie.org/docs/class-ICanBoogie.Object.html
[PropertyNotWritable]: http://icanboogie.org/docs/class-ICanBoogie.PropertyNotWritable.html
[PropertyNotReadable]: http://icanboogie.org/docs/class-ICanBoogie.PropertyNotReadable.html
[PrototypeTrait]: http://icanboogie.org/docs/class-ICanBoogie.PrototypeTrait.html
[ToArray]: http://icanboogie.org/docs/class-ICanBoogie.ToArray.html
[ToArrayRecursive]: http://icanboogie.org/docs/class-ICanBoogie.ToArrayRecursive.html
