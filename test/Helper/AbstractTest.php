<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Renderer\RendererInterface;
use LaminasTest\View\Helper\TestAsset\ConcreteHelper;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /** @var ConcreteHelper */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = new ConcreteHelper();
    }

    public function testViewSettersGetters(): void
    {
        $viewMock = $this->createMock(RendererInterface::class);

        $this->helper->setView($viewMock);
        $this->assertEquals($viewMock, $this->helper->getView());
    }
}
