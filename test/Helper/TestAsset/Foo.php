<?php

namespace LaminasTest\View\Helper\TestAsset;

class Foo extends \Laminas\View\Helper\Placeholder\Container\AbstractStandalone
{
    // @codingStandardsIgnoreStart
    protected $_regKey = 'foo';
    // @codingStandardsIgnoreEnd
    public function direct(): void
    {
    }
}
