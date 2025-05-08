<?php

declare(strict_types=1);

namespace Laminas\View\HTML;

/**
 * This class is not part of the public API and has no BC guarantees
 *
 * @internal
 */
final class Tag
{
    /**
     * @param non-empty-string $tag
     * @param array<string, scalar> $attributes
     */
    public function __construct(
        public readonly string $tag,
        public array $attributes = [],
    ) {
    }
}
