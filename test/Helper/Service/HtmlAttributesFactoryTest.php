<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Service;

use Laminas\Escaper\Escaper;
use Laminas\View\Helper\HtmlAttributes;
use Laminas\View\Helper\Service\HtmlAttributesFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HtmlAttributesFactoryTest extends TestCase
{
    public function testThatAHelperWillBeCreatedWhenTheContainerDoesNotHaveAnEscaper(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with(Escaper::class)
            ->willReturn(false);
        $container->expects(self::never())
            ->method('get');

        self::assertInstanceOf(
            HtmlAttributes::class,
            (new HtmlAttributesFactory())($container)
        );
    }

    public function testThatAHelperWillBeCreatedWhenTheContainerDoesHaveAnEscaper(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with(Escaper::class)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->willReturn(new Escaper());

        self::assertInstanceOf(
            HtmlAttributes::class,
            (new HtmlAttributesFactory())($container)
        );
    }
}
