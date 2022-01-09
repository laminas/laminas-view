<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

class ToArray
{
    /** @var mixed[] */
    public $array = [];

    public function __construct()
    {
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return $this->array;
    }
}
