<?php

namespace Laminas\View\Helper;

use Laminas\View\Renderer\RendererInterface as Renderer;

abstract class AbstractHelper implements HelperInterface
{
    /**
     * View object instance
     *
     * @var Renderer
     */
    protected $view = null;

    /**
     * Set the View object
     *
     * @param  Renderer $view
     * @return AbstractHelper
     */
    public function setView(Renderer $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the view object
     *
     * @return null|Renderer
     */
    public function getView()
    {
        return $this->view;
    }
}
