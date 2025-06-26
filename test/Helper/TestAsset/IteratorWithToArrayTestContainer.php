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
        /** @psalm-var mixed $value */
        foreach ($info as $key => $value) {
            $this->$key = $value;
        }

        $this->info = $info;
    }

    /**
     * @deprecated To remove in 4.0
     *
     * @return array<array-key, mixed>
     * @psalm-api Used in duck typing
     */
    public function toArray(): array
    {
        return $this->info;
    }
}
