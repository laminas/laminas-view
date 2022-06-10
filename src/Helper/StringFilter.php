<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\View\Exception\InvalidArgumentException;

use function assert;
use function sprintf;

final class StringFilter
{
    private ?FilterChain $chain;
    private ?string $value;
    private FilterPluginManager $filterPluginManager;

    public function __constuct(FilterPluginManager $filterPluginManager)
    {
        $this->filterPluginManager = $filterPluginManager;
    }

    public function __invoke(string $value): self
    {
        $this->value = $value;
        $this->chain = new FilterChain();

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        $this->chain->attach($this->getPlugin($name, $arguments));
    }

    public function __toString(): string
    {
        $value = $this->chain->filter($this->value);

        $this->value = null;
        $this->chain = null;

        return $value;
    }

    private function getPlugin(string $name, array $options): FilterInterface
    {
        if (! $this->filterPluginManager->has($name)) {
            throw new InvalidArgumentException(sprintf(
                'No filter exists with the name "%s"',
                $name
            ));
        }

        $plugin = $this->filterPluginManager->get($name, $options);
        assert($plugin instanceof FilterInterface);

        return $plugin;
    }
}
