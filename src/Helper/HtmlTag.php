<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\HtmlAttributesSet;

use function sprintf;

/**
 * Renders <html> tag (both opening and closing) of a web page, to which some custom
 * attributes can be added dynamically.
 */
final class HtmlTag implements StatefulHelperInterface
{
    /**
     * Attributes for the <html> tag.
     *
     * @var array<string, scalar>
     */
    private array $attributes = [];

    /**
     * Whether to add the relevant namespaces depending on the doctype
     */
    private bool $addNamespace = false;

    public function __construct(
        private readonly EscaperInterface $escaper,
        private readonly Doctype $doctype,
    ) {
    }

    public function resetState(): void
    {
        $this->attributes   = [];
        $this->addNamespace = false;
    }

    /**
     * Retrieve object instance; optionally add attributes.
     *
     * @param array<string, scalar> $attributes
     */
    public function __invoke(array $attributes = []): self
    {
        if ($attributes !== []) {
            $this->attributes = $attributes;
        }

        return $this;
    }

    /**
     * Add an attribute to the <html> tag
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Add new or overwrite the existing attributes.
     *
     * @param array<string, scalar> $attributes
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addXhtmlNamespace(bool $flag): self
    {
        $this->addNamespace = $flag;

        return $this;
    }

    /**
     * Render opening tag.
     */
    public function openTag(): string
    {
        $attributes = $this->attributes;

        if ($this->doctype->isXhtml() && $this->addNamespace) {
            $attributes['xmlns'] = 'https://www.w3.org/1999/xhtml';
        }

        return sprintf('<html%s>', new HtmlAttributesSet($this->escaper, $attributes));
    }

    /**
     * Render closing tag.
     */
    public function closeTag(): string
    {
        return '</html>';
    }
}
