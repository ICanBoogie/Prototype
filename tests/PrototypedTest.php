<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\ICanBoogie;

use Exception;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\PropertyNotReadable;
use ICanBoogie\PropertyNotWritable;
use ICanBoogie\Prototype\UnableToInstantiate;
use ICanBoogie\Prototyped;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Prototype\ExportCase;
use Test\ICanBoogie\Prototype\ToArrayCase;
use Test\ICanBoogie\PrototypedCases\AssignableCase;
use Test\ICanBoogie\PrototypedCases\CreatedAtCase;
use Test\ICanBoogie\PrototypedCases\CreatedAtCaseExtended;
use Test\ICanBoogie\PrototypedCases\FailingCase;
use Test\ICanBoogie\PrototypedCases\ReadOnlyProperty;
use Test\ICanBoogie\PrototypedCases\ReadOnlyPropertyExtended;
use Test\ICanBoogie\PrototypedCases\ReadOnlyPropertyPrivate;
use Test\ICanBoogie\PrototypedCases\ReadOnlyPropertyPrivateExtended;
use Test\ICanBoogie\PrototypedCases\ReadOnlyPropertyProtected;
use Test\ICanBoogie\PrototypedCases\ReadOnlyPropertyProtectedExtended;
use Test\ICanBoogie\PrototypedCases\SampleA;
use Test\ICanBoogie\PrototypedCases\SampleD;
use Test\ICanBoogie\PrototypedCases\ToArrayWithFacadePropertyCase;
use Throwable;

use function get_class;

final class PrototypedTest extends TestCase
{
    public function test_export_empty(): void
    {
        $o = new Prototyped();

        $this->assertEmpty($o->__sleep());
        $this->assertEmpty($o->to_array());
    }

    /**
     * @dataProvider provide_test_readonly
     *
     */
    public function test_readonly($class): void
    {
        $o = new $class();
        $this->assertEquals('value', $o->property);
        $this->expectException(PropertyNotWritable::class);
        $o->property = true;
    }

    /**
     * @return array<array{ class-string }>
     */
    public static function provide_test_readonly(): array
    {
        return [

            [ ReadOnlyProperty::class ],
            [ ReadOnlyPropertyExtended::class ],
            [ ReadOnlyPropertyProtected::class ],
            [ ReadOnlyPropertyProtectedExtended::class ],
            [ ReadOnlyPropertyPrivate::class ],
            [ ReadOnlyPropertyPrivateExtended::class ],

        ];
    }

    /**
     * @dataProvider provide_test_write_only
     */
    public function test_write_only(string $class): void
    {
        $o = new $class();
        $o->property = true;
        $this->expectException(PropertyNotReadable::class);
        $o->property;
    }

    /**
     * @return array<array{ class-string }>
     */
    public static function provide_test_write_only(): array
    {
        return [

            [ PrototypedCases\WriteOnlyProperty::class ],
            [ PrototypedCases\WriteOnlyPropertyExtended::class ],
            [ PrototypedCases\WriteOnlyPropertyProtected::class ],
            [ PrototypedCases\WriteOnlyPropertyProtectedExtended::class ],
            [ PrototypedCases\WriteOnlyPropertyPrivate::class ],
            [ PrototypedCases\WriteOnlyPropertyPrivateExtended::class ],

        ];
    }

    public function test_set_undefined(): void
    {
        $o = new SampleA();
        $v = uniqid();
        $p = 'property' . uniqid();
        $o->$p = $v;
        $this->assertSame($v, $o->$p);
    }

    public function test_get_undefined(): void
    {
        $o = new Prototyped();
        $p = 'property' . uniqid();
        $this->expectException(PropertyNotDefined::class);
        $o->$p;
    }

    public function test_to_array(): void
    {
        $o = new Prototyped();
        $this->assertEmpty($o->to_array());

        $o = new ExportCase();
        $array = $o->to_array();
        $this->assertArrayHasKey('public', $array);
        $this->assertArrayHasKey('public_with_lazy_getter', $array);
        $this->assertArrayNotHasKey('protected', $array);
        $this->assertArrayNotHasKey('protected_with_getter', $array);
        $this->assertArrayNotHasKey('protected_with_setter', $array);
        $this->assertArrayNotHasKey('protected_with_getter_and_setter', $array);
        $this->assertArrayNotHasKey('protected_with_lazy_getter', $array);
        $this->assertArrayNotHasKey('private', $array);
        $this->assertArrayNotHasKey('private_with_getter', $array);
        $this->assertArrayNotHasKey('private_with_setter', $array);
        $this->assertArrayHasKey('private_with_getter_and_setter', $array);
    }

    public function test_sleep(): void
    {
        $o = new Prototyped();
        $this->assertEmpty($o->__sleep());

        $o = new ExportCase();
        $properties = $o->__sleep();
        $this->assertArrayHasKey('public', $properties);
        $this->assertArrayNotHasKey('public_with_lazy_getter', $properties);
        $this->assertArrayHasKey('protected', $properties);
        $this->assertArrayHasKey('protected_with_getter', $properties);
        $this->assertArrayHasKey('protected_with_setter', $properties);
        $this->assertArrayHasKey('protected_with_getter_and_setter', $properties);
        $this->assertArrayNotHasKey('protected_with_lazy_getter', $properties);
        $this->assertArrayNotHasKey('private', $properties);
        $this->assertArrayNotHasKey('private_with_getter', $properties);
        $this->assertArrayNotHasKey('private_with_setter', $properties);
        $this->assertArrayHasKey('private_with_getter_and_setter', $properties);
        $this->assertArrayNotHasKey('private_with_lazy_getter', $properties);
    }

    public function test_to_array2(): void
    {
        $a = new ToArrayCase(1, 2, 3);
        $this->assertEquals([ 'a' => 1, 'b' => 2, 'c' => 3 ], $a->to_array());
    }

    public function test_to_array_with_property_facade(): void
    {
        $a = new ToArrayWithFacadePropertyCase(1, 2, 3);
        $this->assertEquals([ 'a' => 1, 'c' => 3 ], $a->to_array());
    }

    public function test_to_array_recursive(): void
    {
        $a = new ToArrayCase(1, new ToArrayCase(11, 12, 13), [ 1, 2, 3 ]);
        $this->assertEquals(
            [ 'a' => 1, 'b' => [ 'a' => 11, 'b' => 12, 'c' => 13 ], 'c' => [ 1, 2, 3 ] ],
            $a->to_array_recursive()
        );
    }

    public function test_to_json(): void
    {
        $a = new ToArrayCase(1, new ToArrayCase(11, 12, 13), [ 1, 2, 3 ]);
        $this->assertEquals('{"a":1,"b":{"a":11,"b":12,"c":13},"c":[1,2,3]}', $a->to_json());
    }

    public function testDefaultValueForUnsetProperty(): void
    {
        $o = new PrototypedCases\DefaultValueForUnsetProperty();
        $o->title = 'The quick brown fox';
        $this->assertEquals('the-quick-brown-fox', $o->slug);
        $this->assertArrayNotHasKey('slug', (array)$o);
        $this->assertArrayNotHasKey('slug', $o->to_array());
        $this->assertNotContains('slug', $o->__sleep());

        $o = PrototypedCases\DefaultValueForUnsetProperty::from([ 'title' => 'The quick brown fox' ]);
        $this->assertEquals('the-quick-brown-fox', $o->slug);
        $this->assertArrayNotHasKey('slug', (array)$o);
        $this->assertArrayNotHasKey('slug', $o->to_array());
        $this->assertNotContains('slug', $o->__sleep());

        $o = new PrototypedCases\DefaultValueForUnsetProperty();
        $o->title = 'The quick brown fox';
        $o->slug = 'brown-fox';
        $this->assertEquals('brown-fox', $o->slug);
        $this->assertArrayHasKey('slug', (array)$o);
        $this->assertArrayHasKey('slug', $o->to_array());
        $this->assertContains('slug', $o->__sleep());

        $o = PrototypedCases\DefaultValueForUnsetProperty::from(
            [ 'title' => 'The quick brown fox', 'slug' => 'brown-fox' ]
        );
        $this->assertEquals('brown-fox', $o->slug);
        $this->assertArrayHasKey('slug', (array)$o);
        $this->assertArrayHasKey('slug', $o->to_array());
        $this->assertContains('slug', $o->__sleep());
    }

    public function testDefaultValueForUnsetProtectedProperty(): void
    {
        $o = new PrototypedCases\DefaultValueForUnsetProtectedProperty();
        $o->title = 'Testing';
        $this->assertEquals('testing', $o->slug);
        # slug comes from the volatile getter, the property must *not* be set.
        $this->assertArrayNotHasKey('slug', (array)$o);
    }

    public function testInvalidUseOfDefaultValueForUnsetProtectedProperty(): void
    {
        $o = new PrototypedCases\DefaultValueForUnsetProtectedProperty();
        $this->expectException(PropertyNotWritable::class);
        $o->slug = 'madonna';
    }

    public function testInvalidProtectedPropertyGetter(): void
    {
        $o = new PrototypedCases\InvalidProtectedPropertyGetter();
        $this->expectException(PropertyNotWritable::class);
        $a = $o->value;
    }

    public function testValidProtectedPropertyGetter(): void
    {
        $o = new PrototypedCases\ValidProtectedPropertyGetter();
        $this->assertNotNull($o->value);
    }

    public function testVirtualProperty(): void
    {
        $o = new PrototypedCases\VirtualProperty();

        $o->minutes = 1;
        $this->assertEquals(1, $o->minutes);
        $this->assertEquals(60, $o->seconds);

        $o->seconds = 120;
        $this->assertEquals(2, $o->minutes);

        $o->minutes *= 2;
        $this->assertEquals(240, $o->seconds);
        $this->assertEquals(4, $o->minutes);

        $this->assertArrayNotHasKey('minutes', (array)$o);
        $this->assertArrayNotHasKey('minutes', $o->__sleep());
        $this->assertArrayNotHasKey('minutes', $o->to_array());
    }

    public function testGetUnsetPublicProperty(): void
    {
        $fixture = new SampleD();
        $this->expectException(PropertyNotDefined::class);
        $fixture->unset;
    }

    public function testGetUnsetProtectedProperty(): void
    {
        $fixture = new SampleD();
        $this->expectException(PropertyNotReadable::class);
        $fixture->unset_protected;
    }

    public function testGetUndefinedProperty(): void
    {
        $fixture = new SampleD();
        $this->expectException(PropertyNotDefined::class);
        $fixture->madonna;
    }

    public function testProtectedProperty(): void
    {
        $fixture = new SampleD();
        $fixture->c = 'c';

        $this->assertEquals('c', $fixture->c);
    }

    public function testProtectedVolatileProperty(): void
    {
        $fixture = new SampleD();
        $fixture->d = 'd';

        $this->assertEquals('d', $fixture->d);
    }

    /**
     * Properties with getters should be removed before serialization.
     */
    public function testSleepAndGetters(): void
    {
        $fixture = new SampleD();

        $this->assertEquals('a', $fixture->a);
        $this->assertEquals('b', $fixture->b);

        $fixture = $fixture->__sleep();

        $this->assertArrayNotHasKey('a', $fixture);
        $this->assertArrayNotHasKey('b', $fixture);
    }

    public function test_prototype_is_not_exported(): void
    {
        $o = new Prototyped();
        $this->assertNotContains('prototype', $o->__sleep());
        $this->assertArrayNotHasKey('prototype', $o->to_array());
    }

    /**
     * @dataProvider provide_test_created_at_case
     *
     * @param class-string $class
     *
     * - A string or a DateTime can be set to `created_at`
     * - A \DateTime instance is always obtained through `created_at`.
     * - The `created_at` property MUST be preserved by serialization.
     */
    public function test_created_at_case(string $class): void
    {
        /* @var $o CreatedAtCase */
        $o = new $class();

        $now = new \DateTime();
        $o->created_at = $now;
        $this->assertInstanceOf(\DateTime::class, $o->created_at);

        $sleep = $o->__sleep();
        $this->assertArrayHasKey('created_at', $sleep);
        $this->assertContains("\x00" . CreatedAtCase::class . "\x00created_at", $sleep);

        $serialized = serialize($o);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(\DateTime::class, $unserialized->created_at);
        $this->assertTrue($unserialized->created_at == $now);
    }

    public static function provide_test_created_at_case(): array
    {
        return [

            [ CreatedAtCase::class ],
            [ CreatedAtCaseExtended::class ],

        ];
    }

    public function test_assign_safe(): void
    {
        $case = new AssignableCase();
        $case->assign([

            AssignableCase::PROPERTY_ID => uniqid(),
            AssignableCase::PROPERTY_COMMENT => $comment = uniqid(),
            AssignableCase::PROPERTY_COLOR => $color = uniqid(),

        ]);

        $this->assertEmpty($case->id);
        $this->assertSame($comment, $case->comment);
        $this->assertSame($color, $case->color);
    }

    public function test_assign_unsafe(): void
    {
        $case = new AssignableCase();
        $case->assign([

            AssignableCase::PROPERTY_ID => $id = uniqid(),
            AssignableCase::PROPERTY_COMMENT => $comment = uniqid(),
            AssignableCase::PROPERTY_COLOR => $color = uniqid(),

        ], AssignableCase::ASSIGN_UNSAFE);

        $this->assertSame($id, $case->id);
        $this->assertSame($comment, $case->comment);
        $this->assertSame($color, $case->color);
    }

    public function test_from_is_unsafe(): void
    {
        $case = AssignableCase::from([

            AssignableCase::PROPERTY_ID => $id = uniqid(),
            AssignableCase::PROPERTY_COMMENT => $comment = uniqid(),
            AssignableCase::PROPERTY_COLOR => $color = uniqid(),

        ]);

        $this->assertSame($id, $case->id);
        $this->assertSame($comment, $case->comment);
        $this->assertSame($color, $case->color);
    }

    public function test_from_should_decorate_failures(): void
    {
        $cause = new Exception();

        try {
            FailingCase::from([ 'one' => 1 ], [ $cause ]);
        } catch (UnableToInstantiate $e) {
            $this->assertSame($cause, $e->getPrevious());

            return;
        } catch (Throwable $e) {
            $this->fail("Excepted decorating exception, got: " . get_class($e));
        }
    }
}
