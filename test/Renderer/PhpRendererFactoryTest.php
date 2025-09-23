<?php

declare(strict_types=1);

namespace LaminasTest\View\Renderer;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\RenderingFailedException;
use Laminas\View\HelperPluginManager;
use Laminas\View\HelperPluginManagerInterface;
use Laminas\View\Renderer\PhpRendererFactory;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplatePathStack;
use LaminasTest\View\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function is_array;

final class PhpRendererFactoryTest extends TestCase
{
    private function containerWithConfig(array|null $config): ContainerInterface
    {
        $resolver = new TemplatePathStack(['script_paths' => [__DIR__ . '/templates']]);
        $helpers  = new HelperPluginManager(new ServiceManager());

        $container = new InMemoryContainer();
        $container->set(ResolverInterface::class, $resolver);
        $container->set(HelperPluginManagerInterface::class, $helpers);
        if (is_array($config)) {
            $container->set('config', $config);
        }

        return $container;
    }

    public function testStrictVariablesIsEnabledWhenThereIsNoConfiguration(): void
    {
        $container = $this->containerWithConfig(null);
        $renderer  = (new PhpRendererFactory())->__invoke($container);

        $this->expectException(RenderingFailedException::class);
        $this->expectExceptionMessage('Access to an undeclared variable');
        $renderer->render('variable-as-property.phtml');
    }

    public function testStrictVariablesCanBeTurnedOffInConfig(): void
    {
        $container = $this->containerWithConfig([
            'view_manager' => [
                'strict_variables' => false,
            ],
        ]);
        $renderer  = (new PhpRendererFactory())->__invoke($container);
        $content   = $renderer->render('variable-as-property.phtml');

        self::assertStringContainsString('<p></p>', $content);
    }
}
