<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\View\Exception\PluginCannotBeRetrievedException;
use Psr\Container\ContainerInterface;
use Throwable;

use function array_key_exists;
use function array_keys;
use function class_exists;
use function in_array;
use function is_callable;

/**
 * @internal
 */
final class PluginManager
{
    private ContainerInterface $container;
    /** @var array<non-empty-string, string|class-string> */
    private array $pluginMap;
    /** Determines whether an attempt is made to create a helper that is not already present in the container */
    private bool $autoInvoke;

    /** @param array<non-empty-string, non-empty-string|class-string> $pluginMap */
    public function __construct(ContainerInterface $container, array $pluginMap, bool $autoInvoke)
    {
        $this->container  = $container;
        $this->pluginMap  = $pluginMap;
        $this->autoInvoke = $autoInvoke;
    }

    /** @param non-empty-string $pluginName */
    public function has(string $pluginName): bool
    {
        $target = $this->resolveTarget($pluginName);
        if (! $target) {
            return false;
        }

        if ($this->container->has($target)) {
            return true;
        }

        return $this->isNewAble($target);
    }

    /**
     * @param non-empty-string $pluginName
     * @thows PluginCannotBeRetrievedException If the plugin has not been mapped, does not exist or cannot be created.
     */
    public function get(string $pluginName): callable
    {
        $target = $this->resolveTarget($pluginName);
        if (! $target) {
            throw PluginCannotBeRetrievedException::becauseItHasNotBeenMapped(
                $pluginName,
                $this->availablePluginNames()
            );
        }

        /** @psalm-var non-empty-string|class-string $target */
        if ($this->container->has($target)) {
            return $this->validatePlugin(
                $pluginName,
                $this->container->get($target)
            );
        }

        if (! $this->isNewAble($target)) {
            throw PluginCannotBeRetrievedException::becauseItsTargetIsNotAClassString($pluginName, $target);
        }

        /** @psalm-var class-string $target */
        try {
            return $this->validatePlugin(
                $pluginName,
                new $target()
            );
        } catch (Throwable $error) {
            throw PluginCannotBeRetrievedException::becauseItCannotBeCreated($pluginName, $error);
        }
    }

    /** @return string|class-string|null */
    private function resolveTarget(string $value): ?string
    {
        if (array_key_exists($value, $this->pluginMap)) {
            return $this->pluginMap[$value];
        }

        if (in_array($value, $this->pluginMap, true)) {
            return $value;
        }

        return null;
    }

    private function isNewAble(string $target): bool
    {
        return $this->autoInvoke && class_exists($target);
    }

    /** @return array<array-key, non-empty-string> */
    private function availablePluginNames(): array
    {
        return array_keys($this->pluginMap);
    }

    /**
     * @param non-empty-string $name
     * @param mixed $plugin
     */
    private function validatePlugin(string $name, $plugin): callable
    {
        if (is_callable($plugin)) {
            return $plugin;
        }

        throw PluginCannotBeRetrievedException::becauseItIsNotCallable($name);
    }
}
