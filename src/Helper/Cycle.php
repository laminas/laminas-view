<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Stringable;

use function count;

/**
 * Helper for alternating between a set of values
 */
final class Cycle implements Stringable, StatefulHelperInterface
{
    /**
     * Default name
     */
    private const DEFAULT_NAME = 'default';

    /**
     * Array of values
     *
     * @var array<string, list<scalar|Stringable>>
     */
    private array $data = [
        self::DEFAULT_NAME => [],
    ];

    /**
     * Actual name of cycle
     */
    private string $name = self::DEFAULT_NAME;

    /**
     * Pointers
     *
     * @var array<string, int>
     */
    private array $pointers = [
        self::DEFAULT_NAME => -1,
    ];

    public function resetState(): void
    {
        $this->name     = self::DEFAULT_NAME;
        $this->pointers = [
            self::DEFAULT_NAME => -1,
        ];
        $this->data     = [
            self::DEFAULT_NAME => [],
        ];
    }

    /**
     * Add elements to alternate
     *
     * @param list<scalar|Stringable> $data
     */
    public function __invoke(array $data = [], string $name = self::DEFAULT_NAME): self
    {
        if ($data !== []) {
            $this->data[$name] = $data;
        }

        $this->setName($name);

        return $this;
    }

    /**
     * Cast to string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Turn helper into string
     */
    public function toString(): string
    {
        return (string) ($this->data[$this->name][$this->key()] ?? '');
    }

    /**
     * Add elements to alternate
     *
     * @param list<scalar|Stringable> $data
     */
    public function assign(array $data, string $name = self::DEFAULT_NAME): self
    {
        $this->setName($name);
        $this->data[$name] = $data;
        $this->rewind();

        return $this;
    }

    /**
     * Sets actual name of cycle
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        if (! isset($this->data[$this->name])) {
            $this->data[$this->name] = [];
        }

        if (! isset($this->pointers[$this->name])) {
            $this->rewind();
        }

        return $this;
    }

    /**
     * Return all elements
     *
     * @return list<scalar|Stringable>
     */
    public function getAll(): array
    {
        return $this->data[$this->name];
    }

    public function next(): self
    {
        $count = count($this->data[$this->name]);

        if ($this->pointers[$this->name] === $count - 1) {
            $this->pointers[$this->name] = 0;
        } else {
            $this->pointers[$this->name] = ++$this->pointers[$this->name];
        }

        return $this;
    }

    /**
     * Move to previous value
     */
    public function prev(): self
    {
        $count = count($this->data[$this->name]);

        if ($this->pointers[$this->name] <= 0) {
            $this->pointers[$this->name] = $count - 1;
        } else {
            $this->pointers[$this->name] = --$this->pointers[$this->name];
        }

        return $this;
    }

    /**
     * Return iteration number
     */
    public function key(): int
    {
        if ($this->pointers[$this->name] < 0) {
            return 0;
        }

        return $this->pointers[$this->name];
    }

    /**
     * Rewind pointer
     */
    public function rewind(): void
    {
        $this->pointers[$this->name] = -1;
    }

    /**
     * Return current element
     */
    public function current(): int|float|bool|string|Stringable
    {
        return $this->data[$this->name][$this->key()];
    }
}
