<?php

namespace LaminasTest\View\TestAsset;

class VariableFunctor
{
    public $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return $this->value;
    }
}
