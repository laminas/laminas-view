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
use function sprintf;
use function str_repeat;
use function trim;

use const PHP_EOL;

/**
 * This class is not designed for user inheritance and should be considered internal.
 *
 * @psalm-inheritors HeadScript|InlineScript
 */
abstract class AbstractJavascriptHelper implements StatefulHelperInterface, Stringable
{
    /** @var Container<Tag> */
    private Container $container;
    private string $separator;
    private string $indent;
    private Position|null $capturePosition = null;
    /** @var array<string, scalar> */
    private array $captureAttributes = [];

    final public function __construct(
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

    final public function resetState(): void
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

    /** Instance Accessor */
    final public function __invoke(): self
    {
        return $this;
    }

    /** @param array<string, scalar> $attributes */
    private function isEmptyScript(string|null $content, array $attributes): bool
    {
        return ($content === null || trim($content) === '')
            && (! isset($attributes['src']) || ! is_string($attributes['src']) || $attributes['src'] === '');
    }

    /** @param array<string, scalar> $attributes */
    final public function appendScript(string $content, array $attributes = []): self
    {
        if ($this->isEmptyScript($content, $attributes)) {
            return $this;
        }

        $this->container->append(new Tag('script', $attributes, $content));

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    final public function prependScript(string $content, array $attributes = []): self
    {
        if ($this->isEmptyScript($content, $attributes)) {
            return $this;
        }

        $this->container->prepend(new Tag('script', $attributes, $content));

        return $this;
    }

    /** @param array<string, scalar> $attributes */
    final public function setScript(string $content, array $attributes = []): self
    {
        if ($this->isEmptyScript($content, $attributes)) {
            return $this;
        }

        $this->container->set(new Tag('script', $attributes, $content));

        return $this;
    }

    /**
     * When a file src is added, make sure it is not duplicated
     *
     * @param non-empty-string $src
     */
    private function removeMatchingSrc(string $src): void
    {
        $match = $this->container->firstMatch(
            static fn (Tag $tag): bool => ($tag->attributes['src'] ?? null) === $src,
        );

        if ($match === null) {
            return;
        }

        $this->container->remove($match);
    }

    /**
     * @param non-empty-string $src
     * @param array<string, scalar> $attributes
     */
    final public function appendFile(string $src, array $attributes = []): self
    {
        $this->removeMatchingSrc($src);
        $attributes['src'] = $src;
        $this->container->append(new Tag('script', $attributes));

        return $this;
    }

    /**
     * @param non-empty-string $src
     * @param array<string, scalar> $attributes
     */
    final public function prependFile(string $src, array $attributes = []): self
    {
        $this->removeMatchingSrc($src);
        $attributes['src'] = $src;
        $this->container->prepend(new Tag('script', $attributes));

        return $this;
    }

    /**
     * @param non-empty-string $src
     * @param array<string, scalar> $attributes
     */
    final public function setFile(string $src, array $attributes = []): self
    {
        $attributes['src'] = $src;
        $this->container->set(new Tag('script', $attributes));

        return $this;
    }

    /** @param int<1, max>|string $indent */
    final public function setIndent(int|string $indent): self
    {
        $this->indent = is_int($indent)
            ? str_repeat(' ', $indent)
            : $indent;

        return $this;
    }

    final public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Render aggregated style tags to a string
     *
     * @param int<1, max>|string|null $indent
     */
    final public function toString(int|string|null $indent = null): string
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

    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Start capturing JS to the output buffer
     *
     * @param array<string, scalar> $attributes Optional script tag attributes
     * @throws RuntimeException
     */
    final public function captureStart(
        Position $position = Position::Append,
        array $attributes = [],
    ): void {
        $this->container->captureStart();
        $this->capturePosition   = $position;
        $this->captureAttributes = $attributes;
    }

    /**
     * Finish capturing the output buffer and store the content as a script tag
     */
    final public function captureEnd(): void
    {
        $content = $this->container->captureEnd();
        assert($this->capturePosition !== null);
        match ($this->capturePosition) {
            Position::Append => $this->appendScript($content, $this->captureAttributes),
            Position::Prepend => $this->prependScript($content, $this->captureAttributes),
            Position::Set => $this->setScript($content, $this->captureAttributes),
        };

        $this->capturePosition   = null;
        $this->captureAttributes = [];
    }

    private function itemToString(Tag $item): string
    {
        $attributes = $item->attributes;
        // Add the `type` attribute if otherwise unset and the doctype is not HTML5
        if (! $this->doctype->isHtml5() && ! isset($attributes['type'])) {
            $attributes['type'] = 'text/javascript';
        }

        return sprintf(
            '<script%s>%s</script>',
            (string) new HtmlAttributesSet($this->escaper, $attributes),
            $item->content === null ? '' : PHP_EOL . $item->content . PHP_EOL,
        );
    }
}
