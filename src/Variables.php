<?php

declare(strict_types=1);

namespace Laminas\View;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Laminas\View\Exception\UndefinedVariableException;
use Traversable;

use function array_key_exists;
use function count;

/**
 * Represents the variables assigned to the view model to be rendered
 *
 * @no-seal-properties This class is a mixed property bag
 * @implements IteratorAggregate<string, mixed>
 * @implements ArrayAccess<string, mixed>
 */
final class Variables implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @param array<string, mixed> $variables
     * @param bool $strictVariables When true, undefined variables accessed in the view scripts will trigger exceptions
     */
    public function __construct(
        private array $variables = [],
        private readonly bool $strictVariables = true,
    ) {
    }

    /**
     * Assign many values at once
     *
     * @param array<string, mixed> $variables
     */
    public function assign(array $variables): self
    {
        /** @psalm-var mixed $value */
        foreach ($variables as $name => $value) {
            $this->variables[$name] = $value;
        }

        return $this;
    }

    /**
     * Get a variable value
     *
     * If the value has not been defined, a null value will be returned unless
     * strict variables is active, in which case, an exception will be thrown.
     *
     * @throws UndefinedVariableException
     */
    public function __get(string $offset): mixed
    {
        if (! array_key_exists($offset, $this->variables)) {
            if ($this->strictVariables) {
                throw UndefinedVariableException::forVariableName($offset);
            }

            return null;
        }

        return $this->variables[$offset];
    }

    public function __set(string $offset, mixed $value): void
    {
        $this->variables[$offset] = $value;
    }

    public function __isset(string $offset): bool
    {
        return isset($this->variables[$offset]);
    }

    public function __unset(string $offset): void
    {
        unset($this->variables[$offset]);
    }

    /** @return Traversable<string, mixed> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->variables);
    }

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * @param string $offset
     * @throws UndefinedVariableException
     */
    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->__unset($offset);
    }

    /** @return array<string, mixed> */
    public function getArrayCopy(): array
    {
        return $this->variables;
    }

    public function count(): int
    {
        return count($this->variables);
    }
}
