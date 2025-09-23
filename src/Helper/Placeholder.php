<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Helper\Placeholder\Position;
use Stringable;

use function implode;

/**
 * Helper for aggregating string content between otherwise segregated Views.
 */
final class Placeholder implements StatefulHelperInterface, Stringable
{
    /**
     * Placeholder Containers
     *
     * @var array<string, Container<string>>
     */
    private array $items                  = [];
    private string|null $currentContainer = null;
    private string $separator;
    /** @var array<string, Position> */
    private array $capturePosition = [];

    public function __construct(
        private readonly string $defaultSeparator = '',
    ) {
        $this->separator = $this->defaultSeparator;
    }

    public function resetState(): void
    {
        foreach ($this->items as $container) {
            if ($container->isCapturing()) {
                $container->captureEnd();
            }
        }

        $this->separator        = $this->defaultSeparator;
        $this->items            = [];
        $this->currentContainer = null;
        $this->capturePosition  = [];
    }

    /**
     * Instance Accessor
     */
    public function __invoke(string|null $placeholder = null): self
    {
        if ($placeholder !== null) {
            $this->container($placeholder);
        }

        return $this;
    }

    /** @return Container<string> */
    private function container(string $name): Container
    {
        $this->currentContainer = $name;
        if (! isset($this->items[$name])) {
            /** @psalm-var Container<string> */
            $this->items[$name] = new Container();
        }

        return $this->items[$name];
    }

    public function containerExists(string $name): bool
    {
        return isset($this->items[$name]);
    }

    private function name(string|null $name): string
    {
        $name ??= $this->currentContainer;
        if ($name === null) {
            throw new RuntimeException('Cannot determine the name of the placeholder');
        }

        return $name;
    }

    public function append(string $content, string|null $placeholder = null): self
    {
        $this->container($this->name($placeholder))->append($content);

        return $this;
    }

    public function prepend(string $content, string|null $placeholder = null): self
    {
        $this->container($this->name($placeholder))->prepend($content);

        return $this;
    }

    public function set(string $content, string|null $placeholder = null): self
    {
        $this->container($this->name($placeholder))->set($content);

        return $this;
    }

    public function toString(string|null $placeholder = null): string
    {
        return implode($this->separator, $this->container($this->name($placeholder))->toArray());
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function captureStart(
        string|null $placeholder = null,
        Position $position = Position::Append,
    ): void {
        $name = $this->name($placeholder);
        $this->container($name)->captureStart();
        $this->capturePosition[$name] = $position;
    }

    public function captureEnd(string|null $placeholder = null): void
    {
        $name      = $this->name($placeholder);
        $container = $this->container($name);
        $content   = $container->captureEnd();
        $position  = $this->capturePosition[$name];
        match ($position) {
            Position::Append => $container->append($content),
            Position::Prepend => $container->prepend($content),
            Position::Set => $container->set($content),
        };
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }
}
