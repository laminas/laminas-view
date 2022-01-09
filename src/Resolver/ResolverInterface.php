<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use Laminas\View\Renderer\RendererInterface as Renderer;

interface ResolverInterface
{
    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param  string $name
     * @return mixed
     */
    public function resolve($name, ?Renderer $renderer = null);
}
