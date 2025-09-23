<?php

declare(strict_types=1);

namespace LaminasTest\View\Resolver\Factory;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ConfigProvider;
use Laminas\View\Helper\ViewModel;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel as Model;
use Laminas\View\Resolver\Factory\AggregateResolverFactory;
use PHPUnit\Framework\TestCase;

use function array_merge_recursive;
use function realpath;

final class AggregateResolverFactoryTest extends TestCase
{
    private static function containerWithConfig(array $config): ServiceManager
    {
        $config                             = array_merge_recursive(
            (new ConfigProvider())->__invoke(),
            $config,
        );
        $config['dependencies']['services'] = ['config' => $config];

        return new ServiceManager($config['dependencies']);
    }

    public function testMapsAreResolvedFirstByDefault(): void
    {
        $templates = realpath(__DIR__ . '/../../_templates');
        self::assertNotFalse($templates);

        $container = self::containerWithConfig([
            'view_manager' => [
                'template_map'        => [
                    'layout' => $templates . '/name-space/bar.phtml',
                ],
                'template_path_stack' => [
                    $templates, // layout.phtml is in this directory and would otherwise resolve.
                ],
            ],
        ]);

        $factory  = new AggregateResolverFactory();
        $resolver = $factory($container);

        self::assertSame(
            $templates . '/name-space/bar.phtml',
            $resolver->resolve('layout'),
        );
    }

    public function testPathStackResolvesBeforePrefixStack(): void
    {
        $templates = realpath(__DIR__ . '/../../_templates');
        self::assertNotFalse($templates);

        // Both directories contain a file named 'bar.phtml'
        $container = self::containerWithConfig([
            'view_manager' => [
                'template_path_stack'        => [
                    $templates,
                ],
                'prefix_template_path_stack' => [
                    'name-space' => $templates . '/prefix-path-stack-resolver',
                ],
            ],
        ]);

        $factory  = new AggregateResolverFactory();
        $resolver = $factory($container);

        self::assertSame(
            $templates . '/name-space/bar.phtml',
            $resolver->resolve('name-space/bar'),
        );
    }

    public function testRelativeFallbackResolversAreUsedAndTheViewModelPluginIsCorrectlyComposed(): void
    {
        $container = self::containerWithConfig([
            'templates' => [
                'map' => [
                    'view/relative' => 'expect.phtml',
                ],
            ],
        ]);

        $view = new Model();
        $view->setTemplate('view/some-file.phtml');

        $factory  = new AggregateResolverFactory();
        $resolver = $factory($container);

        $helper = $container->get(HelperPluginManager::class)->get(ViewModel::class);
        $helper->setCurrent($view);

        self::assertSame(
            'expect.phtml',
            $resolver->resolve('relative'),
        );
    }
}
