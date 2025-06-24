<?php

declare(strict_types=1);

namespace Laminas\View\Renderer;

/**
 * @deprecated Since 2.40.0. This interface will be removed in 3.0 without replacement. With the removal of rendering
 *             strategies, this feature is unnecessary. View Models can be queried directly for child models.
 */
interface TreeRendererInterface
{
    /**
     * Indicate whether the renderer is capable of rendering trees of view models
     *
     * @return bool
     */
    public function canRenderTrees();
}
