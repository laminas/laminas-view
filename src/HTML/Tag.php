<?php

declare(strict_types=1);

namespace Laminas\View\HTML;

use function array_change_key_case;
use function array_key_exists;
use function ksort;
use function strtolower;

use const CASE_LOWER;

/**
 * This class is not part of the public API and has no BC guarantees
 *
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Tag
{
    /** @var array<string, scalar> */
    public array $attributes;

    /**
     * @param non-empty-string $tag
     * @param array<string, scalar> $attributes
     */
    public function __construct(
        public string $tag,
        array $attributes = [],
        public string|null $content = null,
    ) {
        $attributes = array_change_key_case($attributes, CASE_LOWER);
        ksort($attributes);

        $this->attributes = $attributes;
    }

    public function equals(self $other): bool
    {
        return $this->tag === $other->tag
            && $this->attributes === $other->attributes
            && $this->content === $other->content;
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->attributes);
    }

    public function getAttribute(string $name): int|float|string|bool|null
    {
        $name = strtolower($name);

        return $this->attributes[$name] ?? null;
    }
}
