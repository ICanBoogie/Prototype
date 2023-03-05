<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

use ArrayAccess;
use ArrayIterator;
use ICanBoogie\Prototype\Config;
use ICanBoogie\Prototype\MethodNotDefined;
use IteratorAggregate;
use Traversable;

use function array_diff_key;
use function array_intersect_key;
use function array_merge;
use function get_parent_class;
use function is_object;
use function is_subclass_of;

/**
 * Manages the prototype methods that may be bound to classes using {@link PrototypeTrait}.
 *
 * @implements ArrayAccess<string, callable>
 * @implements IteratorAggregate<string, callable>
 */
final class Prototype implements ArrayAccess, IteratorAggregate
{
    /**
     * Prototypes instances per class.
     *
     * @var array<string, Prototype>
     */
    private static array $prototypes = [];

    /**
     * Prototype methods per class.
     *
     * @var array<class-string, array<string, callable>>|null
     */
    private static ?array $bindings = null;

    /**
     * Returns the prototype associated with the specified class or object.
     *
     * @param object|class-string $class_or_object Class name or object.
     */
    public static function from(object|string $class_or_object): Prototype
    {
        $class = is_object($class_or_object) ? $class_or_object::class : $class_or_object;

        return self::$prototypes[$class] ??= new self($class);
    }

    /**
     * Defines prototype methods.
     */
    public static function bind(Config $config): void
    {
        $bindings = $config->bindings;

        if (!$bindings) {
            return;
        }

        self::update_bindings($bindings);
        self::update_instances($bindings);
    }

    /**
     * @param object|class-string $class_or_object
     */
    public static function has_method(object|string $class_or_object, string $method): bool
    {
        return self::from($class_or_object)->offsetExists($method);
    }

    /**
     * Updates prototype methods with bindings.
     *
     * @param array<class-string, array<string, callable>> $bindings
     */
    private static function update_bindings(array $bindings): void
    {
        $current = &self::$bindings;

        if (!$current) {
            $current = $bindings;
        }

        $intersect = array_intersect_key($bindings, $current);
        $current += array_diff_key($bindings, $current);

        foreach ($intersect as $class => $methods) {
            $current[$class] = array_merge($current[$class], $methods);
        }
    }

    /**
     * Updates instances with bindings.
     *
     * @param array<class-string, array<string, callable>> $bindings
     */
    private static function update_instances(array $bindings): void
    {
        foreach (self::$prototypes as $class => $prototype) {
            $prototype->consolidated_methods = null;

            if (empty($bindings[$class])) {
                continue;
            }

            $prototype->methods = $bindings[$class] + $prototype->methods;
        }
    }

    /**
     * Parent prototype.
     */
    private readonly ?Prototype $parent;

    /**
     * Methods defined by the prototype.
     *
     * @var array<string, callable>
     */
    private array $methods = [];

    /**
     * Methods defined by the prototypes chain.
     *
     * @var array<string, callable>|null
     */
    private ?array $consolidated_methods = null;

    /**
     * Creates a prototype for the specified class.
     *
     * @param class-string $class
     */
    private function __construct(
        private readonly string $class
    ) {
        $parent_class = get_parent_class($class);
        $this->parent = $parent_class ? self::from($parent_class) : null;

        if (isset(self::$bindings[$class])) {
            $this->methods = self::$bindings[$class];
        }
    }

    /**
     * Returns the consolidated methods of the prototype.
     *
     * @return array<string, callable>
     */
    private function get_consolidated_methods(): array
    {
        return $this->consolidated_methods ??= $this->consolidate_methods();
    }

    /**
     * Consolidate the methods of the prototype.
     *
     * The method creates a single array from the prototype methods and those of its parents.
     *
     * @return array<string, callable>
     */
    private function consolidate_methods(): array
    {
        $methods = $this->methods;

        if ($this->parent) {
            $methods += $this->parent->get_consolidated_methods();
        }

        return $methods;
    }

    /**
     * Revokes the consolidated methods of the prototype.
     *
     * The method must be invoked when prototype methods are modified.
     */
    private function revoke_consolidated_methods(): void
    {
        $class = $this->class;

        foreach (self::$prototypes as $prototype) {
            if (!is_subclass_of($prototype->class, $class)) {
                continue;
            }

            $prototype->consolidated_methods = null;
        }

        $this->consolidated_methods = null;
    }

    /**
     * Adds or replaces the specified method of the prototype.
     *
     * @param string $offset The name of the method.
     *
     * @param callable $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        self::$prototypes[$this->class]->methods[$offset] = $value;

        $this->revoke_consolidated_methods();
    }

    /**
     * Removed the specified method from the prototype.
     *
     * @param string $offset The name of the method.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset(self::$prototypes[$this->class]->methods[$offset]);

        $this->revoke_consolidated_methods();
    }

    /**
     * Checks if the prototype defines the specified method.
     *
     * @param string $offset The name of the method.
     */
    public function offsetExists(mixed $offset): bool
    {
        $methods = $this->consolidated_methods ??= $this->consolidate_methods();

        return isset($methods[$offset]);
    }

    /**
     * Returns the callback associated with the specified method.
     *
     * @param string $offset The name of the method.
     *
     * @throws MethodNotDefined if the method is not defined.
     */
    public function offsetGet(mixed $offset): callable
    {
        $methods = $this->consolidated_methods ??= $this->consolidate_methods();

        if (!isset($methods[$offset])) {
            throw new MethodNotDefined($offset, $this->class);
        }

        return $methods[$offset];
    }

    /**
     * Returns an iterator for the prototype methods.
     */
    public function getIterator(): Traversable
    {
        $methods = $this->consolidated_methods ??= $this->consolidate_methods();

        return new ArrayIterator($methods);
    }
}
