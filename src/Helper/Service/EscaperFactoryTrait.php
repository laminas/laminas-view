<?php

declare(strict_types=1);

namespace Laminas\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\View\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

use function assert;
use function gettype;
use function is_array;
use function is_string;
use function sprintf;

trait EscaperFactoryTrait
{
    /** @return non-empty-string */
    private function viewEncoding(ContainerInterface $container): string
    {
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        assert(is_array($config));
        $viewConfig = $this->assertArray('view_manager', $config);

        $encoding = $viewConfig['encoding'] ?? 'UTF-8';
        if (! is_string($encoding) || empty($encoding)) {
            throw new RuntimeException(sprintf(
                'View encoding should be a string. Received "%s"',
                gettype($viewConfig['encoding'])
            ));
        }

        return $encoding;
    }

    private function retrieveOrCreateEscaper(ContainerInterface $container): Escaper
    {
        if ($container->has(Escaper::class)) {
            $escaper = $container->get(Escaper::class);
            assert($escaper instanceof Escaper);

            return $escaper;
        }

        return new Escaper($this->viewEncoding($container));
    }

    /**
     * @param array<array-key, mixed> $array
     * @return array<array-key, mixed>
     */
    private function assertArray(string $key, array $array): array
    {
        $value = $array[$key] ?? [];
        if (! is_array($value)) {
            throw new RuntimeException('Invalid view configuration.');
        }

        return $value;
    }
}
