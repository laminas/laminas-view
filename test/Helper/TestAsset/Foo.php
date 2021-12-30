<?php

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\Placeholder\Container\AbstractStandalone;

class Foo extends AbstractStandalone
{
    // @codingStandardsIgnoreStart
    protected $_regKey = 'foo';
    // @codingStandardsIgnoreEnd
    public function direct(): void
    {
    }
}
