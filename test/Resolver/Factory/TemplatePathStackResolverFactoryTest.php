<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver\Factory;

use Laminas\View\Resolver\Factory\TemplatePathStackResolverFactory;
use Laminas\View\Resolver\TemplateCannotBeFound;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function is_string;
use function realpath;

final class TemplatePathStackResolverFactoryTest extends TestCase
{
    /** @return array<string, array{0: array, 1: non-empty-string, 2: null|string}> */
    public static function configProvider(): array
    {
        $templates = realpath(__DIR__ . '/../../_templates');
        self::assertNotFalse($templates);

        return [
            'Typical MVC Config'    => [
                [
                    'view_manager' => [
                        'template_path_stack' => [
                            $templates,
                        ],
                    ],
                ],
                'empty',
                $templates . '/empty.phtml',
            ],
            'Mezzio Default Suffix' => [
                [
                    'view_manager' => [
                        'template_path_stack' => [
                            $templates . '/prefix-path-stack-resolver',
                        ],
                    ],
                    'templates'    => [
                        'extension' => 'php',
                    ],
                ],
                'foo',
                $templates . '/prefix-path-stack-resolver/foo.php',
            ],
            'MVC Default Suffix'    => [
                [
                    'view_manager' => [
                        'default_template_suffix' => 'php',
                        'template_path_stack'     => [
                            $templates . '/prefix-path-stack-resolver',
                        ],
                    ],
                    'templates'    => [
                        'extension' => 'php',
                    ],
                ],
                'foo',
                $templates . '/prefix-path-stack-resolver/foo.php',
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

        $factory  = new TemplatePathStackResolverFactory();
        $resolver = $factory->__invoke($container);

        if (is_string($expect)) {
            self::assertSame($expect, $resolver->resolve($input));

            return;
        }

        $this->expectException(TemplateCannotBeFound::class);
        $resolver->resolve($input);
    }
}
