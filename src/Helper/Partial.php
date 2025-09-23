<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Traversable;

use function get_object_vars;
use function is_array;
use function iterator_to_array;
use function method_exists;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Helper for rendering a template fragment in its own variable scope.
 */
final class Partial implements StatefulHelperInterface
{
    /**
     * Variable to which object will be assigned
     */
    private string|null $objectKey = null;

    public function __construct(private readonly PhpRenderer $renderer)
    {
    }

    public function resetState(): void
    {
        $this->objectKey = null;
    }

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object. It proxies to view's render function
     *
     * @param  non-empty-string|ModelInterface|null $name Name of view script, or a view model
     * @param  iterable<string, mixed>|object|null $values Variables to populate in the view
     * @return ($name is null ? self : string)
     * @throws RuntimeException
     */
    public function __invoke(
        string|ModelInterface|null $name = null,
        iterable|object|null $values = null,
    ): string|self {
        if ($name === null) {
            return $this;
        }

        // If we were passed only a view model, just render it.
        if ($name instanceof ModelInterface) {
            return $this->renderer->render($name);
        }

        return $this->renderer->render($name, $this->extractVariablesForRender($values));
    }

    /**
     * @param iterable<string, mixed>|object|null $values
     * @return iterable<string, mixed>
     */
    private function extractVariablesForRender(iterable|object|null $values): iterable
    {
        if ($values === null) {
            return [];
        }

        if (is_array($values)) {
            return $values;
        }

        if ($values instanceof ModelInterface) {
            return $values->getVariables();
        }

        if ($this->objectKey !== null) {
            return [$this->objectKey => $values];
        }

        if ($values instanceof Traversable) {
            return iterator_to_array($values);
        }

        if (method_exists($values, 'toArray')) {
            trigger_error(
                'Non-iterable objects implementing a `toArray` method will be rejected in version 4.0 '
                . 'of laminas-view ',
                E_USER_DEPRECATED,
            );

            /** @psalm-var mixed $variables */
            $variables = $values->toArray();

            if (is_array($variables)) {
                return $variables; // We cannot guarantee iterable<string, mixed> here
            }
        }

        return get_object_vars($values);
    }

    /**
     * Set object key
     */
    public function setObjectKey(string|null $key): self
    {
        $this->objectKey = $key;

        return $this;
    }

    /**
     * Retrieve object key
     *
     * The objectKey is the variable to which an object in the iterator will be
     * assigned.
     */
    public function getObjectKey(): string|null
    {
        return $this->objectKey;
    }
}
