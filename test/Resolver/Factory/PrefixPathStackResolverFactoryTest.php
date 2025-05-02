<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver\Factory;

use Laminas\View\Resolver\Factory\PrefixPathStackResolverFactory;
use Laminas\View\Resolver\TemplateCannotBeFound;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function is_string;
use function realpath;

final class PrefixPathStackResolverFactoryTest extends TestCase
{
    /** @return array<string, array{0: array, 1: non-empty-string, 2: null|string}> */
    public static function configProvider(): array
    {
        $templates = realpath(__DIR__ . '/../../_templates/prefix-path-stack-resolver');
        self::assertNotFalse($templates);

        return [
            'Typical MVC Config'    => [
                [
                    'view_manager' => [
                        'prefix_template_path_stack' => [
                            'muppets' => $templates,
                        ],
                    ],
                ],
                'muppets/bar',
                $templates . '/bar.phtml',
            ],
            'Mezzio Default Suffix' => [
                [
                    'view_manager' => [
                        'prefix_template_path_stack' => [
                            'muppets' => $templates,
                        ],
                    ],
                    'templates'    => [
                        'extension' => 'php',
                    ],
                ],
                'muppets/foo',
                $templates . '/foo.php',
            ],
            'MVC Default Suffix'    => [
                [
                    'view_manager' => [
                        'default_template_suffix'    => 'php',
                        'prefix_template_path_stack' => [
                            'muppets' => $templates,
                        ],
                    ],
                ],
                'muppets/foo',
                $templates . '/foo.php',
            ],
            'Empty Config'          => [
                [],
                'foo',
                null,
            ],
        ];
    }

    /** @param non-empty-string $input */
    #[DataProvider('configProvider')]
    public function testFactory(array $config, string $input, string|null $expect): void
    {
        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory  = new PrefixPathStackResolverFactory();
        $resolver = $factory->__invoke($container);

        if (is_string($expect)) {
            self::assertSame($expect, $resolver->resolve($input));

            return;
        }

        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve($input);
    }
}
