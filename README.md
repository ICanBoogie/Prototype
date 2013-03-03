# Prototype [![Build Status](https://secure.travis-ci.org/ICanBoogie/Prototype.png?branch=master)](http://travis-ci.org/ICanBoogie/Prototype)

With the `Object` and `Prototype` classes, provided by the __Prototype__ package, you can easily
implement getters and setters as well as define methods, getters and setters at runtime. These
getters and setters are always mapped to a magic property and can be used to inject dependencies,
reverse application control, lazy load resources, or create read-only and write-only properties.




## Requirements

The package requires PHP 5.3 or later.  
The package [icanboogie/common](https://packagist.org/packages/icanboogie/common) is required.





## Installation

The recommended way to install this package is through [composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
    "minimum-stability": "dev",
    "require": {
		"icanboogie/prototype": "*"
    }
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/ICanBoogie/Prototype), its repository can
be cloned with the following command line:

	$ git clone git://github.com/ICanBoogie/Prototype.git
	




## Documentation

The package is documented as part of the [ICanBoogie](http://icanboogie.org/) framework
[documentation](http://icanboogie.org/docs/). You can generate the documentation for the package
and its dependencies with the `make doc` command. The documentation is generated in the `docs`
directory. [ApiGen](http://apigen.org/) is required. You can later clean the directory with
the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/): [![Build Status](https://travis-ci.org/ICanBoogie/Prototype.png?branch=master)](https://travis-ci.org/ICanBoogie/Prototype)





## Getters and setters

Getters and setters are available by extending the `Object` class. They are defined
as `protected` functions and are mapped to magic properties. Thus, you don't call a
`set_minutes()` method to set _minutes_ or a `get_minutes()` method to get them, but simply use
a magic `minutes` property, just like you would with any property.

Two types of getters/setters are available. The _lazy_ type creates properties when they
are first accessed while the _volatile_ type leaves the persistence of the property value
up to the getter. More over, you can define read-only or write-only properties using _volatile_
getters/setters.

__Remember__: Getters/setters are only called when the property they _emulate_ is not accessible.





### Volatile getters and setters

Volatile getters/setters can be used to create interfaces to other properties. For
instance, the following getters and setters provide a magic `minutes` property which stores its
value in the `seconds` property.

```php
<?php

use ICanBoogie\Object;

class Time extends Object
{
	public $seconds;

	protected function volatile_set_minutes($minutes)
	{
		$this->seconds = $minutes * 60;
	}

	protected function volatile_get_minutes()
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





### Read-only properties

Read-only properties are created by setting their visibility to `protected` or `private` and
defining only a _volatile_ getter:

```php
<?php

use ICanBoogie\Object;

class A extends Object
{
	protected $id;
	
	public function __construct($id)
	{
		$this->id = $id;
	}

	protected function volatile_get_id()
	{
		return $this->id;
	}
}

$a = new A(6);
echo $a->id; // 6
$a->id = 13; // throws ICanBoogie\PropertyIsNotWritable
```





### Write-only properties

Write-only properties are created by setting their visibility to `protected` or `private` and
defining only a _volatile_ setter:

```php
<?php

use ICanBoogie\Object;

class A extends Object
{
	private $writeonly;

	protected function volatile_set_writeonly($value)
	{
		$this->writeonly = $value;
	}
}

$a = new A;
$a->writeonly = 'test'; // test
$v = $a->writeonly; // throws ICanBoogie\PropertyIsNotReadable
```





### Lazy loading

Properties that only create their value the first time they are accessed can also be defined, they
are often used for lazy loading.

In the following example, the `get_pseudo_uniqid()` getter returns a unique value, but because the 
`pseudo_uniqid` property is created with the `public` visibility after the getter was called,
any subsequent access to the property returns the same value, because the property is now
accessible:

```php
<?php

use ICanBoogie\Object;

class A extends Object
{
	protected function get_pseudo_uniqid()
	{
		return uniqid();
	}
}

$a = new A;

echo $a->pseudo_uniqid; // 5089497a540f8
echo $a->pseudo_uniqid; // 5089497a540f8
```

Of course, unsetting the property resets the process.

```php
<?php

unset($a->pseudo_uniqid);

echo $a->pseudo_uniqid; // 508949b5aaa00
echo $a->pseudo_uniqid; // 508949b5aaa00
```





## Defining methods at runtime

Methods can be defined at runtime using the _prototype_ of a class.

Methods defined by the prototype of a class are inherited by the sub-classes of that class.

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

$time->prototype['volatile_set_minutes'] = function(Time $time, $minutes) {

	$time->seconds = $minutes * 60;
};

$time->prototype['volatile_get_minutes'] = function(Time $time, $minutes) {

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

class A extends Object
{
	public $imageid;
}

$model = $core->models['images'];
$prototype = Prototype::get('A');

$prototype['get_image'] = function(A $target) use ($model)
{
	return $target->imageid ? $model[$target->imageid] : null;
};

$a = new A();
$a->imageid = 12;
echo $a->image->nid; // 12
```




## Defining prototypes methods

Prototype methods can be defined using a global configuration; through the `prototype` property
of an `Object` instance; or using the `Prototype` instance associated with an `Object` class.





### Defining prototypes methods using a global configuration

All prototypes can be configured using a single global configuration. For each class you can
define the methods that the prototype implements.

The following example demonstrate how the `meow()` method is defined for instances of the `Cat`
and `FierceCat` classes. Although they are defined using closure in the example, methods can be
defined using any callable such as `"Website\Hooks::cat_meow"`.

```php
<?php

ICanBoogie\Prototype::configure
(
	array
	(
		'Cat' => array
		(
			'meow' => function(Cat $cat) {

				return 'Meow';
			
			}
		),
		
		'FierceCat' => array
		(
			'meow' => function(Cat $cat) {
			
				return 'MEOOOW !';

			}
		)
	)
);
```





### Defining prototypes methods through the `prototype` property

As we have seen in previous examples, prototype methods can be defined using the `prototype`
property of `Object` instances:

```php
<?php

use ICanBoogie\Object;

class Cat {}

$cat = new Cat;

$cat->prototype['meow'] = function(Cat $cat)
{
	return 'Meow';
}  

echo $cat->meow();
```





### Defining prototypes methods using a prototype instance

Prototype methods can be defined using the `Prototype` instance of a class:

```php
<?php

use ICanBoogie\Prototype;

$prototype = Prototype::get('Cat');

$prototype['meow'] = function(Cat $cat)
{
	return 'Meow';
}
```





### Defining prototypes methods using config fragments

When using the [ICanBoogie](http://icanboogie.org/) framework, prototypes methods can be defined
using the `hooks` config and the `prototypes` namespace:

```php
<?php

// config/hooks.php

return array
(
    'prototypes' => array
    (
        'Icybee\Modules\Pages\Page::my_additional_method' => 'Website\Hooks::my_additional_method',
        'Icybee\Modules\Pages\Page::get_my_property' => 'Website\Hooks::get_my_property'
    )
);
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

$a = A::from(array('a' => 1, 'b' => 2, 'c' => 3));
```

Instances are created in the same fashion [PDO](http://www.php.net/manual/en/book.pdo.php)
creates instances when fetching objects using the `FETCH_CLASS` mode, that is the properties
of the instance are set *before* its constructor is invoked.

`Object` sub-classes might want to override the `Object::from` method to allow creating
instances from different kind of sources, just like the `Operation::from` method creates an
`Operation` instance from a `Request`:

```php
<?php

namespace ICanboogie;

class Operation
{
	static public function from($properties=null, array $construct_args=array(), $class_name=null)
	{
		if ($properties instanceof Request)
		{
			return static::from_request($properties);
		}

		return parent::from($properties, $construct_args, $class_name);
	}
}
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
	$event = new Object\PropertyEvent($target, array('property' => $property));

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





## License

Prototype is licensed under the New BSD License - See the LICENSE file for details.