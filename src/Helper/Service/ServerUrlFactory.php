<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use ArrayAccess;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\ServerUrl;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;
use function is_string;

final class ServerUrlFactory
{
    public function __invoke(ContainerInterface $container): ServerUrl
    {
        return new ServerUrl(
            $this->fetchConfiguredServerUrl($container) ?: $this->detectServerUrlFromEnvironment()
        );
    }

    private function fetchConfiguredServerUrl(ContainerInterface $container): ?string
    {
        $config = $container->has('config') ? $container->get('config') : [];
        assert(is_array($config) || $config instanceof ArrayAccess);

        $helperConfig = $config['view_helper_config'] ?? [];
        assert(is_array($helperConfig));

        $serverUrl = $helperConfig['server_url'] ?? null;
        assert(is_string($serverUrl) || $serverUrl === null);

        return $serverUrl;
    }

    private function detectServerUrlFromEnvironment(): string
    {
        $serverRequest = ServerRequestFactory::fromGlobals($_SERVER);
        $uri           = $serverRequest->getUri()
            ->withPath('')
            ->withQuery('')
            ->withFragment('');

        if (! $uri->getHost() || ! $uri->getScheme()) {
            throw new RuntimeException(
                'The current host or scheme cannot be detected from the environment'
            );
        }

        return (string) $uri;
    }
}
