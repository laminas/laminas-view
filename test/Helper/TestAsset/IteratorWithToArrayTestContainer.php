<?php

namespace LaminasTest\View\Helper\TestAsset;

class IteratorWithToArrayTestContainer
{
    // @codingStandardsIgnoreStart
    protected $_info;
    // @codingStandardsIgnoreEnd

    public function __construct(array $info)
    {
        foreach ($info as $key => $value) {
            $this->$key = $value;
        }
        $this->_info = $info;
    }

    public function toArray()
    {
        return $this->_info;
    }
}
