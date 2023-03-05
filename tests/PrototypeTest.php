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

use ICanBoogie\Prototype;
use ICanBoogie\Prototype\Config;
use ICanBoogie\Prototype\MethodNotDefined;
use PHPUnit\Framework\TestCase;
use Test\ICanBoogie\Prototype\Cat;
use Test\ICanBoogie\Prototype\FierceCat;
use Test\ICanBoogie\Prototype\NormalCat;
use Test\ICanBoogie\PrototypedCases\SampleA;
use Test\ICanBoogie\PrototypedCases\SampleB;
use Test\ICanBoogie\PrototypeTraitCases\BindCase;
use Test\ICanBoogie\PrototypeTraitCases\UnsetCase;

final class PrototypeTest extends TestCase
{
    private SampleA $a;
    private SampleB $b;

    protected function setUp(): void
    {
        $this->a = new SampleA();
        $this->b = new SampleB();

        Prototype::set_method(
            SampleA::class,
            'set_minutes',
            fn(SampleA $self, int $minutes) => $self->seconds = $minutes * 60
        );

        Prototype::set_method(
            SampleA::class,
            'get_minutes',
            fn(SampleA $self) => $self->seconds / 60
        );
    }

    public function testBind(): void
    {
        $method1 = 'm' . uniqid();
        $method2 = 'm' . uniqid();
        $value1 = uniqid();
        $value2 = uniqid();
        $value3 = uniqid();

        $callback1 = fn(BindCase $case) => $value1;
        $callback2 = fn(BindCase $case) => $value2;
        $callback3 = fn(BindCase $case) => $value3;

        Prototype::bind(new Config([ BindCase::class => [ $method1 => $callback1 ] ]));
        Prototype::bind(new Config([ BindCase::class => [ $method2 => $callback2 ] ]));
        Prototype::bind(new Config([]));

        $case = new BindCase();

        $this->assertSame($value1, $case->$method1());
        $this->assertSame($value2, $case->$method2());

        Prototype::bind(new Config([ BindCase::class => [ $method1 => $callback3 ] ]));

        $methods = iterator_to_array(Prototype::from(BindCase::class));

        $this->assertSame([

            $method1 => $callback3,
            $method2 => $callback2,

        ], $methods);

        $this->assertSame($value3, $case->$method1());
        $this->assertSame($value2, $case->$method2());
    }

    public function testMethod(): void
    {
        $a = $this->a;

        Prototype::set_method(
            $a,
            'format',
            fn (SampleA $self, string $format) => date($format, $self->seconds)
        );

        $a->seconds = time();
        $format = 'H:i:s';

        /** @phpstan-ignore-next-line */
        $this->assertEquals(date($format, $a->seconds), $a->format($format));
    }

    public function testSetterGetter(): void
    {
        $a = $this->a;

        $a->minutes = 2;

        $this->assertEquals(120, $a->seconds);
        $this->assertEquals(2, $a->minutes);
    }

    public function testPrototypeChain(): void
    {
        $b = $this->b;
        $prototype = Prototype::from($b);
        $prototype['set_hours'] = fn(SampleB $self, $hours) => $self->seconds = $hours * 3600;
        $prototype['get_hours'] = fn(SampleB $self, $hours) => $self->seconds / 3600;

        $b->minutes = 4;

        $this->assertEquals(240, $b->seconds);
        $this->assertEquals(4, $b->minutes);

        $b->hours = 1;

        $this->assertEquals(3600, $b->seconds);
        $this->assertEquals(1, $b->hours);

        # hours should be a simple property for A

        $a = $this->a;

        $a->seconds = 0;
        $a->hours = 1;

        $this->assertEquals(0, $a->seconds);
        $this->assertEquals(1, $a->hours);
    }

    public function testPrototypeChainWithCats(): void
    {
        $cat = new Cat();
        $normal_cat = new NormalCat();
        $fierce_cat = new FierceCat();
        $other_fierce_cat = new FierceCat();

        Prototype::from($cat)['meow'] = fn($target) => 'Meow';
        Prototype::from($fierce_cat)['meow'] = fn($target) => 'MEOOOW !';

        /** @phpstan-ignore-next-line */
        $this->assertEquals('Meow', $cat->meow());
        /** @phpstan-ignore-next-line */
        $this->assertEquals('Meow', $normal_cat->meow());
        /** @phpstan-ignore-next-line */
        $this->assertEquals('MEOOOW !', $fierce_cat->meow());
        /** @phpstan-ignore-next-line */
        $this->assertEquals('MEOOOW !', $other_fierce_cat->meow());
    }

    public function testMethodNotDefined(): void
    {
        $this->expectException(MethodNotDefined::class);
        /** @phpstan-ignore-next-line */
        $this->a->undefined_method();
    }

    public function testUnset(): void
    {
        $value = uniqid();
        $method = 'm' . uniqid();

        $prototype = Prototype::from(UnsetCase::class);
        $prototype[$method] = function () use ($value) {
            return $value;
        };

        $case = new UnsetCase();

        $this->assertSame($value, $case->$method());

        unset($prototype[$method]);

        $this->expectException(MethodNotDefined::class);

        $case->$method();
    }
}
