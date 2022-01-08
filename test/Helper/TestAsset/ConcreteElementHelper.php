<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\AbstractHtmlElement;

final class ConcreteElementHelper extends AbstractHtmlElement
{
    public function normalizeElementId(string $id): string
    {
        return $this->normalizeId($id);
    }

    public function compileAttributes(array $attributes): string
    {
        return $this->htmlAttribs($attributes);
    }
}
