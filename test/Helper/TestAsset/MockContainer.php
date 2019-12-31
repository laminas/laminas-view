<?php

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\Placeholder\Container\AbstractContainer;

class MockContainer extends AbstractContainer
{
    public $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }
}
