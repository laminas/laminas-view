<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Helper\Escaper\AbstractHelper as AbstractEscapeHelper;
use Laminas\View\HtmlAttributesSet;
use Laminas\View\Renderer\PhpRenderer;

use function assert;

/**
 * Helper for creating HtmlAttributesSet objects
 */
class HtmlAttributes extends AbstractHelper
{
    /**
     * Returns a new HtmlAttributesSet object, optionally initializing it with
     * the provided value.
     *
     * @param iterable<string, scalar|array|null> $attributes
     */
    public function __invoke(iterable $attributes = []): HtmlAttributesSet
    {
        $renderer = $this->getView();
        assert($renderer instanceof PhpRenderer);
        $escapePlugin = $renderer->plugin('escapeHtml');
        assert($escapePlugin instanceof AbstractEscapeHelper);
        $escaper = $escapePlugin->getEscaper();

        return new HtmlAttributesSet(
            $escaper,
            $attributes
        );
    }
}
