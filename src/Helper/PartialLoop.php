<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception;
use Traversable;

use function get_debug_type;
use function is_array;
use function iterator_to_array;
use function method_exists;
use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Helper for rendering a template fragment in its own variable scope; iterates
 * over data provided and renders for each iteration.
 */
final class PartialLoop implements StatefulHelperInterface
{
    /**
     * Marker to where the pointer is at in the loop
     */
    private int $partialCounter = 0;

    /**
     * The current nesting level
     */
    private int $nestingLevel = 0;

    /**
     * Stack with object keys for each nested level
     *
     * @var array<int, non-empty-string|null> indexed by nesting level
     */
    private array $objectKeyStack = [
        0 => null,
    ];

    public function __construct(private readonly Partial $partialHelper)
    {
    }

    public function resetState(): void
    {
        $this->partialHelper->resetState();
        $this->partialCounter = 0;
        $this->nestingLevel   = 0;
        $this->objectKeyStack = [
            0 => null,
        ];
    }

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @param non-empty-string|null $name Name of view script
     * @param iterable|object $values Variables to populate in the view
     * @return ($name is string ? string : self)
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke(string|null $name = null, iterable|object $values = []): self|string
    {
        if ($name === null) {
            return $this;
        }

        return $this->loop($name, $values);
    }

    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * @param non-empty-string $name Name of view script
     * @param iterable|object $values Variables to populate in the view
     * @throws Exception\InvalidArgumentException
     */
    private function loop(string $name, iterable|object $values): string
    {
        // reset the counter if it's called again
        $this->partialCounter = 0;
        $content              = '';

        /** @psalm-var mixed $item */
        foreach ($this->extractViewVariables($values) as $item) {
            $this->nestObjectKey();

            $this->partialCounter++;
            $content .= $this->partialHelper->__invoke($name, $item);

            $this->unNestObjectKey();
        }

        return $content;
    }

    /**
     * Get the partial counter
     */
    public function getPartialCounter(): int
    {
        return $this->partialCounter;
    }

    /**
     * Set object key in this loop and any child loop
     *
     * @param non-empty-string|null $key
     */
    public function setObjectKey(string|null $key): self
    {
        if (null === $key) {
            unset($this->objectKeyStack[$this->nestingLevel]);
        } else {
            $this->objectKeyStack[$this->nestingLevel] = $key;
        }

        $this->partialHelper->setObjectKey($key);

        return $this;
    }

    /** @return non-empty-string|null */
    public function getObjectKey(): string|null
    {
        return $this->partialHelper->getObjectKey();
    }

    /**
     * Increment nestedLevel and default objectKey to parent's value
     */
    private function nestObjectKey(): void
    {
        $this->nestingLevel += 1;

        $this->setObjectKey($this->getObjectKey());
    }

    /**
     * Decrement nestedLevel and restore objectKey to parent's value
     */
    private function unNestObjectKey(): void
    {
        $this->setObjectKey(null);

        $this->nestingLevel -= 1;
        if (isset($this->objectKeyStack[$this->nestingLevel])) {
            $this->partialHelper->setObjectKey($this->objectKeyStack[$this->nestingLevel]);
        }
    }

    /**
     * @return array<array-key, mixed> Variables to populate in the view
     */
    private function extractViewVariables(iterable|object $values): array
    {
        if (is_array($values)) {
            return $values;
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

            /** @psalm-var mixed $data */
            $data = $values->toArray();
            if (is_array($data)) {
                return $data;
            }
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'PartialLoop helper requires iterable data, %s given',
            get_debug_type($values),
        ));
    }
}
