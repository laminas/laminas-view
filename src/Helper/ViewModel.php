<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Model\ModelInterface;

/**
 * Helper for storing and retrieving the root and current view model
 */
final class ViewModel implements StatefulHelperInterface
{
    private ModelInterface|null $current = null;
    private ModelInterface|null $root    = null;

    public function resetState(): void
    {
        $this->current = null;
        $this->root    = null;
    }

    public function __invoke(): self
    {
        return $this;
    }

    /**
     * Set the current view model
     */
    public function setCurrent(ModelInterface $model): self
    {
        $this->current = $model;
        return $this;
    }

    /**
     * Get the current view model
     */
    public function getCurrent(): ModelInterface|null
    {
        return $this->current;
    }

    /**
     * Is a current view model composed?
     */
    public function hasCurrent(): bool
    {
        return $this->current instanceof ModelInterface;
    }

    /**
     * Set the root view model
     */
    public function setRoot(ModelInterface $model): self
    {
        $this->root = $model;
        return $this;
    }

    /**
     * Get the root view model
     */
    public function getRoot(): ModelInterface|null
    {
        return $this->root;
    }

    /**
     * Is a root view model composed?
     */
    public function hasRoot(): bool
    {
        return $this->root instanceof ModelInterface;
    }
}
