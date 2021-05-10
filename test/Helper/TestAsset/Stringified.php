<?php

namespace LaminasTest\View\Helper\TestAsset;

class Stringified
{
    public function __toString()
    {
        return get_class($this);
    }
}
