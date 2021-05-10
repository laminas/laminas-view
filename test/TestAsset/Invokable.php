<?php

namespace LaminasTest\View\TestAsset;

use Laminas\View\Helper\AbstractHelper as Helper;

class Invokable extends Helper
{
    /**
     * Invokable functor
     *
     * @param  string $message
     * @return string
     */
    public function __invoke($message)
    {
        return __METHOD__ . ': ' . $message;
    }
}
