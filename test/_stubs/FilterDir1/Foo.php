<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Filter;

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 */
class Foo
{
    public function filter($buffer)
    {
        return 'foo';
    }
}
