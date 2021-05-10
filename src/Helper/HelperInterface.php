<?php

namespace Laminas\View\Helper;

use Laminas\View\Renderer\RendererInterface as Renderer;

interface HelperInterface
{
    /**
     * Set the View object
     *
     * @param  Renderer $view
     * @return HelperInterface
     */
    public function setView(Renderer $view);

    /**
     * Get the View object
     *
     * @return Renderer
     */
    public function getView();
}
