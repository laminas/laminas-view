<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 */
class Stub2
{
    public $view;

    public function direct()
    {
        return 'bar';
    }

    public function setView(\Laminas\View\View $view)
    {
        $this->view = $view;
        return $this;
    }
}
