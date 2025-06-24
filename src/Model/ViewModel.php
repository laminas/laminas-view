<?php

declare(strict_types=1);

namespace Laminas\View\Model;

use ArrayIterator;
use Laminas\View\Exception\UndefinedVariableException;
use ReturnTypeWillChange;
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
     * @var string
     */
    protected $captureTo = 'content';

    /**
     * Child models
     *
     * @var list<ModelInterface>
     */
    protected $children = [];

    /**
     * Template to use when rendering this model
     *
     * @var string
     */
    protected $template = '';

    /**
     * Is this a standalone, or terminal, model?
     *
     * @var bool
     */
    protected $terminate = false;

    /**
     * View variables
     *
     * @var array<string, mixed>
     */
    private array $variables;

    /**
     * Is this append to child  with the same capture?
     *
     * @var bool
     */
    protected $append = false;

    /**
     * @param iterable<string, mixed> $variables
     */
    public function __construct(
        iterable $variables = [],
        private readonly bool $strictVariables = true,
    ) {
        $this->variables = array_map(
            static fn (mixed $value): mixed => $value,
            is_array($variables) ? $variables : iterator_to_array($variables)
        );
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
     *
     * @throws UndefinedVariableException
     */
    public function __get(string $name): mixed
    {
        if (! isset($this->variables[$name]) && $this->strictVariables) {
            throw UndefinedVariableException::forVariableName($name);
        }

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

    /**
     * Set the template to be used by this model
     *
     * @param  string $template
     * @return ViewModel
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;
        return $this;
    }

    /**
     * Get the template to be used by this model
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Add a child model
     *
     * @param  null|string $captureTo Optional; if specified, the "capture to" value to set on the child
     * @param  null|bool $append Optional; if specified, append to child  with the same capture
     * @return ViewModel
     */
    public function addChild(ModelInterface $child, $captureTo = null, $append = null)
    {
        $this->children[] = $child;
        if (null !== $captureTo) {
            $child->setCaptureTo($captureTo);
        }
        if (null !== $append) {
            $child->setAppend($append);
        }

        return $this;
    }

    /**
     * Return all children.
     *
     * Return specifies an array, but may be any iterable object.
     *
     * @return list<ModelInterface>
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Does the model have any children?
     *
     * @return bool
     */
    public function hasChildren()
    {
        return (bool) $this->children;
    }

    /**
     * Clears out all child models
     *
     * @return ViewModel
     */
    public function clearChildren()
    {
        $this->children = [];
        return $this;
    }

    /**
     * Returns an array of Viewmodels with captureTo value $capture
     *
     * @param string $capture
     * @param bool $recursive search recursive through children, default true
     * @return list<ModelInterface>
     */
    public function getChildrenByCaptureTo($capture, $recursive = true)
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

    /**
     * Set the name of the variable to capture this model to, if it is a child model
     *
     * @param  string $capture
     * @return ViewModel
     */
    public function setCaptureTo($capture)
    {
        $this->captureTo = (string) $capture;
        return $this;
    }

    /**
     * Get the name of the variable to which to capture this model
     *
     * @return string
     */
    public function captureTo()
    {
        return $this->captureTo;
    }

    /**
     * Set flag indicating whether or not this is considered a terminal or standalone model
     *
     * @param  bool $terminate
     * @return ViewModel
     */
    public function setTerminal($terminate)
    {
        $this->terminate = (bool) $terminate;
        return $this;
    }

    /**
     * Is this considered a terminal or standalone model?
     *
     * @return bool
     */
    public function terminate()
    {
        return $this->terminate;
    }

    /**
     * Set flag indicating whether or not append to child  with the same capture
     *
     * @param  bool $append
     * @return ViewModel
     */
    public function setAppend($append)
    {
        $this->append = (bool) $append;
        return $this;
    }

    /**
     * Is this append to child  with the same capture?
     *
     * @return bool
     */
    public function isAppend()
    {
        return $this->append;
    }

    /**
     * Return count of children
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return count($this->children);
    }

    /**
     * Get iterator of children
     *
     * @return Traversable<int, ModelInterface>
     */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->children);
    }
}
