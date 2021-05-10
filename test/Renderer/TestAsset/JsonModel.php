<?php

namespace LaminasTest\View\Renderer\TestAsset;

use JsonSerializable;

class JsonModel implements JsonSerializable
{
    public $value = false;

    public function jsonSerialize()
    {
        return $this->value;
    }
}
