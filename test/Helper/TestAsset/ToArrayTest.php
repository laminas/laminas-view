<?php

namespace LaminasTest\View\Helper\TestAsset;

class ToArrayTest
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }
}
