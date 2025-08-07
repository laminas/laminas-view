<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\Translator\TranslatorInterface;
use Laminas\View\Helper\Service\FetchTranslatorFromContainer;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function sprintf;

final class FetchTranslatorFromContainerTest extends TestCase
{
    private static function makeTranslator(): TranslatorInterface
    {
        return new class implements TranslatorInterface
        {
            public function translate($message, $textDomain = 'default', $locale = null) // phpcs:ignore
            {
                return $message;
            }

            public function translatePlural($singular, $plural, $number, $textDomain = 'default', $locale = null) // phpcs:ignore
            {
                return $singular;
            }
        };
    }

    /** @return iterable<string, array{0: array<string, object>, 1: TranslatorInterface|null}> */
    public static function configScenarios(): iterable
    {
        $translator = self::makeTranslator();
        foreach (FetchTranslatorFromContainer::SEARCH_ALIASES as $alias) {
            yield sprintf('%s with instance', $alias) => [[$alias => $translator], $translator];
            yield sprintf('%s not set', $alias) => [[], null];
            yield sprintf('%s wrong instance type', $alias) => [[$alias => new stdClass()], null];
        }
    }

    /** @param array<string, object> $config */
    #[DataProvider('configScenarios')]
    public function testConfigScenarios(array $config, TranslatorInterface|null $expect): void
    {
        $container = new InMemoryContainer();
        foreach ($config as $id => $service) {
            $container->set($id, $service);
        }

        if ($expect === null) {
            self::assertNull(FetchTranslatorFromContainer::withHistoricAliases($container));

            return;
        }

        self::assertSame($expect, FetchTranslatorFromContainer::withHistoricAliases($container));
    }
}
