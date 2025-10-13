<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\EscaperInterface;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Helper\Placeholder\Position;
use Laminas\View\HTML\Tag;
use Laminas\View\HtmlAttributesSet;
use Stringable;

use function assert;
use function implode;
use function is_int;
use function is_string;
use function preg_replace;
use function str_repeat;

use const PHP_EOL;

/**
 * Helper for adding inline CSS to the head in style tags
 */
final class HeadStyle implements StatefulHelperInterface, Stringable
{
    /** @var Container<Tag> */
    private Container $container;
    private string $separator;
    private string $indent;
    private Position|null $capturePosition = null;
    /** @var array<string, scalar> */
    private array $captureAttributes = [];

    public function __construct(
        private readonly EscaperInterface $escaper,
        private readonly Doctype $doctype,
        private readonly string $defaultSeparator = PHP_EOL,
        private readonly string $defaultIndent = '',
    ) {
        /** @psalm-var Container<Tag> */
        $this->container = new Container();
        $this->separator = $this->defaultSeparator;
        $this->indent    = $this->defaultIndent;
    }

    public function resetState(): void
    {
        if ($this->container->isCapturing()) {
            $this->container->captureEnd();
        }

        /** @psalm-var Container<Tag> */
        $this->container         = new Container();
        $this->separator         = $this->defaultSeparator;
        $this->indent            = $this->defaultIndent;
        $this->capturePosition   = null;
        $this->captureAttributes = [];
    }

    /**
     * Return headStyle object
     *
     * Returns headStyle helper object; optionally, appends a new style element to the list
     *
     * @param string|null $content CSS to add to a style element
     * @param array<string, scalar> $attributes to apply to the style element
     */
    public function __invoke(
        string|null $content = null,
        array $attributes = [],
        Position $position = Position::Append,
    ): self {
        if ($content !== null) {
            match ($position) {
                Position::Append => $this->appendStyle($content, $attributes),
                Position::Prepend => $this->prependStyle($content, $attributes),
                Position::Set => $this->setStyle($content, $attributes),
            };
        }

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    public function appendStyle(string $content, array $attributes = []): self
    {
        if ($content === '') {
            return $this;
        }

        $this->container->append(new Tag('style', $attributes, $content));

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    public function prependStyle(string $content, array $attributes = []): self
    {
        if ($content === '') {
            return $this;
        }

        $this->container->prepend(new Tag('style', $attributes, $content));

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    public function setStyle(string $content, array $attributes = []): self
    {
        if ($content === '') {
            return $this;
        }

        $this->container->set(new Tag('style', $attributes, $content));

        return $this;
    }

    /** @param int<1, max>|string $indent */
    public function setIndent(int|string $indent): self
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

    /**
     * Render aggregated style tags to a string
     *
     * @param int<1, max>|string|null $indent
     */
    public function toString(int|string|null $indent = null): string
    {
        if ($indent !== null) {
            $this->setIndent($indent);
        }

        $items = [];
        foreach ($this->container as $item) {
            $items[] = $this->itemToString($item);
        }

        $content = implode($this->separator, $items);
        $content = preg_replace("/(\r\n?|\n)/", '$1' . $this->indent, $content);
        assert(is_string($content));

        return $this->indent . $content;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Start capturing CSS to the output buffer
     *
     * @param array<string, scalar> $attributes Optional style tag attributes
     * @throws RuntimeException
     */
    public function captureStart(
        Position $position = Position::Append,
        array $attributes = [],
    ): void {
        $this->container->captureStart();
        $this->capturePosition   = $position;
        $this->captureAttributes = $attributes;
    }

    /**
     * Finish capturing the output buffer and store the content as a style tag
     */
    public function captureEnd(): void
    {
        $content = $this->container->captureEnd();
        assert($this->capturePosition !== null);
        match ($this->capturePosition) {
            Position::Append => $this->appendStyle($content, $this->captureAttributes),
            Position::Prepend => $this->prependStyle($content, $this->captureAttributes),
            Position::Set => $this->setStyle($content, $this->captureAttributes),
        };

        $this->capturePosition   = null;
        $this->captureAttributes = [];
    }

    private function itemToString(Tag $item): string
    {
        assert($item->content !== null);

        $attributes = $item->attributes;
        if (! $this->doctype->isHtml5()) {
            $attributes['type'] = 'text/css';
        }

        $attributes = (string) new HtmlAttributesSet($this->escaper, $attributes);

        return <<<HTML
            <style{$attributes}>
            {$item->content}
            </style>
            HTML;
    }
}
