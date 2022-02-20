<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\Escaper\AbstractHelper as AbstractEscapeHelper;
use Laminas\View\HtmlAttributesSet;
use Laminas\View\Renderer\PhpRenderer;

use function assert;

/**
 * Helper for creating HtmlAttributesSet objects
 */
class HtmlAttributes extends AbstractHelper
{
    use DeprecatedAbstractHelperHierarchyTrait;

    private Escaper $escaper;

    public function __construct(?Escaper $escaper = null)
    {
        $this->escaper = $escaper ?: new Escaper();
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
