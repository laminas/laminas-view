<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

interface ResolverInterface
{
    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param non-empty-string $name
     * @return non-empty-string|false
     */
    public function resolve(string $name): string|false;
}
