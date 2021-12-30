<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\AbstractHelper;

class ConcreteHelper extends AbstractHelper
{
    public function __invoke(string $output): string
    {
        return $output;
    }
}
