<?php

namespace LaminasTest\View\Helper\TestAsset;

use function get_class;

class Stringified
{
    public function __toString()
    {
        return get_class($this);
    }
}
