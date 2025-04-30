<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\HeadTitle;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;
use function is_bool;
use function is_string;

final class HeadTitleFactory
{
    public function __invoke(ContainerInterface $container): HeadTitle
    {
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        assert(is_array($config));

        $options = $this->resolveOptions($config);

        $escaper = $container->has(Escaper::class)
            ? $container->get(Escaper::class)
            : new Escaper();

        return new HeadTitle(
            $escaper,
            $options['auto_escape'],
            $options['separator'],
            $options['indent'],
            $options['prefix'],
            $options['postfix'],
            FetchTranslatorFromContainer::withHistoricAliases($container),
            $options['text_domain'],
        );
    }

    /**
     * @param array<array-key, mixed> $config
     * @return array{
     *     indent: string,
     *     separator: string,
     *     prefix: string,
     *     postfix: string,
     *     auto_escape: bool,
     *     text_domain: non-empty-string,
     * }
     */
    private function resolveOptions(array $config): array
    {
        $options = [
            'indent'      => '',
            'separator'   => '',
            'prefix'      => '',
            'postfix'     => '',
            'auto_escape' => true,
            'text_domain' => 'default',
        ];

        if (! isset($config['view_helper_config']) || ! is_array($config['view_helper_config'])) {
            return $options;
        }

        $config = $config['view_helper_config'];
        if (! isset($config['head_title']) || ! is_array($config['head_title'])) {
            return $options;
        }

        $config = $config['head_title'];

        $options['indent']      = isset($config['indent']) && is_string($config['indent'])
            ? $config['indent']
            : $options['indent'];
        $options['separator']   = isset($config['separator']) && is_string($config['separator'])
            ? $config['separator']
            : $options['separator'];
        $options['prefix']      = isset($config['prefix']) && is_string($config['prefix'])
            ? $config['prefix']
            : $options['prefix'];
        $options['postfix']     = isset($config['postfix']) && is_string($config['postfix'])
            ? $config['postfix']
            : $options['postfix'];
        $options['auto_escape'] = isset($config['auto_escape']) && is_bool($config['auto_escape'])
            ? $config['auto_escape']
            : $options['auto_escape'];
        $options['text_domain'] = isset($config['text_domain'])
            && is_string($config['text_domain'])
            && $config['text_domain'] !== ''
            ? $config['text_domain']
            : $options['text_domain'];

        return $options;
    }
}
