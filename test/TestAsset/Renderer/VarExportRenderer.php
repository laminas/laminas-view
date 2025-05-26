<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset\Renderer;

use ArrayAccess;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ModelInterface as Model;
use Laminas\View\Renderer\RendererInterface as Renderer;

use function var_export;

final class VarExportRenderer implements Renderer
{
    public function getEngine(): string
    {
        return 'var_export';
    }

    /**
     * @param string|ModelInterface  $nameOrModel
     * @param null|array|ArrayAccess $values
     * @return string The script output.
     */
    public function render($nameOrModel, $values = null)
    {
        if (! $nameOrModel instanceof Model) {
            return var_export($nameOrModel, true);
        }

        $values = $nameOrModel->getVariables();
        return var_export($values, true);
    }
}
