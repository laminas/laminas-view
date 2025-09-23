<?php

declare(strict_types=1);

namespace Laminas\View;

use ArrayObject;
use Laminas\Escaper\EscaperInterface;
use Laminas\View\Exception\InvalidArgumentException;
use Stringable;
use Traversable;

use function array_change_key_case;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function get_debug_type;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_scalar;
use function iterator_to_array;
use function json_encode;
use function sprintf;
use function str_contains;
use function str_starts_with;

use const CASE_LOWER;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;

/**
 * Class for storing and processing HTML tag attributes.
 *
 * @psalm-type AttributeSet = array<string, scalar|array|null>
 * @extends ArrayObject<string, scalar|array|null>
 */
final class HtmlAttributesSet extends ArrayObject implements Stringable
{
    /**
     * These attributes can be arrays, and when encountered will be joined with the mapped separator character
     */
    private const ARRAY_VALUES = [
        'accept'    => ',',
        'allow'     => ' ',
        'class'     => ' ',
        'accesskey' => ' ',
        'part'      => ' ',
        'rel'       => ' ',
        'sizes'     => ',',
        'srcset'    => ',',
    ];

    /**
     * For array attributes that are not mapped to a separator, this character is the default
     */
    private const DEFAULT_SEPARATOR = ' ';

    /** @param iterable<string, scalar|array|null> $attributes */
    public function __construct(private readonly EscaperInterface $escaper, iterable $attributes = [])
    {
        $attributes = $attributes instanceof Traversable
            ? iterator_to_array($attributes, true)
            : $attributes;
        parent::__construct($attributes);
    }

    /**
     * Set several attributes at once.
     *
     * @param AttributeSet $attributes
     */
    public function set(iterable $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->offsetSet($name, $value);
        }

        return $this;
    }

    /**
     * Add a value to an attribute.
     *
     * Sets the attribute if it does not exist.
     */
    public function add(string $name, string|int|float|bool|array|null $value): self
    {
        $this->offsetSet(
            $name,
            $this->offsetExists($name)
                ? array_merge((array) $this->offsetGet($name), (array) $value)
                : $value,
        );

        return $this;
    }

    /**
     * Merge attributes with existing attributes.
     *
     * @param AttributeSet $attributes
     */
    public function merge(iterable $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->add($name, $value);
        }

        return $this;
    }

    /**
     * Whether the named attribute equals or contains the given value
     */
    public function hasValue(string $name, string|int|float|bool|array|null $value): bool
    {
        if (! $this->offsetExists($name)) {
            return false;
        }

        $storeValue = $this->offsetGet($name);
        if (is_array($storeValue) && is_scalar($value)) {
            return in_array($value, $storeValue, true);
        }

        return $value === $storeValue;
    }

    /**
     * @param AttributeSet $attributes
     * @return array<string, string|bool>
     */
    private function normalise(array $attributes): array
    {
        $out        = [];
        $attributes = array_change_key_case($attributes, CASE_LOWER);
        foreach ($attributes as $name => $value) {
            if ($value === false) {
                continue;
            }

            if ($value === true) {
                $out[$name] = true;
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $out[$name] = (string) $value;

                continue;
            }

            if (array_key_exists($name, self::ARRAY_VALUES)) {
                $out[$name] = $this->stringifyList($value, $name, self::ARRAY_VALUES[$name]);

                continue;
            }

            // For legacy compat, event handlers given as arrays are JSON encoded for some reason.
            if (str_starts_with($name, 'on')) {
                $flags      = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_THROW_ON_ERROR;
                $out[$name] = json_encode($value, $flags);

                continue;
            }

            $out[$name] = $this->stringifyList($value, $name, self::DEFAULT_SEPARATOR);
        }

        return $out;
    }

    /**
     * @param array<array-key, mixed> $values
     * @throws InvalidArgumentException
     */
    private function stringifyList(array $values, string $name, string $separator): string
    {
        $list = [];
        foreach ($values as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (! is_scalar($value)) {
                throw new InvalidArgumentException(sprintf(
                    'The attribute "%s" is an array, but members must be scalar. %s received',
                    $name,
                    get_debug_type($value),
                ));
            }

            $list[] = (string) $value;
        }

        return implode($separator, $list);
    }

    /**
     * Return a string of tag attributes.
     */
    public function __toString(): string
    {
        $this->ksort();
        $attributes = $this->normalise($this->getArrayCopy());
        if ($attributes === []) {
            return '';
        }
        $attributes = array_map(
            function (string|bool $value, string $name): string {
                // The most compatible (but verbose) way of handling boolean attributes is name="name"
                if (is_bool($value)) {
                    return sprintf('%s="%s"', $this->escaper->escapeHtml($name), $this->escaper->escapeHtmlAttr($name));
                }

                $quote = str_contains($value, '"') ? "'" : '"';

                return sprintf(
                    '%1$s=%2$s%3$s%2$s',
                    $this->escaper->escapeHtml($name),
                    $quote,
                    $this->escaper->escapeHtmlAttr($value),
                );
            },
            $attributes,
            array_keys($attributes),
        );

        return ' ' . implode(' ', $attributes);
    }
}
