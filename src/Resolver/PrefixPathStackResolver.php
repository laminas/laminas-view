<?php

declare(strict_types=1);

namespace Laminas\View\Resolver;

use Laminas\View\Renderer\RendererInterface as Renderer;

use function strlen;
use function strpos;
use function substr;

final class PrefixPathStackResolver implements ResolverInterface
{
    /**
     * Array containing prefix as key and "template path stack array" as value
     *
     * @var string[]|string[][]|ResolverInterface[]
     */
    private array $prefixes = [];

    /**
     * Constructor
     *
     * @param string[]|string[][]|ResolverInterface[] $prefixes Set of path prefixes
     *     to be matched (array keys), with either a path or an array of paths
     *     to use for matching as in the {@see \Laminas\View\Resolver\TemplatePathStack},
     *     or a {@see \Laminas\View\Resolver\ResolverInterface}
     *     to use for view path starting with that prefix
     */
    public function __construct(array $prefixes = [])
    {
        $this->prefixes = $prefixes;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, ?Renderer $renderer = null)
    {
        foreach ($this->prefixes as $prefix => &$resolver) {
            if (strpos($name, (string) $prefix) !== 0) {
                continue;
            }

            if (! $resolver instanceof ResolverInterface) {
                $resolver = new TemplatePathStack(['script_paths' => (array) $resolver]);
            }

            if ($result = $resolver->resolve(substr($name, strlen($prefix)), $renderer)) {
                return $result;
            }
        }

        return; // phpcs:ignore
    }
}
