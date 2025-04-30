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
     * Historically, view `encoding` is set in `view_manager.encoding`
     *
     * @param non-empty-string $defaultEncoding
     * @return non-empty-string
     */
    public static function viewEncoding(
        ContainerInterface $container,
        string $defaultEncoding = self::DEFAULT_ENCODING,
    ): string {
        $config = self::get($container);
        /** @var mixed $encoding */
        $encoding = $config['view_manager']['encoding'] ?? '';

        return is_string($encoding) && $encoding !== '' ? $encoding : $defaultEncoding;
    }
}
