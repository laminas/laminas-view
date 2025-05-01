<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Psr\Container\ContainerInterface;

use function is_array;
use function is_string;

/**
 * Provides consistent retrieval of configuration and individual values based on historic conventions
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class Configuration
{
    private const DEFAULT_ENCODING = 'utf-8';

    /** @return array<array-key, mixed> */
    public static function get(ContainerInterface $container): array
    {
        /** @var mixed $config */
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        return is_array($config) ? $config : [];
    }

    /**
     * Fetch the encoding configuration variable
     *
     * Historically, view `encoding` was set in `view_manager.encoding` but now it is
     * found in `view_helper_config.encoding`. We're still checking the old location.
     *
     * @param non-empty-string $defaultEncoding
     * @return non-empty-string
     * @psalm-suppress MixedAssignment It is just annoying here.
     */
    public static function viewEncoding(
        ContainerInterface $container,
        string $defaultEncoding = self::DEFAULT_ENCODING,
    ): string {
        $config   = self::get($container);
        $encoding = $config['view_manager']['encoding'] ?? '';
        $encoding = $config['view_helper_config']['encoding'] ?? $encoding;

        return is_string($encoding) && $encoding !== '' ? $encoding : $defaultEncoding;
    }
}
