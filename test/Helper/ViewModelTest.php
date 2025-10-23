<?php

declare(strict_types=1);

namespace Helper;

use Laminas\View\Helper\Layout;
use Laminas\View\Helper\ViewModel;
use Laminas\View\Model\ViewModel as Model;
use PHPUnit\Framework\TestCase;

final class ViewModelTest extends TestCase
{
    private ViewModel $helper;
    private Layout $layout;

    protected function setUp(): void
    {
        $this->layout = new Layout();
        $this->helper = new ViewModel($this->layout);
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testTheCurrentModelIsNotAvailableByDefault(): void
    {
        self::assertNull($this->helper->getCurrent());
        self::assertFalse($this->helper->hasCurrent());
    }

    public function testTheRootModelIsRetrievedFromTheLayoutHelper(): void
    {
        self::assertSame(
            $this->layout->getModel(),
            $this->helper->getRoot(),
        );
    }

    public function testTheCurrentModelCanBeRetrievedWhenSet(): void
    {
        $model = new Model();
        $this->helper->setCurrent($model);
        self::assertSame($model, $this->helper->getCurrent());
        self::assertTrue($this->helper->hasCurrent());
    }

    public function testTheCurrentModelIsNullifiedWhenStateIsReset(): void
    {
        $this->helper->setCurrent(new Model());
        $this->helper->resetState();
        self::assertNull($this->helper->getCurrent());
    }
}
