<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception;
use Laminas\View\Helper\Asset;

use function is_array;

/**
 * @final
 * @psalm-suppress DeprecatedInterface Compatibility with Service Manager 2 should be removed in version 3.0.
 */
class AssetFactory implements FactoryInterface
{
    /**
     * @param string $name
     * @param null|array $options
     * @return Asset
     * @throws Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        /** @psalm-var mixed $config */
        $config = $container->get('config');
        $config = is_array($config) ? $config : [];

        $helperConfig = $this->assertArray('view_helper_config', $config);
        $helperConfig = $this->assertArray('asset', $helperConfig);
        /** @psalm-var array<non-empty-string, non-empty-string> $resourceMap */
        $resourceMap = $this->assertArray('resource_map', $helperConfig);

        return new Asset($resourceMap);
    }

    /**
     * Create service
     *
     * @deprecated
     *
     * @param string|null $rName
     * @param string|null $cName
     * @return Asset
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $rName = null, $cName = null)
    {
        return $this($serviceLocator, $cName);
    }

    /**
     * @param array<array-key, mixed> $array
     * @return array<array-key, mixed>
     */
    private function assertArray(string $key, array $array): array
    {
        $value = $array[$key] ?? [];
        if (! is_array($value)) {
            throw new Exception\RuntimeException('Invalid resource map configuration.');
        }

        return $value;
    }
}
