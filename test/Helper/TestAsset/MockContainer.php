<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\Placeholder\Container\AbstractContainer;

class MockContainer extends AbstractContainer
{
    /** @var array */
    public $data = [];

    /** @param array $data */
    public function __construct($data)
    {
        parent::__construct();
        $this->data = $data;
    }
}
