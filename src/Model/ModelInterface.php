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
     * @param  string $template
     * @return ModelInterface
     */
    public function setTemplate($template);

    /**
     * Get the template to be used by this model
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Add a child model
     *
     * @param  null|string $captureTo Optional; if specified, the "capture to" value to set on the child
     * @param  null|bool $append Optional; if specified, append to child  with the same capture
     * @return ModelInterface
     */
    public function addChild(ModelInterface $child, $captureTo = null, $append = false);

    /**
     * Return all children.
     *
     * Return specifies an array, but may be any iterable object.
     *
     * @return list<ModelInterface>
     */
    public function getChildren();

    /**
     * Does the model have any children?
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Set the name of the variable to capture this model to, if it is a child model
     *
     * @param  string $capture
     * @return ModelInterface
     */
    public function setCaptureTo($capture);

    /**
     * Get the name of the variable to which to capture this model
     *
     * @return string
     */
    public function captureTo();

    /**
     * Set flag indicating whether or not this is considered a terminal or standalone model
     *
     * @param  bool $terminate
     * @return ModelInterface
     */
    public function setTerminal($terminate);

    /**
     * Is this considered a terminal or standalone model?
     *
     * @return bool
     */
    public function terminate();

    /**
     * Set flag indicating whether or not append to child  with the same capture
     *
     * @param  bool $append
     * @return ModelInterface
     */
    public function setAppend($append);

    /**
     * Is this append to child  with the same capture?
     *
     * @return bool
     */
    public function isAppend();
}
