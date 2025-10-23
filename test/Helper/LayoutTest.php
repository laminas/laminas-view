<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Layout;
use PHPUnit\Framework\TestCase;

final class LayoutTest extends TestCase
{
    private Layout $helper;

    protected function setUp(): void
    {
        $this->helper = new Layout();
    }

    public function testThatTheLayoutTemplateIsInitiallyNull(): void
    {
        self::assertNull($this->helper->getLayoutTemplate());
    }

    public function testLayoutIsNotDisabledByDefault(): void
    {
        self::assertFalse($this->helper->isDisabled());
    }

    public function testInvokingWithStringSetsLayoutTemplate(): void
    {
        $this->helper->__invoke('whatever');

        self::assertSame('whatever', $this->helper->getLayoutTemplate());
    }

    public function testLayoutTemplateCanBeChangedViaSetter(): void
    {
        $this->helper->setLayout('foo');

        self::assertSame('foo', $this->helper->getLayoutTemplate());
    }

    public function testThatAnEmptyLayoutModelIsReturnedByDefault(): void
    {
        $model = $this->helper->getModel();

        self::assertSame('', $model->getTemplate());
        self::assertSame([], $model->getVariables());
    }

    public function testThatTheLayoutTemplateIsSynchronisedWithTheModel(): void
    {
        $this->helper->__invoke('foo');

        self::assertSame('foo', $this->helper->getModel()->getTemplate());
    }

    public function testThatLayoutCanBeDisabledBySettingAFlag(): void
    {
        $this->helper->disable();

        self::assertTrue($this->helper->isDisabled());
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame(
            $this->helper,
            $this->helper->__invoke(),
        );
        self::assertSame(
            $this->helper,
            $this->helper->__invoke('foo'),
        );
    }

    public function testStateResetHasExpectedConsequence(): void
    {
        $model = $this->helper->getModel();
        $model->setVariable('foo', 'bar');
        $this->helper->disable();
        $this->helper->__invoke('foo');

        $this->helper->resetState();

        self::assertNotSame($model, $this->helper->getModel());
        self::assertNotSame('foo', $this->helper->getLayoutTemplate());
        self::assertNull($this->helper->getLayoutTemplate());
        self::assertFalse($this->helper->isDisabled());
    }
}
