<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

class IteratorWithToArrayTestContainer
{
    /** @var array<array-key, mixed> */
    protected $_info; // phpcs:ignore

    /** @param array<array-key, mixed> $info */
    public function __construct(array $info)
    {
        foreach ($info as $key => $value) {
            $this->$key = $value;
        }
        $this->_info = $info;
    }

    /** @return array<array-key, mixed> */
    public function toArray(): array
    {
        return $this->_info;
    }
}
