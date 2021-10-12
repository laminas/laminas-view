<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View;

use ArrayObject;
use Laminas\Escaper\Escaper;

/**
 * Class for storing and processing HTML tag attributes.
 */
class HtmlAttributesSet extends ArrayObject
{
    /**
     * HTML escaper
     *
     * @var Escaper
     */
    protected $htmlEscaper;

    /**
     * HTML attribute escaper
     *
     * @var Escaper
     */
    protected $htmlAttributeEscaper;

    /**
     * Constructor.
     *
     * @param Escaper $htmlEscaper General HTML escaper
     * @param Escaper $htmlAttributeEscaper Escaper for use with HTML attributes
     * @param iterable $attributes Attributes to manage
     */
    public function __construct(Escaper $htmlEscaper, Escaper $htmlAttributeEscaper, iterable $attributes = [])
    {
        parent::__construct();
        $this->htmlEscaper = $htmlEscaper;
        $this->htmlAttributeEscaper = $htmlAttributeEscaper;
        foreach ($attributes as $name => $value) {
            $this->offsetSet($name, $value);
        }
    }

    /**
     * Set several attributes at once.
     */
    public function set(iterable $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this[$name] = $value;
        }
        return $this;
    }

    /**
     * Add a value to an attribute.
     *
     * Sets the attribute if it does not exist.
     *
     * @param $value string|array Value
     */
    public function add(string $name, $value): self
    {
        $this->offsetSet(
            $name,
            $this->offsetExists($name)
                ? array_merge((array) $this->offsetGet($name), (array) $value)
                : $value
        );
        return $this;
    }

    /**
     * Merge attributes with existing attributes.
     */
    public function merge(iterable $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->add($name, $value);
        }
        return $this;
    }

    /**
     * Does a specific attribute with a specific value exist?
     */
    public function hasValue(string $name, string $value): bool
    {
        if (! $this->offsetExists($name)) {
            return false;
        }

        $storeValue = $this->offsetGet($name);
        if (is_array($storeValue)) {
            return in_array($value, $storeValue);
        }

        return $value === $storeValue;
    }

    /**
     * Return a string of tag attributes.
     */
    public function __toString(): string
    {
        $xhtml = '';

        foreach ($this->getArrayCopy() as $key => $value) {
            $key = $this->htmlEscaper->escapeHtml($key);

            if ((0 === strpos($key, 'on') || ('constraints' === $key)) && ! is_scalar($value)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                // non-scalar data should be cast to JSON first
                $value = json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            }

            if (0 !== strpos($key, 'on') && 'constraints' !== $key && is_array($value)) {
                // Non-event keys and non-constraints keys with array values
                // should have values separated by whitespace
                $value = implode(' ', $value);
            }

            $value  = $this->htmlAttributeEscaper->escapeHtmlAttr($value);
            $quote  = strpos($value, '"') !== false ? "'" : '"';
            $xhtml .= sprintf(' %2$s=%1$s%3$s%1$s', $quote, $key, $value);
        }

        return $xhtml;
    }
}
