<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use function is_string;
use function str_starts_with;
use function strlen;
use function substr;

final class PrefixPathStackResolver implements ResolverInterface
{
    /** @var array<non-empty-string, ResolverInterface> */
    private readonly array $resolvers;

    /**
     * @param array<non-empty-string, list<non-empty-string>|non-empty-string|ResolverInterface> $prefixes Set of path
     *                  prefixes to be matched (array keys), with either a path or an array of paths
     *                  to use for matching as in the {@see TemplatePathStack},
     *                  or a {@see ResolverInterface} to use for view path starting with that prefix
     * @param non-empty-string $defaultSuffix
     */
    public function __construct(array $prefixes = [], string $defaultSuffix = 'phtml')
    {
        $resolvers = [];
        foreach ($prefixes as $prefix => $path) {
            if ($path instanceof ResolverInterface) {
                $resolvers[$prefix] = $path;

                continue;
            }

            if (is_string($path)) {
                $path = [$path];
            }

            $resolvers[$prefix] = new TemplatePathStack([
                'default_suffix' => $defaultSuffix,
                'script_paths'   => $path,
            ]);
        }

        $this->resolvers = $resolvers;
    }

    /** @inheritDoc */
    public function resolve(string $name): string
    {
        foreach ($this->resolvers as $prefix => $resolver) {
            if (! str_starts_with($name, $prefix)) {
                continue;
            }

            $template = substr($name, strlen($prefix));
            if ($template === '') {
                continue;
            }

            if ($resolver->has($template)) {
                return $resolver->resolve($template);
            }
        }

        throw TemplateCannotBeFound::byName($name);
    }

    public function has(string $name): bool
    {
        foreach ($this->resolvers as $prefix => $resolver) {
            if (! str_starts_with($name, $prefix)) {
                continue;
            }

            $template = substr($name, strlen($prefix));
            if ($template === '') {
                continue;
            }

            if ($resolver->has($template)) {
                return true;
            }
        }

        return false;
    }
}
