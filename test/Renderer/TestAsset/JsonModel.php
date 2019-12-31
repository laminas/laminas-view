<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Renderer\TestAsset;

use JsonSerializable;

class JsonModel implements JsonSerializable
{
    public $value = false;

    public function jsonSerialize()
    {
        return $this->value;
    }
}
