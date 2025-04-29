<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Closure;
use Laminas\Escaper\Escaper;
use Laminas\Translator\TranslatorInterface;
use Stringable;

use function array_map;
use function array_unshift;
use function implode;
use function is_int;
use function sprintf;
use function str_repeat;

/**
 * Helper for setting and retrieving title element for HTML head.
 */
final class HeadTitle implements Stringable, StatefulHelperInterface
{
    /** @var list<string> $items */
    private array $items = [];
    private readonly Escaper $escaper;
    private string|null $separator;
    private string|null $indent;
    private string|null $prefix;
    private string|null $postfix;

    /**
     * @param non-empty-string $translatorTextDomain
     */
    public function __construct(
        Escaper|null $escaper = null,
        private readonly bool $autoEscape = true,
        private readonly string $defaultSeparator = '',
        private readonly string $defaultIndent = '',
        private readonly string $defaultPrefix = '',
        private readonly string $defaultPostfix = '',
        private readonly TranslatorInterface|null $translator = null,
        private readonly string $translatorTextDomain = 'default',
    ) {
        $this->escaper   = $escaper ?? new Escaper();
        $this->separator = null;
        $this->indent    = null;
        $this->prefix    = null;
        $this->postfix   = null;
    }

    public function resetState(): void
    {
        $this->items     = [];
        $this->separator = null;
        $this->indent    = null;
        $this->prefix    = null;
        $this->postfix   = null;
    }

    public function __invoke(string|null $title = null): self
    {
        if ($title !== null && $title !== '') {
            $this->append($title);
        }

        return $this;
    }

    public function append(string $value): self
    {
        $this->items[] = $value;

        return $this;
    }

    public function prepend(string $value): self
    {
        array_unshift($this->items, $value);

        return $this;
    }

    public function set(string $value): self
    {
        $this->items = [$value];

        return $this;
    }

    public function setIndent(string|int $indent): self
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        $this->indent = $indent;

        return $this;
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function setPostfix(string $postfix): self
    {
        $this->postfix = $postfix;

        return $this;
    }

    public function toString(): string
    {
        return sprintf(
            '%s<title>%s</title>',
            $this->indent ?? $this->defaultIndent,
            $this->renderTitle(),
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function renderTitle(): string
    {
        $items = array_map(
            ($this->translatorCallback())(...),
            $this->items,
        );

        $content = sprintf(
            '%s%s%s',
            $this->prefix ?? $this->defaultPrefix,
            implode($this->separator ?? $this->defaultSeparator, $items),
            $this->postfix ?? $this->defaultPostfix,
        );

        return $this->autoEscape
            ? $this->escaper->escapeHtml($content)
            : $content;
    }

    /**
     * Create and return a callback for translation of the title items
     *
     * @return Closure(string): string
     */
    private function translatorCallback(): Closure
    {
        $translator = $this->translator;

        if ($translator === null) {
            return static fn (string $value): string => $value;
        }

        return fn (string $value): string => $translator->translate($value, $this->translatorTextDomain);
    }
}
