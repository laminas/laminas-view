<?php

namespace LaminasTest\View\Renderer\TestAsset;

use JsonSerializable;
use ReturnTypeWillChange; // phpcs:ignore

class JsonModel implements JsonSerializable
{
    public $value = false;

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->value;
    }
}
