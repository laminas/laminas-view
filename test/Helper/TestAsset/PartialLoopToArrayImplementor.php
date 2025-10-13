<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use LaminasTest\View\Helper\PartialLoopTest;

final class PartialLoopToArrayImplementor
{
    public function __construct(private readonly array $data)
    {
    }

    /**
     * @deprecated This should be removed in 4.0
     *
     * @link PartialLoopTest::testShouldAllowIteratingOverObjectsImplementingToArrayWithDeprecation
     *
     * @psalm-api This method is used via duck-typing in tests
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
