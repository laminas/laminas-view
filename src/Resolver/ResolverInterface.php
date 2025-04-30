<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

interface ResolverInterface
{
    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param non-empty-string $name
     * @return non-empty-string
     * @throws TemplateCannotBeFound If the given template cannot be found/resolved.
     */
    public function resolve(string $name): string;
}
