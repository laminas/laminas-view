<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\HTML\Tag;
use Laminas\View\HtmlAttributesSet;
use Stringable;

use function array_filter;
use function array_map;
use function array_unshift;
use function array_values;
use function implode;
use function is_int;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

final class HeadMeta implements StatefulHelperInterface, Stringable
{
    /** @var list<Tag> */
    private array $items = [];
    private string $indent;
    private string $separator;

    public function __construct(
        private readonly Doctype $doctype,
        private readonly EscaperInterface $escaper,
        private readonly string $defaultSeparator = PHP_EOL,
        private readonly string $defaultIndent = '',
    ) {
        $this->indent    = $this->defaultIndent;
        $this->separator = $this->defaultSeparator;
    }

    public function resetState(): void
    {
        $this->items     = [];
        $this->indent    = $this->defaultIndent;
        $this->separator = $this->defaultSeparator;
    }

    /**
     * Retrieve object instance; optionally add meta tag
     *
     * @param array<string, scalar> $attributes
     */
    public function __invoke(
        string|null $name = null,
        string|null $content = null,
        array $attributes = [],
    ): self {
        if ($name !== null && $content !== null) {
            return $this->appendName($name, $content, $attributes);
        }

        if ($attributes === []) {
            return $this;
        }

        return $this->append($attributes);
    }

    /**
     * Render placeholder as string
     */
    public function toString(string|int|null $indent = null): string
    {
        $indent   = is_int($indent) ? str_repeat(' ', $indent) : $indent;
        $indent ??= $this->indent;
        $indent   = $this->escaper->escapeHtml($indent);

        return implode($this->escaper->escapeHtml($this->separator), array_map(
            function (Tag $tag) use ($indent): string {
                return $indent . $this->itemToString($tag);
            },
            $this->items,
        ));
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function itemToString(Tag $item): string
    {
        $attributes = new HtmlAttributesSet($this->escaper, $item->attributes);
        $closing    = $this->doctype->isXhtml() ? ' /' : '';

        return sprintf('<%s%s%s>', $item->tag, (string) $attributes, $closing);
    }

    /** @param array<string, scalar> $attributes */
    public function append(array $attributes): self
    {
        if ($attributes === []) {
            return $this;
        }

        $tag = new Tag('meta', $attributes);
        foreach ($this->items as $item) {
            if ($item->equals($tag)) {
                return $this;
            }
        }

        $this->items[] = $tag;

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    public function prepend(array $attributes): self
    {
        if ($attributes === []) {
            return $this;
        }

        $tag = new Tag('meta', $attributes);
        foreach ($this->items as $item) {
            if ($item->equals($tag)) {
                return $this;
            }
        }

        array_unshift($this->items, $tag);

        return $this;
    }

    /**
     * Create an HTML5-style meta charset tag. Something like <meta charset="utf-8">
     */
    public function setCharset(string $charset): self
    {
        $this->clearByAttribute('charset');
        $this->prepend(['charset' => $charset]);

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    public function setName(string $name, string $content, array $attributes = []): self
    {
        $this->clearByAttributeValue('name', $name);
        $attributes['name']    = $name;
        $attributes['content'] = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function appendName(string $name, string $content, array $attributes = []): self
    {
        $attributes['name']    = $name;
        $attributes['content'] = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function prependName(string $name, string $content, array $attributes = []): self
    {
        $attributes['name']    = $name;
        $attributes['content'] = $content;

        return $this->prepend($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function setItemprop(string $itemprop, string $content, array $attributes = []): self
    {
        $this->clearByAttributeValue('itemprop', $itemprop);
        $attributes['itemprop'] = $itemprop;
        $attributes['content']  = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function appendItemprop(string $itemprop, string $content, array $attributes = []): self
    {
        $attributes['itemprop'] = $itemprop;
        $attributes['content']  = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function prependItemprop(string $itemprop, string $content, array $attributes = []): self
    {
        $attributes['itemprop'] = $itemprop;
        $attributes['content']  = $content;

        return $this->prepend($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function setProperty(string $property, string $content, array $attributes = []): self
    {
        $this->clearByAttributeValue('property', $property);
        $attributes['property'] = $property;
        $attributes['content']  = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function appendProperty(string $property, string $content, array $attributes = []): self
    {
        $attributes['property'] = $property;
        $attributes['content']  = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function prependProperty(string $property, string $content, array $attributes = []): self
    {
        $attributes['property'] = $property;
        $attributes['content']  = $content;

        return $this->prepend($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function setHttpEquiv(string $httpEquiv, string $content, array $attributes = []): self
    {
        $this->clearByAttributeValue('http-equiv', $httpEquiv);
        $attributes['http-equiv'] = $httpEquiv;
        $attributes['content']    = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function appendHttpEquiv(string $httpEquiv, string $content, array $attributes = []): self
    {
        $attributes['http-equiv'] = $httpEquiv;
        $attributes['content']    = $content;

        return $this->append($attributes);
    }

    /** @param array<string, scalar> $attributes */
    public function prependHttpEquiv(string $httpEquiv, string $content, array $attributes = []): self
    {
        $attributes['http-equiv'] = $httpEquiv;
        $attributes['content']    = $content;

        return $this->prepend($attributes);
    }

    private function clearByAttribute(string $attribute): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            static fn (Tag $item): bool => ! $item->hasAttribute($attribute),
        ));
    }

    private function clearByAttributeValue(string $name, int|string|float|bool $value): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            static fn (Tag $item): bool => (! $item->hasAttribute($name)) || $item->getAttribute($name) !== $value,
        ));
    }

    public function setIndent(string|int $indent): self
    {
        $this->indent = is_int($indent)
            ? str_repeat(' ', $indent)
            : $indent;

        return $this;
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }
}
