<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use AllowDynamicProperties;

#[AllowDynamicProperties]
final class IteratorWithToArrayTestContainer
{
    /** @var array<array-key, mixed> */
    private $info;

    /** @param array<array-key, mixed> $info */
    public function __construct(array $info)
    {
        foreach ($info as $key => $value) {
            $this->$key = $value;
        }

        $this->info = $info;
    }

    /** @return array<array-key, mixed> */
    public function toArray(): array
    {
        return $this->info;
    }
}
