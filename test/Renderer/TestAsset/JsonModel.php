<?php

declare(strict_types=1);

namespace LaminasTest\View\Renderer\TestAsset;

use JsonSerializable;
use ReturnTypeWillChange; // phpcs:ignore

class JsonModel implements JsonSerializable
{
    /** @var mixed */
    public $value = false;

    /**
     * @return bool|mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->value;
    }
}
