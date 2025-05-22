<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Renderer\RendererInterface as Renderer;

interface HelperInterface
{
    /**
     * Set the View object
     *
     * @deprecated Since 2.40.0 This method will be removed in 3.0 without replacement. Dependency injection should be
     *             used to provide dependencies to consuming classes.
     *
     * @return HelperInterface
     */
    public function setView(Renderer $view);

    /**
     * Get the View object
     *
     * @deprecated Since 2.40.0 This method will be removed in 3.0 without replacement. Accessing dependencies in this
     *             way breaks encapsulation and is unnecessary when DI is used effectively.
     *
     * @return Renderer|null
     */
    public function getView();
}
