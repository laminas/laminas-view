<?php

declare(strict_types=1);

namespace Laminas\View\Model;

use ArrayIterator;
use Traversable;

use function array_key_exists;
use function array_map;
use function array_merge;
use function count;
use function is_array;
use function iterator_to_array;

/**
 * @psalm-no-seal-properties This object is a mixed property bag
 */
final class ViewModel implements ModelInterface, ClearableModelInterface, RetrievableChildrenInterface
{
    /**
     * What variable a parent model should capture this model to
     *
     * @var non-empty-string
     */
    private string $captureTo = 'content';

    /**
     * Child models
     *
     * @var list<ModelInterface>
     */
    private array $children = [];

    /**
     * Is this a standalone, or terminal, model?
     */
    private bool $terminate = false;

    /**
     * View variables
     *
     * @var array<string, mixed>
     */
    private array $variables;

    /**
     * Is this append to child with the same capture?
     */
    private bool $append = false;

    /**
     * @param iterable<non-empty-string, mixed> $variables
     * @param array<non-empty-string, ModelInterface> $children
     */
    public function __construct(
        iterable $variables = [],
        private string $template = '',
        array $children = [],
    ) {
        $this->variables = array_map(
            static fn (mixed $value): mixed => $value,
            is_array($variables) ? $variables : iterator_to_array($variables)
        );

        foreach ($children as $captureTo => $child) {
            $this->addChild($child, $captureTo);
        }
    }

    /**
     * Property overloading: set variable value
     */
    public function __set(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }

    /**
     * Property overloading: get variable value
     */
    public function __get(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * Property overloading: do we have the requested variable value?
     */
    public function __isset(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /**
     * Property overloading: unset the requested variable
     */
    public function __unset(string $name): void
    {
        unset($this->variables[$name]);
    }

    /**
     * Get a single view variable
     */
    public function getVariable(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->variables)
            ? $this->variables[$name]
            : $default;
    }

    /**
     * Set view variable
     */
    public function setVariable(string $name, mixed $value): static
    {
        $this->{$name} = $value;

        return $this;
    }

    /**
     * Set view variables en masse
     *
     * Can be an array or a Traversable + ArrayAccess object.
     *
     * @param iterable<string, mixed> $variables
     * @param bool $overwrite Whether to overwrite existing variables
     */
    public function setVariables(iterable $variables, bool $overwrite = false): static
    {
        if ($overwrite) {
            $this->variables = [];
        }

        $this->variables = array_merge($this->variables, array_map(
            static fn (mixed $value): mixed => $value,
            is_array($variables) ? $variables : iterator_to_array($variables)
        ));

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function clearVariables(): static
    {
        $this->variables = [];

        return $this;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function addChild(ModelInterface $child, string|null $captureTo = null, bool|null $append = null): static
    {
        if ($captureTo !== null) {
            $child = $child->setCaptureTo($captureTo);
        }

        if ($append !== null) {
            $child = $child->setAppend($append);
        }

        $this->children[] = $child;

        return $this;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    public function clearChildren(): static
    {
        $this->children = [];
        return $this;
    }

    public function getChildrenByCaptureTo(string $capture, bool $recursive = true): array
    {
        $children = [];

        foreach ($this->children as $child) {
            if ($recursive === true && $child instanceof RetrievableChildrenInterface) {
                $children = array_merge($children, $child->getChildrenByCaptureTo($capture));
            }

            if ($child->captureTo() === $capture) {
                $children[] = $child;
            }
        }

        return $children;
    }

    public function setCaptureTo(string $capture): static
    {
        $this->captureTo = $capture;
        return $this;
    }

    public function captureTo(): string
    {
        return $this->captureTo;
    }

    public function setTerminal(bool $terminate): static
    {
        $this->terminate = $terminate;
        return $this;
    }

    public function terminate(): bool
    {
        return $this->terminate;
    }

    public function setAppend(bool $append): static
    {
        $this->append = $append;
        return $this;
    }

    public function isAppend(): bool
    {
        return $this->append;
    }

    public function count(): int
    {
        return count($this->children);
    }

    /**
     * Get iterator of children
     *
     * @return Traversable<int, ModelInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->children);
    }
}
