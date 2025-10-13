<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-internal Laminas
 * @psalm-internal LaminasTest
 */
final readonly class FetchTranslatorFromContainer
{
    public const SEARCH_ALIASES = [
        TranslatorInterface::class,
        'MvcTranslator',
        'Laminas\I18n\Translator\TranslatorInterface', // phpcs:ignore
        'Translator',
    ];

    public static function withHistoricAliases(ContainerInterface $container): TranslatorInterface|null
    {
        foreach (self::SEARCH_ALIASES as $alias) {
            if (! $container->has($alias)) {
                continue;
            }

            $service = $container->get($alias);
            if (! $service instanceof TranslatorInterface) {
                continue;
            }

            return $service;
        }

        return null;
    }
}
