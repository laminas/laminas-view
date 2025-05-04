<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use Countable;
use IteratorAggregate;
use Laminas\Stdlib\PriorityQueue;
use Traversable;

/** @implements IteratorAggregate<int, ResolverInterface> */
final class AggregateResolver implements Countable, IteratorAggregate, ResolverInterface
{
    /** @var PriorityQueue<ResolverInterface, int> */
    private PriorityQueue $queue;

    /**
     * @param list<ResolverInterface> $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        /** @var PriorityQueue<ResolverInterface, int> $priorityQueue */
        $priorityQueue = new PriorityQueue();

        $this->queue = $priorityQueue;
        foreach ($resolvers as $resolver) {
            $this->attach($resolver);
        }
    }

    public function count(): int
    {
        return $this->queue->count();
    }

    /**
     * IteratorAggregate: return internal iterator
     *
     * @return Traversable<int, ResolverInterface>
     */
    public function getIterator(): Traversable
    {
        return $this->queue;
    }

    public function attach(ResolverInterface $resolver, int $priority = 1): self
    {
        $this->queue->insert($resolver, $priority);
        return $this;
    }

    public function resolve(string $name): string|false
    {
        foreach ($this->queue as $resolver) {
            $path = $resolver->resolve($name);
            if ($path === false) {
                continue;
            }

            return $path;
        }

        return false;
    }
}
