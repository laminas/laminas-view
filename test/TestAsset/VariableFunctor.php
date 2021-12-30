<?php

namespace LaminasTest\View\TestAsset;

/**
 * @template T
 */
class VariableFunctor
{
    /** @var T|null */
    public $value;

    /** @param T|null $value */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /** @return T */
    public function __invoke()
    {
        return $this->value;
    }
}
