<?php

declare(strict_types=1);

namespace Helper;

use Laminas\View\Helper\ViewModel;
use Laminas\View\Model\ViewModel as Model;
use PHPUnit\Framework\TestCase;

final class ViewModelTest extends TestCase
{
    private ViewModel $helper;

    protected function setUp(): void
    {
        $this->helper = new ViewModel();
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, $this->helper->__invoke());
    }

    public function testThatNoModelsAreAvailableByDefault(): void
    {
        self::assertNull($this->helper->getCurrent());
        self::assertNull($this->helper->getRoot());

        self::assertFalse($this->helper->hasRoot());
        self::assertFalse($this->helper->hasCurrent());
    }

    public function testTheRootModelCanBeRetrievedWhenSet(): void
    {
        $model = new Model();
        $this->helper->setRoot($model);
        self::assertSame($model, $this->helper->getRoot());
        self::assertTrue($this->helper->hasRoot());
    }

    public function testTheCurrentModelCanBeRetrievedWhenSet(): void
    {
        $model = new Model();
        $this->helper->setCurrent($model);
        self::assertSame($model, $this->helper->getCurrent());
        self::assertTrue($this->helper->hasCurrent());
    }

    public function testModelsAreNullifiedWhenStateIsReset(): void
    {
        $this->helper->setRoot(new Model());
        $this->helper->setCurrent(new Model());
        $this->helper->resetState();

        self::assertNull($this->helper->getRoot());
        self::assertNull($this->helper->getCurrent());
    }
}
