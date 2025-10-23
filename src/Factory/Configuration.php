<?php

declare(strict_types=1);

namespace Laminas\View\Factory;

use Laminas\View\ConfigProvider;
use Psr\Container\ContainerInterface;

use function is_array;
use function is_bool;
use function is_string;

/**
 * Provides consistent retrieval of configuration and individual values based on historic conventions
 *
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class Configuration
{
    private const DEFAULT_ENCODING         = 'utf-8';
    private const DEFAULT_TEMPLATE_SUFFIX  = 'phtml';
    private const STRICT_VARIABLES_DEFAULT = true;
    private const DEFAULT_LAYOUT_TEMPLATE  = 'layout::default';
    private const DEFAULT_CAPTURE_TO       = 'content';

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
     */
    public static function viewEncoding(
        ContainerInterface $container,
        string $defaultEncoding = self::DEFAULT_ENCODING,
    ): string {
        /** @var ViewConfigShape $config */
        $config   = self::get($container);
        $encoding = $config['view_manager']['encoding'] ?? '';
        $encoding = $config['view_helper_config']['encoding'] ?? $encoding;

        return $encoding !== '' ? $encoding : $defaultEncoding;
    }

    /**
     * Retrieve the default template suffix from conventional locations
     *
     * @return non-empty-string
     */
    public static function defaultTemplateSuffix(
        ContainerInterface $container,
    ): string {
        /** @var ViewConfigShape $config */
        $config = self::get($container);
        $suffix = $config['view_manager']['default_template_suffix'] ?? null;
        $suffix = $config['templates']['extension'] ?? $suffix;

        return is_string($suffix) ? $suffix : self::DEFAULT_TEMPLATE_SUFFIX;
    }

    /**
     * Return the strict_variables configuration option
     */
    public static function strictVariables(
        ContainerInterface $container,
    ): bool {
        /** @var ViewConfigShape $config */
        $config = self::get($container);

        $strict = $config['view_manager']['strict_variables'] ?? null;
        $strict = $config['templates']['strict_variables'] ?? $strict;

        return is_bool($strict) ? $strict : self::STRICT_VARIABLES_DEFAULT;
    }

    /**
     * Retrieve the name of the default layout template
     *
     * @return non-empty-string
     */
    public static function defaultLayout(
        ContainerInterface $container,
    ): string {
        /** @var ViewConfigShape $config */
        $config = self::get($container);

        $template = $config['view_manager']['default_layout'] ?? null;
        $template = $config['templates']['default_layout'] ?? $template;

        return is_string($template) ? $template : self::DEFAULT_LAYOUT_TEMPLATE;
    }

    /**
     * Retrieve the default variable name that "child" templates will be captured or rendered to
     *
     * @return non-empty-string
     */
    public static function defaultCaptureTo(
        ContainerInterface $container,
    ): string {
        /** @var ViewConfigShape $config */
        $config = self::get($container);

        $captureTo = $config['view_manager']['default_capture_to'] ?? null;
        $captureTo = $config['templates']['default_capture_to'] ?? $captureTo;

        return is_string($captureTo) ? $captureTo : self::DEFAULT_CAPTURE_TO;
    }
}
