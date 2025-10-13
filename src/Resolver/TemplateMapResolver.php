<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use ArrayIterator;
use IteratorAggregate;
use Laminas\View\Exception\InvalidArgumentException;
use Traversable;

use function array_key_exists;
use function array_replace_recursive;
use function is_array;
use function is_iterable;
use function is_string;
use function iterator_to_array;
use function sprintf;

/** @implements IteratorAggregate<non-empty-string, non-empty-string> */
final class TemplateMapResolver implements IteratorAggregate, ResolverInterface
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $map = [];

    /** @param iterable<non-empty-string, non-empty-string> $map */
    public function __construct(iterable $map = [])
    {
        $this->setMap($map);
    }

    /** @return Traversable<non-empty-string, non-empty-string> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->map);
    }

    /**
     * Set (overwrite) template map
     *
     * Maps should be arrays with name => path pairs
     *
     * @param iterable<string, string> $map
     * @throws InvalidArgumentException
     */
    public function setMap(iterable $map): void
    {
        foreach ($map as $name => $value) {
            $this->assertMap($name, $value);
        }

        /** @psalm-var iterable<non-empty-string, non-empty-string> $map */

        $this->map = is_array($map) ? $map : iterator_to_array($map);
    }

    /**
     * @psalm-assert non-empty-string $name
     * @psalm-assert non-empty-string $value
     * @throws InvalidArgumentException
     */
    private function assertMap(int|string $name, mixed $value): void
    {
        if (! is_string($name) || ! is_string($value) || $name === '' || $value === '') {
            throw new InvalidArgumentException(sprintf(
                'Template names and values should be non-empty strings. Received `%s => %s`',
                $name,
                (string) $value,
            ));
        }
    }

    /**
     * Add an entry to the map
     *
     * A hash map can be passed as the first argument to perform a merge
     *
     * @param string|iterable<string, string> $nameOrMap
     * @throws InvalidArgumentException
     */
    public function add(string|iterable $nameOrMap, string|null $path = null): void
    {
        if (is_string($nameOrMap) && is_string($path)) {
            $this->assertMap($nameOrMap, $path);
            $this->merge([$nameOrMap => $path]);

            return;
        }

        if (is_iterable($nameOrMap)) {
            $this->merge($nameOrMap);

            return;
        }

        throw new InvalidArgumentException(
            'Either specify both $nameOrMap and $path as strings, or, $nameOrMap as an iterable'
        );
    }

    /**
     * Merge internal map with provided map
     *
     * @param iterable<string, string> $map
     * @throws InvalidArgumentException
     */
    public function merge(iterable $map): void
    {
        foreach ($map as $name => $value) {
            $this->assertMap($name, $value);
        }

        $map = is_array($map) ? $map : iterator_to_array($map);

        /** @psalm-var array<non-empty-string, non-empty-string> $result */
        $result = array_replace_recursive($this->map, $map);

        $this->map = $result;
    }

    /**
     * Does the resolver contain an entry for the given name?
     *
     * @param non-empty-string $name
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->map);
    }

    /**
     * Retrieve a template path by name
     *
     * @param non-empty-string $name
     * @return non-empty-string
     * @throws TemplateCannotBeFound If no entry exists.
     */
    public function get(string $name): string
    {
        if (! $this->has($name)) {
            throw TemplateCannotBeFound::byName($name);
        }

        return $this->map[$name];
    }

    /**
     * Retrieve the template map
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function getMap(): array
    {
        return $this->map;
    }

    public function resolve(string $name): string|false
    {
        return $this->map[$name] ?? false;
    }
}
