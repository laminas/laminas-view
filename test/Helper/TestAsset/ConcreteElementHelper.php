<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\AbstractHtmlElement;

final class ConcreteElementHelper extends AbstractHtmlElement
{
    /** @return string */
    public function normalizeElementId(string $id)
    {
        return $this->normalizeId($id);
    }

    /** @return string */
    public function compileAttributes(array $attributes)
    {
        return $this->htmlAttribs($attributes);
    }
}
