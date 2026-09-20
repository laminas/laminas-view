<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Config', 'src/ConfigProvider.php')
    ->layer('Console', 'src/Console')
    ->layer('Exception', 'src/Exception')
    ->layer('Factory', 'src/Factory')
    ->layer('HTML', [
        'src/HTML',
        'src/HtmlAttributesSet.php',
    ])
    ->layerPattern(
        'Helper',
        '/^Laminas\\\\View\\\\Helper\\\\.*$/',
        [
            '/^Laminas\\\\View\\\\Helper\\\\Escaper\\\\.*$/',
            '/^Laminas\\\\View\\\\Helper\\\\Placeholder\\\\.*$/',
            '/^Laminas\\\\View\\\\Helper\\\\Service\\\\.*$/',
        ]
    )
    ->layer('HelperEscaper', 'src/Helper/Escaper')
    ->layer('HelperPlaceholder', 'src/Helper/Placeholder')
    ->layer('HelperService', 'src/Helper/Service')
    ->layer('HelperPluginManager', 'src/HelperPluginManager.php')
    ->layer('HelperPluginManagerInterface', 'src/HelperPluginManagerInterface.php')
    ->layer('Model', 'src/Model')
    ->layer('Renderer', 'src/Renderer')
    ->layerPattern(
        'Resolver',
        '/^Laminas\\\\View\\\\Resolver\\\\.*$/',
        '/^Laminas\\\\View\\\\Resolver\\\\Factory\\\\.*$/'
    )
    ->layer('ResolverFactory', 'src/Resolver/Factory')
    ->layer('Template', 'src/TemplateInterface.php')
    ->layer('View', [
        'src/View.php',
        'src/ViewInterface.php',
    ])
    ->ruleset([
        'Exception'                    => [],
        'Console'                      => ['Exception'],
        'HTML'                         => ['Exception'],
        'Model'                        => ['Exception'],
        'HelperEscaper'                => ['Exception'],
        'HelperPlaceholder'            => ['Exception'],
        'Helper'                       => ['+HelperEscaper', '+HelperPlaceholder', '+HTML', '+Model', 'Renderer'],
        'HelperPluginManagerInterface' => ['Helper'],
        'HelperPluginManager'          => ['+HelperPluginManagerInterface', 'HelperService'],
        'HelperService'                => [
            'Config',
            'Exception',
            'Factory',
            'Helper',
            'HelperPluginManager',
            'Renderer',
        ],
        'Resolver'                     => ['Helper', '+Model'],
        'ResolverFactory'              => ['Config', 'Factory', 'Helper', 'HelperPluginManager', 'Resolver'],
        'Renderer'                     => ['Factory', '+HelperPluginManagerInterface', '+Resolver'],
        'Template'                     => ['Helper', '+HelperEscaper', '+HelperPlaceholder', '+HTML', '+Model'],
        'View'                         => ['+HelperPluginManagerInterface', '+Model', 'Renderer'],
        'Factory'                      => [
            'Config',
            'HelperPluginManager',
            'HelperPluginManagerInterface',
            'Renderer',
            'View',
        ],
        'Config'                       => ['Console', '+Factory', '+ResolverFactory'],
    ]);
