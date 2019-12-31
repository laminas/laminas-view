<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\TestAsset;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTest
 */
class VariableFunctor
{
    public $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return $this->value;
    }
}
