<?php

declare(strict_types=1);

namespace Laminas\View\Model;

use Countable;
use IteratorAggregate;

/**
 * Interface describing a view model.
 *
 * Extends "Countable"; count() should return the number of children attached
 * to the model.
 *
 * Extends "IteratorAggregate"; should allow iterating over children.
 *
 * @extends IteratorAggregate<int, ModelInterface>
 */
interface ModelInterface extends Countable, IteratorAggregate
{
    /**
     * Get a single view variable
     */
    public function getVariable(string $name, mixed $default = null): mixed;

    /**
     * Set view variable
     */
    public function setVariable(string $name, mixed $value): static;

    /**
     * Set view variables en masse
     *
     * @param iterable<string, mixed> $variables
     */
    public function setVariables(iterable $variables, bool $overwrite = false): static;

    /**
     * Get view variables
     *
     * @return array<string, mixed>
     */
    public function getVariables(): array;

    /**
     * Set the template to be used by this model
     *
     * @param non-empty-string $template
     */
    public function setTemplate(string $template): static;

    /**
     * Get the template to be used by this model
     *
     * Implementations should return an empty string when the template has not been set
     */
    public function getTemplate(): string;

    /**
     * Add a child model
     *
     * @param null|non-empty-string $captureTo Optional; if specified, the "capture to" value to set on the child.
     *                                         When null, the default 'capture to' value is used.
     * @param bool|null $append Optional; when true, the child model will be marked as an appending model.
     */
    public function addChild(ModelInterface $child, string|null $captureTo = null, bool|null $append = null): static;

    /**
     * Return all children.
     *
     * @return list<ModelInterface>
     */
    public function getChildren(): array;

    /**
     * Does the model have any children?
     */
    public function hasChildren(): bool;

    /**
     * Set the name of the variable to capture this model to, if it is a child model
     *
     * @param non-empty-string $capture
     */
    public function setCaptureTo(string $capture): static;

    /**
     * Get the name of the variable to which to capture this model
     *
     * @return non-empty-string
     */
    public function captureTo(): string;

    /**
     * Set flag indicating whether this is considered a terminal or standalone model
     */
    public function setTerminal(bool $terminate): static;

    /**
     * Is this considered a terminal or standalone model?
     */
    public function terminate(): bool;

    /**
     * Set flag indicating whether to append to child with the same capture
     */
    public function setAppend(bool $append): static;

    /**
     * Is this append to child  with the same capture?
     */
    public function isAppend(): bool;
}
