<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Layout;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

final class LayoutTest extends TestCase
{
    private Layout $helper;
    private ViewModel $rootViewModel;

    protected function setUp(): void
    {
        $viewModelHelper = new ViewModelHelper();

        $this->helper = new Layout(
            $viewModelHelper,
        );

        $this->rootViewModel = new ViewModel();
        $this->rootViewModel->setTemplate('layout');
        $viewModelHelper->setRoot($this->rootViewModel);
    }

    public function testExpectedDefaultLayoutValue(): void
    {
        self::assertSame('layout', $this->rootViewModel->getTemplate());
    }

    public function testInvokingWithATemplateValueAltersRootModelTemplate(): void
    {
        $returnValue = $this->helper->__invoke('alternate/layout');

        self::assertSame('alternate/layout', $this->rootViewModel->getTemplate());
        self::assertSame($this->helper, $returnValue);
    }

    public function testInvokingWithoutArgumentReturnsTheRootViewModel(): void
    {
        self::assertSame(
            $this->rootViewModel,
            $this->helper->__invoke(),
        );
    }

    public function testExceptionThrownWhenTheRootViewModelIsNotAvailableWhenSettingTheTemplate(): void
    {
        $helper = new Layout(new ViewModelHelper());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('view model');
        $helper->__invoke('foo/bar');
    }

    public function testExceptionThrownWhenTheRootViewModelIsNotAvailableWithZeroArguments(): void
    {
        $helper = new Layout(new ViewModelHelper());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('view model');
        $helper->__invoke();
    }
}
