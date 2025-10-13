<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception;

use function array_key_exists;
use function sprintf;

/**
 * View helper plugin to fetch asset from resource map.
 */
final readonly class Asset
{
    /**
     * @param array<non-empty-string, non-empty-string> $resourceMap
     */
    public function __construct(private array $resourceMap = [])
    {
    }

    /**
     * @param non-empty-string $asset
     * @return non-empty-string
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke(string $asset): string
    {
        if (! array_key_exists($asset, $this->resourceMap)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The asset with the name "%s" has not been defined.',
                $asset
            ));
        }

        return $this->resourceMap[$asset];
    }
}
