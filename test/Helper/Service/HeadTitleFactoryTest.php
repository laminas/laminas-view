<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use ArrayObject;
use Laminas\View\Helper\Service\HeadTitleFactory;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function is_array;

final class HeadTitleFactoryTest extends TestCase
{
    /** @return iterable<string, array{0: array|null, 1: list<string>, 2: string}> */
    public static function configScenarios(): iterable
    {
        yield 'Reasonable valid config' => [
            [
                'view_helper_config' => [
                    'head_title' => [
                        'indent'      => ' ',
                        'separator'   => ' & ',
                        'prefix'      => 'Pre: ',
                        'postfix'     => ' :Post',
                        'auto_escape' => false,
                        'text_domain' => 'baz',
                    ],
                ],
            ],
            ['Title', '1'],
            ' <title>Pre: Title & 1 :Post</title>',
        ];

        yield 'Empty Config' => [
            [],
            ['Title', '&'],
            '<title>Title&amp;</title>',
        ];

        yield 'No Config' => [
            null,
            ['Title', '1'],
            '<title>Title1</title>',
        ];

        yield 'No Head title config' => [
            [
                'view_helper_config' => [],
            ],
            ['Title', '1'],
            '<title>Title1</title>',
        ];

        yield 'Config with invalid types are ignored' => [
            [
                'view_helper_config' => [
                    'head_title' => [
                        'indent'      => 99,
                        'separator'   => 99,
                        'prefix'      => [],
                        'postfix'     => [],
                        'auto_escape' => 'Foo',
                        'text_domain' => 123,
                    ],
                ],
            ],
            ['Title', '1'],
            '<title>Title1</title>',
        ];
    }

    /** @return iterable<string, array{0: ArrayObject<array-key, mixed>|null, 1: list<string>, 2: string}> */
    public static function configScenariosAsArrayObjects(): iterable
    {
        foreach (self::configScenarios() as $key => $args) {
            yield $key . ' (ArrayObject)' => [
                is_array($args[0]) ? new ArrayObject($args[0]) : $args[0],
                $args[1],
                $args[2],
            ];
        }
    }

    /**
     * @param iterable<array-key, mixed>|null $config
     * @param list<string> $append
     */
    #[DataProvider('configScenarios')]
    #[DataProvider('configScenariosAsArrayObjects')]
    public function testFactory(iterable|null $config, array $append, string $expect): void
    {
        $container = new InMemoryContainer();
        if ($config !== null) {
            $container->set('config', $config);
        }

        $factory = new HeadTitleFactory();
        $helper  = $factory->__invoke($container);

        foreach ($append as $item) {
            $helper->append($item);
        }

        self::assertSame($expect, $helper->__toString());
    }
}
