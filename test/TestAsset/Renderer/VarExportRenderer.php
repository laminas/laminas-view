<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\TestAsset\Renderer;

use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\ResolverInterface as Resolver;

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTest
 */
class VarExportRenderer implements Renderer
{
    public function getEngine()
    {
        return 'var_export';
    }

    public function setResolver(Resolver $resolver)
    {
        // Deliberately empty
    }

    public function render($nameOrModel, $values = null)
    {
        if (!$nameOrModel instanceof Model) {
            return var_export($nameOrModel, true);
        }

        $values = $nameOrModel->getVariables();
        return var_export($values, true);
    }
}
