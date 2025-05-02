<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver\Factory;

use Laminas\View\Resolver\Factory\TemplateMapResolverFactory;
use Laminas\View\Resolver\TemplateCannotBeFound;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function is_string;

final class TemplateMapResolverFactoryTest extends TestCase
{
    /** @return array<string, array{0: array, 1: non-empty-string, 2: null|string}> */
    public static function configProvider(): array
    {
        return [
            'Typical MVC Config'         => [
                [
                    'view_manager' => [
                        'template_map' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'foo',
                'bar',
            ],
            'Typical Mezzio Config'      => [
                [
                    'templates' => [
                        'map' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'foo',
                'bar',
            ],
            'Empty Config'               => [
                [],
                'foo',
                null,
            ],
            'Both specified means merge' => [
                [
                    'view_manager' => [
                        'template_map' => [
                            'foo' => 'mvc',
                        ],
                    ],
                    'templates'    => [
                        'map' => [
                            'foo' => 'mezzio',
                        ],
                    ],
                ],
                'foo',
                'mezzio',
            ],
        ];
    }

    /** @param non-empty-string $input */
    #[DataProvider('configProvider')]
    public function testFactory(array $config, string $input, string|null $expect): void
    {
        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory  = new TemplateMapResolverFactory();
        $resolver = $factory->__invoke($container);

        if (is_string($expect)) {
            self::assertSame($expect, $resolver->resolve($input));

            return;
        }

        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve($input);
    }
}
