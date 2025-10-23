<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Placeholder;

use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use Laminas\View\Exception\RuntimeException;
use Traversable;

use function array_unshift;
use function array_values;
use function assert;
use function count;
use function ob_get_clean;
use function ob_start;

/**
 * Container for placeholder values
 *
 * This class is not part of the public API and has no BC guarantees
 *
 * @internal
 *
 * @template TValue
 * @implements IteratorAggregate<int, TValue>
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class Container implements Countable, IteratorAggregate
{
    /** @var array<int, TValue> */
    private array $items      = [];
    private bool $captureLock = false;

    /** @param TValue $item */
    public function append(mixed $item): void
    {
        $this->items[] = $item;
    }

    /** @param TValue $item */
    public function prepend(mixed $item): void
    {
        array_unshift($this->items, $item);
    }

    /** @param TValue $item */
    public function set(mixed $item): void
    {
        $this->items = [$item];
    }

    /** @param TValue $item */
    public function remove(mixed $item): void
    {
        foreach ($this->items as $key => $remove) {
            if ($item !== $remove) {
                continue;
            }

            unset($this->items[$key]);

            return;
        }
    }

    /**
     * @param Closure(TValue): bool $matcher
     * @return TValue|null
     */
    public function firstMatch(Closure $matcher): mixed
    {
        foreach ($this->items as $item) {
            if ($matcher($item) === true) {
                return $item;
            }
        }

        return null;
    }

    /** @return Traversable<int, TValue> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /** @return list<TValue> */
    public function toArray(): array
    {
        return array_values($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isCapturing(): bool
    {
        return $this->captureLock;
    }

    /**
     * @throws RuntimeException If a capture is already in progress.
     */
    public function captureStart(): void
    {
        if ($this->captureLock) {
            throw new RuntimeException('This container is already capturing output');
        }

        $this->captureLock = true;
        ob_start();
    }

    /**
     * @throws RuntimeException If a capture is not in progress.
     */
    public function captureEnd(): string
    {
        if (! $this->captureLock) {
            throw new RuntimeException('This container is not currently capturing output');
        }

        $content = ob_get_clean();
        assert($content !== false);
        $this->captureLock = false;

        return $content;
    }
}
