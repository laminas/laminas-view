<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use LaminasTest\View\Helper\Placeholder\RegistryTest;

/**
 * @deprecated Should be removed in 3.0 when RegistryTest is removed
 *
 * @see RegistryTest
 *
 * @psalm-suppress MissingTemplateParam
 */
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
