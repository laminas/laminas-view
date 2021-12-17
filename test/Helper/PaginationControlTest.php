<?php

namespace LaminasTest\View\Helper;

use Laminas\Paginator;
use Laminas\View\Exception;
use Laminas\View\Helper;
use Laminas\View\Renderer\PhpRenderer as View;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class PaginationControlTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * @var Helper\PaginationControl
     */
    private $_viewHelper;

    /** @var Paginator\Paginator */
    private $_paginator;
    // @codingStandardsIgnoreEnd

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $resolver = new Resolver\TemplatePathStack(['script_paths' => [
            __DIR__ . '/_files/scripts',
        ]]);
        $view = new View();
        $view->setResolver($resolver);

        Helper\PaginationControl::setDefaultViewPartial(null);
        $this->_viewHelper = new Helper\PaginationControl();
        $this->_viewHelper->setView($view);
        $adapter = new Paginator\Adapter\ArrayAdapter(range(1, 101));
        $this->_paginator = new Paginator\Paginator($adapter);
    }

    public function testGetsAndSetsView(): void
    {
        $view   = new View();
        $helper = new Helper\PaginationControl();
        $this->assertNull($helper->getView());
        $helper->setView($view);
        $this->assertInstanceOf(RendererInterface::class, $helper->getView());
    }

    public function testGetsAndSetsDefaultViewPartial(): void
    {
        $this->assertNull(Helper\PaginationControl::getDefaultViewPartial());
        Helper\PaginationControl::setDefaultViewPartial('partial');
        $this->assertEquals('partial', Helper\PaginationControl::getDefaultViewPartial());
    }

    public function testUsesDefaultViewPartialIfNoneSupplied(): void
    {
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');
        $output = $this->_viewHelper->__invoke($this->_paginator);
        $this->assertStringContainsString('pagination control', $output, $output);
    }

    public function testThrowsExceptionIfNoViewPartialFound(): void
    {
        $this->expectException(Exception\ExceptionInterface::class);
        $this->expectExceptionMessage('No view partial provided and no default set');
        $this->_viewHelper->__invoke($this->_paginator);
    }

    /**
     * @group Laminas-4037
     */
    public function testUsesDefaultScrollingStyleIfNoneSupplied(): void
    {
        // First we'll make sure the base case works
        $output = $this->_viewHelper->__invoke($this->_paginator, 'All', 'testPagination.phtml');
        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);

        Paginator\Paginator::setDefaultScrollingStyle('All');
        $output = $this->_viewHelper->__invoke($this->_paginator, null, 'testPagination.phtml');
        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);

        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');
        $output = $this->_viewHelper->__invoke($this->_paginator);
        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);
    }

    /**
     * @group Laminas-4153
     */
    public function testUsesPaginatorFromViewIfNoneSupplied(): void
    {
        $this->_viewHelper->getView()->paginator = $this->_paginator;
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');

        $output = $this->_viewHelper->__invoke();

        $this->assertStringContainsString('pagination control', $output, $output);
    }

    /**
     * @group Laminas-4153
     */
    public function testThrowsExceptionIfNoPaginatorFound(): void
    {
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');

        $this->expectException(Exception\ExceptionInterface::class);
        $this->expectExceptionMessage('No paginator instance provided or incorrect type');
        $this->_viewHelper->__invoke();
    }

    /**
     * @group Laminas-4233
     */
    public function testAcceptsViewPartialInOtherModule(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to render template "partial.phtml"; resolver could not resolve to a file'
        );
        $this->_viewHelper->__invoke($this->_paginator, null, ['partial.phtml', 'test']);
    }

    /**
     * @group Laminas-4328
     */
    public function testUsesPaginatorFromViewOnlyIfNoneSupplied(): void
    {
        $this->_viewHelper->getView()->vars()->paginator  = $this->_paginator;
        $paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 30)));
        Helper\PaginationControl::setDefaultViewPartial('testPagination.phtml');

        $output = $this->_viewHelper->__invoke($paginator);
        $this->assertStringContainsString('page count (3)', $output, $output);
    }

    /**
     * @group Laminas-4878
     */
    public function testCanUseObjectForScrollingStyle(): void
    {
        $all = new Paginator\ScrollingStyle\All();

        $output = $this->_viewHelper->__invoke($this->_paginator, $all, 'testPagination.phtml');

        $this->assertStringContainsString('page count (11) equals pages in range (11)', $output, $output);
    }
}
