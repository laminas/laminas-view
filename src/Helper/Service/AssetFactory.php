<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Asset;
use Psr\Container\ContainerInterface;
use Traversable;

use function gettype;
use function is_array;
use function iterator_to_array;
use function sprintf;

final class AssetFactory
{
    /**
     * @throws RuntimeException
     */
    public function __invoke(ContainerInterface $container): Asset
    {
        /** @psalm-var mixed $config */
        $config = $container->get('config');
        /** @psalm-var mixed $config */
        $config = $config instanceof Traversable ? iterator_to_array($config, true) : $config;
        $config = is_array($config) ? $config : [];

        $helperConfig = $this->assertArray('view_helper_config', $config);
        $helperConfig = $this->assertArray('asset', $helperConfig);
        /** @psalm-var array<non-empty-string, non-empty-string> $resourceMap */
        $resourceMap = $this->assertArray('resource_map', $helperConfig);

        return new Asset($resourceMap);
    }

    /**
     * @param array<array-key, mixed> $array
     * @return array<array-key, mixed>
     */
    private function assertArray(string $key, array $array): array
    {
        $value = $array[$key] ?? [];
        if (! is_array($value)) {
            throw new RuntimeException(sprintf(
                'Invalid resource map configuration. '
                . 'Expected the key "%s" to contain an array value but received "%s"',
                $key,
                gettype($value)
            ));
        }

        return $value;
    }
}
