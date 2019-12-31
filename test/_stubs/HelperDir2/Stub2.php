<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Zend\View\Helper;

class Stub2
{
    public $view;

    public function direct()
    {
        return 'bar';
    }

    public function setView(\Zend\View\View $view)
    {
        $this->view = $view;
        return $this;
    }
}
