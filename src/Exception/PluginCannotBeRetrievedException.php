<?php

declare(strict_types=1);

namespace Laminas\View\Exception;

use Throwable;

use function count;
use function implode;
use function sprintf;

final class PluginCannotBeRetrievedException extends RuntimeException
{
    /** @param array<array-key, non-empty-string> $availablePlugins */
    public static function becauseItHasNotBeenMapped(string $pluginName, array $availablePlugins): self
    {
        return new self(
            sprintf(
                'The plugin named "%s" cannot be retrieved because it does not exist in the list of known plugins. '
                . 'The available plugins are "%s"',
                $pluginName,
                self::formatPluginNames($availablePlugins)
            )
        );
    }

    /** @param non-empty-string $pluginName */
    public static function becauseItIsNotCallable(string $pluginName): self
    {
        return new self(
            sprintf(
                'The plugin named "%s" cannot be used because the value it resolves to is not `callable`',
                $pluginName
            )
        );
    }

    /** @param non-empty-string $pluginName */
    public static function becauseItCannotBeCreated(string $pluginName, Throwable $error): self
    {
        if ($error instanceof self) {
            return new self(sprintf(
                'The plugin named "%s" cannot be created because it is not an invokable object',
                $pluginName
            ), 0, $error);
        }

        return new self(sprintf(
            'The plugin named "%s" cannot be create because an error occurred during instantiation: "%s"',
            $pluginName,
            $error->getMessage()
        ), 0, $error);
    }

    /**
     * @param non-empty-string $pluginName
     * @param non-empty-string $target
     */
    public static function becauseItsTargetIsNotAClassString(string $pluginName, string $target): self
    {
        return new self(sprintf(
            'The plugin named "%s" and mapped to the value "%s" cannot be created '
            . 'because the value does not appear to be a class that can be instantiated.',
            $pluginName,
            $target
        ));
    }

    /** @param array<array-key, non-empty-string> $names */
    private static function formatPluginNames(array $names): string
    {
        if (! count($names)) {
            return '<none>';
        }

        return implode(', ', $names);
    }
}
