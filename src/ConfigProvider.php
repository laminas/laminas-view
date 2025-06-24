<?php

declare(strict_types=1);

namespace Laminas\View;

use Laminas\Escaper\Escaper;
use Laminas\Escaper\EscaperInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Service\EscaperFactory;

/**
 * @psalm-import-type DoctypeID from Doctype
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type ViewConfigShape = array{
 *     dependencies: ServiceManagerConfiguration,
 *     view_helpers: ServiceManagerConfiguration,
 *     view_helper_config?: array{
 *         asset?: array{resource_map: array<non-empty-string, non-empty-string>},
 *         base_path?: non-empty-string|null,
 *         doctype?: DoctypeID,
 *         encoding?: string,
 *     },
 *     view_manager?: array{
 *         base_path?: non-empty-string|null,
 *         strict_variables: bool,
 *         doctype?: DoctypeID,
 *         encoding?: non-empty-string,
 *         template_map?: array<string, string>,
 *         template_path_stack?: list<non-empty-string>,
 *         prefix_template_path_stack?: array<non-empty-string, non-empty-string>,
 *         default_template_suffix?: non-empty-string,
 *     },
 *     templates?: array{
 *         extension?: non-empty-string,
 *         map?: array<string, string>,
 *     },
 * }
 */
final class ConfigProvider
{
    /** @return ViewConfigShape */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            /**
             * The top-level configuration key for defining custom view helpers.
             *
             * This option should use the `ServiceManagerConfiguration` array format
             */
            'view_helpers'       => [],
            'view_helper_config' => [
                /**
                 * The expected doctype of your HTML output.
                 *
                 * The doctype is used by a number of helpers to determine the correct output style of generated markup
                 */
                'doctype' => Doctype::HTML5,

                /**
                 * Encoding is passed to the Escaper which is consumed by a number of helpers
                 */
                'encoding' => 'utf-8',
                /**
                 * Maps asset names to resources for the `Asset` helper
                 */
                'asset' => [
                    'resource_map' => [],
                ],

                /**
                 * The base path is a string provided to the BasePath view helper
                 */
                'base_path' => null,
            ],
            'view_manager'       => [
                /**
                 * Strict variables controls whether an exception is thrown when a view template attempts to use a
                 * variable that has not been defined.
                 */
                'strict_variables' => true,

                /**
                 * Templates configured here will be provided to the TemplateMapResolver.
                 * This is conventional for an MVC app
                 */
                'template_map' => [
                    // 'template-name' => __DIR__ . '/path/to/template.phtml',
                ],
                /**
                 * Templates configured here will be provided to the TemplatePathStack resolver.
                 * This is conventional for an MVC app
                 */
                'template_path_stack' => [
                    // __DIR__ . '/path/to/a/directory/of/templates',
                ],
                /**
                 * Templates configured here will be provided to the PrefixPathStackResolver
                 */
                'prefix_template_path_stack' => [
                    // 'prefix' => __DIR__ . '/path/to/a/directory/of/templates',
                ],
                /**
                 * The default template suffix is configured here in MVC applications
                 */
                //'default_template_suffix' => 'phtml',
            ],
            'templates'          => [
                /**
                 * Templates configured here will be provided to the TemplateMapResolver
                 * This is conventional for a Mezzio app
                 */
                'map' => [
                    // 'template-name' => __DIR__ . '/path/to/template.phtml',
                ],
                /**
                 * The default template filename extension is configured here. The default is 'phtml'
                 */
                //'extension' => 'phtml',
            ],
        ];
    }

    /** @return ServiceManagerConfiguration */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                HelperPluginManager::class              => HelperPluginManagerFactory::class,
                Escaper::class                          => EscaperFactory::class,
                Renderer\PhpRenderer::class             => Renderer\PhpRendererFactory::class,
                Resolver\AggregateResolver::class       => Resolver\Factory\AggregateResolverFactory::class,
                Resolver\PrefixPathStackResolver::class => Resolver\Factory\PrefixPathStackResolverFactory::class,
                Resolver\TemplateMapResolver::class     => Resolver\Factory\TemplateMapResolverFactory::class,
                Resolver\TemplatePathStack::class       => Resolver\Factory\TemplatePathStackResolverFactory::class,
            ],
            'aliases'   => [
                EscaperInterface::class             => Escaper::class,
                HelperPluginManagerInterface::class => HelperPluginManager::class,
                Renderer\RendererInterface::class   => Renderer\PhpRenderer::class,
                Resolver\ResolverInterface::class   => Resolver\AggregateResolver::class,
            ],
        ];
    }
}
