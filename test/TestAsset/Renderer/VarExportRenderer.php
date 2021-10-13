<?php

namespace LaminasTest\View\TestAsset\Renderer;

use Laminas\View\Model\ModelInterface as Model;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Resolver\ResolverInterface as Resolver;

class VarExportRenderer implements Renderer
{
    public function getEngine()
    {
        return 'var_export';
    }

    /**
     * @return void
     */
    public function setResolver(Resolver $resolver)
    {
        // Deliberately empty
    }

    public function render($nameOrModel, $values = null)
    {
        if (! $nameOrModel instanceof Model) {
            return var_export($nameOrModel, true);
        }

        $values = $nameOrModel->getVariables();
        return var_export($values, true);
    }
}
