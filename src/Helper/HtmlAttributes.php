<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\HtmlAttributesSet;

/**
 * Helper for creating HtmlAttributesSet objects
 */
final class HtmlAttributes
{
    public function __construct(private readonly Escaper $escaper)
    {
    }

    /**
     * Returns a new HtmlAttributesSet object, optionally initializing it with
     * the provided value.
     *
     * @param iterable<string, scalar|array|null> $attributes
     */
    public function __invoke(iterable $attributes = []): HtmlAttributesSet
    {
        return new HtmlAttributesSet(
            $this->escaper,
            $attributes
        );
    }
}
