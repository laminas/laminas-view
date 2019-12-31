<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\TestAsset;

use Zend\View\Helper\AbstractHelper as Helper;

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
