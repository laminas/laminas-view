<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\HTML\Tag;
use Laminas\View\HtmlAttributesSet;
use Stringable;

use function array_map;
use function array_values;
use function implode;
use function sprintf;

use const PHP_EOL;

final class HeadLink implements StatefulHelperInterface, Stringable
{
    /** @var list<Tag> */
    private array $items;
    private string $indent;
    private string $separator;

    public function __construct(
        private readonly EscaperInterface $escaper,
        private readonly Doctype $doctype,
        private readonly string $defaultSeparator = PHP_EOL,
        private readonly string $defaultIndent = '',
    ) {
        $this->items     = [];
        $this->indent    = $this->defaultIndent;
        $this->separator = $this->defaultSeparator;
    }

    public function resetState(): void
    {
        $this->items     = [];
        $this->separator = $this->defaultSeparator;
        $this->indent    = $this->defaultIndent;
    }

    /**
     * Allows helper retrieval from a template, optionally appending a new <link> element to the list
     *
     * @param array<string, scalar>|null $attributes
     */
    public function __invoke(array|null $attributes = null): self
    {
        if ($attributes !== null) {
            $this->appendItem($this->createItem($attributes));
        }

        return $this;
    }

    public function setIndent(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Append a link with any specification
     *
     * @param array<string, scalar> $attributes
     */
    public function append(array $attributes): self
    {
        $this->appendItem($this->createItem($attributes));

        return $this;
    }

    /**
     * Prepend a link with any specification
     *
     * @param array<string, scalar> $attributes
     */
    public function prepend(array $attributes): self
    {
        $this->prependItem($this->createItem($attributes));

        return $this;
    }

    /**
     * Reset the list with the provided link specification
     *
     * @param array<string, scalar> $attributes
     */
    public function set(array $attributes): self
    {
        $this->setItem($this->createItem($attributes));

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    private function createItem(array $attributes): Tag
    {
        return new Tag('link', $attributes);
    }

    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    private function stylesheet(string $href, array $attributes): Tag
    {
        $attributes['rel']  = 'stylesheet';
        $attributes['href'] = $href;
        $attributes['type'] = 'text/css';

        return $this->createItem($attributes);
    }

    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    public function appendStylesheet(string $href, array $attributes = []): self
    {
        $this->appendItem($this->stylesheet($href, $attributes));

        return $this;
    }

    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    public function prependStylesheet(string $href, array $attributes = []): self
    {
        $this->prependItem($this->stylesheet($href, $attributes));

        return $this;
    }

    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    public function setStylesheet(string $href, array $attributes = []): self
    {
        $this->setItem($this->stylesheet($href, $attributes));

        return $this;
    }

    private function appendItem(Tag $item): void
    {
        $this->unsetMatchingItem($item);

        $this->items[] = $item;
    }

    private function prependItem(Tag $item): void
    {
        $this->unsetMatchingItem($item);

        $this->items = [$item, ...$this->items];
    }

    private function setItem(Tag $item): void
    {
        $this->items = [$item];
    }

    private function unsetMatchingItem(Tag $item): void
    {
        $list = $this->items;
        foreach ($list as $index => $tag) {
            if (
                isset($tag->attributes['rel'])
                &&
                isset($item->attributes['rel'])
                &&
                $tag->attributes['rel'] !== $item->attributes['rel']
            ) {
                continue;
            }

            if (
                isset($tag->attributes['href'])
                &&
                isset($item->attributes['href'])
                &&
                $tag->attributes['href'] !== $item->attributes['href']
            ) {
                continue;
            }

            unset($list[$index]);
            break;
        }

        $this->items = array_values($list);
    }

    /**
     * Create HTML link element from data item
     */
    private function itemToString(Tag $item): string
    {
        if ($item->attributes === []) {
            return '';
        }

        $attributes = new HtmlAttributesSet($this->escaper, $item->attributes);

        return sprintf(
            '<%s%s%s>',
            $item->tag,
            (string) $attributes,
            $this->doctype->isXhtml() ? ' /' : '',
        );
    }

    public function toString(string|null $indent = null): string
    {
        $indent = $this->escaper->escapeHtml($indent ?? $this->indent);

        return implode(
            $this->separator,
            array_map(
                fn (Tag $tag): string => $indent . $this->itemToString($tag),
                $this->items,
            ),
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
