<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Renderer\TestAsset;

use JsonSerializable;
use ReturnTypeWillChange;

class JsonModel implements JsonSerializable
{
    public $value = false;

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->value;
    }
}
